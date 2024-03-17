<?php

namespace phpbb\db\connectors;

use DateTimeInterface;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Session;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\BSON\UTCDateTime;
use phpbb\db\errors\DatabaseError;
use phpbb\db\errors\DuplicateError;
use phpbb\db\query;
use stdClass;

/**
 * MongoDB connector
 */
class mongodb extends abstraction
{

    private ?Manager $mongodb = null;
    private ?Session $session = null;

    /**
     * Starts the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    public function transaction(): abstraction
    {
        if ($this->session) {
            $this->session = $this->connection()->startSession(
                $this->config->get('options')->get('session', [])
            );
            $this->session->startTransaction(
                $this->config->get('options')->get('transaction', [])
            );
        }
        return $this;
    }

    /**
     * Commits the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    public function commit(): abstraction
    {
        if (!$this->session) {
            throw new DatabaseError("No active mongodb transaction");
        }
        $this->session->commitTransaction();
        return $this;
    }

    /**
     * Rollbacks the transaction
     * 
     * @author ikubicki
     * @return abstraction
     */
    public function rollback(): abstraction
    {
        if (!$this->session) {
            throw new DatabaseError("No active mongodb transaction");
        }
        $this->session->abortTransaction();
        $this->session = null;
        return $this;
    }

    /**
     * Inserts a single document for a given query and values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $values
     * @return bool
     */
    public function add(query $query, array $values): bool
    {
        return $this->addMany($query, [$values]);
    }

    /**
     * Inserts multiple documents for a given query and collection of values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $collection
     * @return bool
     */
    public function addMany(query $query, array $collection): bool
    {
        $cursor = $this->connection()->executeWriteCommand(
            $this->config->raw('database'),
            $this->getInsertCommand($query, $collection),
            $this->getCommandOptions()
        );
        $cursor->next();
        $result = $cursor->current();
        if (count($result->writeErrors ?? [])) {
            $error = $result->writeErrors[0];
            if ($error->code === 11000) {
                throw new DuplicateError($error->errmsg);
            }
            throw new DatabaseError($error->errmsg);
        }
        return true;
    }

    /**
     * Updates documents for a given query with provided values
     * 
     * @author ikubicki
     * @param query $query
     * @param array $values
     * @return bool
     */
    public function update(query $query, array $values): bool
    {
        $cursor = $this->connection()->executeWriteCommand(
            $this->config->raw('database'),
            $this->getUpdateCommand($query, $values),
            $this->getCommandOptions()
        );
        $cursor->next();
        $result = $cursor->current();
        if (count($result->writeErrors ?? [])) {
            throw new DatabaseError($result->writeErrors[0]->errmsg);
        }
        return true;
    }

    /**
     * Removes documents for a query
     * 
     * @author ikubicki
     * @param query $query
     * @return bool
     */
    public function remove(query $query): bool
    {
        $cursor = $this->connection()->executeWriteCommand(
            $this->config->raw('database'),
            $this->getDeleteCommand($query),
            $this->getCommandOptions()
        );
        $cursor->next();
        $result = $cursor->current();
        if (count($result->writeErrors ?? [])) {
            throw new DatabaseError($result->writeErrors[0]->errmsg);
        }
        return true;
    }

    /**
     * Executes a find command for a query
     * Returns an iterable collection with hydration ability
     * 
     * @author ikubicki
     * @param query $query
     * @return records
     */
    public function query(query $query): records
    {
        $cursor = $this->connection()->executeReadCommand(
            $this->config->raw('database'),
            $this->getFindCommand($query),
            $this->getCommandOptions()
        );
        return $this->wrapCursor($cursor);
    }

    /**
     * Starts the connection
     * 
     * @author ikubicki
     * @return Manager
     */
    private function connection(): Manager
    {
        if (!$this->mongodb) {
            if (!class_exists(Manager::class)) {
                throw new DatabaseError(sprintf(DatabaseError::DATABASE_NOT_INSTALLED, 'MongoDB'));
            }
            if (!$this->config->raw('database')) {
                throw new DatabaseError(DatabaseError::DATABASE_NOT_SELECTED);
            }
            $this->mongodb = new Manager(
                $this->config->get('connection'),
                $this->config->get('options')->raw('uri'),
                $this->config->get('options')->raw('driver'),
            );
        }
        return $this->mongodb;
    }

    /**
     * Wraps cursor object results into an iterable collection with hydration ability
     * 
     * @author ikubicki
     * @param Cursor $cursor
     * @return records
     */
    private function wrapCursor(Cursor $cursor): records
    {
        $records = new records();
        foreach ($cursor as $document) {
            if (isset($document->_id)) {
                $document->{'$id'} = (string) $document->_id;
                unset($document->_id);
            }
            foreach(get_object_vars($document) as $property => $value) {
                if ($value instanceof UTCDateTime) {
                    $document->$property = $value->toDateTime();
                }
            }
            $records->append(new record($document));
        }
        return $records;
    }

    /**
     * Returns an insert command
     * 
     * @author ikubicki
     * @param query $query
     * @param array $collections
     * @return Command
     */
    private function getInsertCommand(query $query, array $collections): Command
    {
        return new Command([
            'insert' => $query->collection,
            'documents' => array_map([$this, 'convertValues'], $collections),
        ]);
    }

    /**
     * Returns an update command
     * 
     * @author ikubicki
     * @param query $query
     * @param array $values
     * @return Command
     */
    private function getUpdateCommand(query $query, array $values): Command
    {
        return new Command([
            'update' => $query->collection,
            'updates' => [[
                'q' => $this->convertValues($query->filters),
                'u' => [
                    '$set' => $this->convertValues($values),
                ],
                'multi' => ($query->options[$query::LIMIT] ?? 0) != 1,
                'upsert' => ($query->options[$query::INSERT] ?? false),
            ]]
        ]);
    }

    /**
     * Returns a delete command
     * 
     * @author ikubicki
     * @param query $query
     * @return Command
     */
    private function getDeleteCommand(query $query): Command
    {
        return new Command([
            'delete' => $query->collection,
            'deletes' => [[
                'q' => $this->convertValues($query->filters),
                'limit' => $query->options[$query::LIMIT] ?? 1,
            ]]
        ]);
    }

    /**
     * Returns a find command
     * 
     * @author ikubicki
     * @param query $query
     * @return Command
     */
    private function getFindCommand(query $query): Command
    {
        return new Command((object) array_merge(
            [
                'find' => $query->collection,
                'filter' => $this->convertValues($query->filters ?: []),
            ],
            $this->projection($query->fields),
            $this->sort($query->options),
            $this->limit($query->options),
        ));
    }

    private function convertValues($values): stdClass
    {
        foreach($values as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $values[$key] = new UTCDateTime($value->getTimestamp() * 1000);
            }
        }
        return (object) $values;
    }

    /**
     * Returns a command options
     * 
     * @author ikubicki
     * @return array
     */
    private function getCommandOptions(): array
    {
        $options = [];
        if ($this->session) {
            $options['session'] = $this->session;
        }
        return $options;
    }

    /**
     * Returns projection option
     * 
     * @author ikubicki
     * @param array $fields
     * @return array
     */
    private function projection(array $fields): array
    {
        if (count($fields)) {
            return[
                'projection' => array_fill_keys($fields, 1)
            ];
        }
        return [];
    }

    /**
     * Returns limit option
     * 
     * @author ikubicki
     * @param array $options
     * @return array
     */
    private function limit($options): array
    {
        if ($options[query::LIMIT] ?? false) {
            return [
                'limit' => $options[query::LIMIT],
            ];
        }
        return[];
    }

    /**
     * Returns sort option
     * 
     * @author ikubicki
     * @param array $options
     * @return array
     */
    private function sort($options): array
    {
        if ($options[query::SORT] ?? false) {
            return [
                'sort' => array_map(function($v) {
                    return $v == query::SORT_DESCENDING ? -1 : 1;
                }, $options[query::SORT]) 
            ];
        }
        return[];
    }
}