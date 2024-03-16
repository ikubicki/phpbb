<?php

namespace phpbb\db\connectors;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Session;
use MongoDB\Driver\Query as MongoQuery;
use phpbb\db\query;
use phpbb\errors\ServerError;
use stdClass;

class mongo extends abstraction
{

    private ?Manager $mongodb = null;
    private ?Session $session = null;

    protected function connection(): Manager
    {
        if (!class_exists(Manager::class)) {
            throw new ServerError(sprintf(ServerError::DATABASE_NOT_INSTALLED, 'MongoDB'));
        }
        if (!$this->mongodb) {
            $this->mongodb = new Manager(
                $this->config->get('connection'),
                $this->config->get('options')->raw('uri'),
                $this->config->get('options')->raw('driver'),
            );
        }
        return $this->mongodb;
    }

    public function startTransaction(array $sessionOptions = [], array $transactionOptions = []): mongo
    {
        if ($this->session) {
            $this->session = $this->connection()->startSession($sessionOptions);
            $this->startTransaction($transactionOptions);
        }
        return $this;
    }

    public function commitTransaction(): mongo
    {
        if (!$this->session) {
            throw new ServerError("No active mongodb transaction");
        }
        $this->session->commitTransaction();
        return $this;
    }

    public function abortTransaction(): mongo
    {
        if (!$this->session) {
            throw new ServerError("No active mongodb transaction");
        }
        $this->session->abortTransaction();
        $this->session = null;
        return $this;
    }

    public function add(query $query, array $values): ?stdClass
    {

    }

    public function update(query $query, array $values): array
    {

    }

    public function query(query $query): array
    {
        $records = [];
        $query = new MongoQuery($query->query, $this->options($query));
        $cursor = $this->connection()->executeQuery('phpbb-auth.users', $query);
        foreach ($cursor as $document) {
            $document->{'$id'} = (string) $document->_id;
            unset($document->_id);
            $records[] = $document;
        }
        return $records;
    }

    public function remove(query $query): bool
    {

    }

    private function options(query $query)
    {
        return array_merge(
            $this->projection($query->fields),
            $this->sort($query->options),
            $this->limit($query->options),
        );
    }

    private function projection($fields)
    {
        if (count($fields)) {
            return[
                'projection' => array_fill_keys($fields, 1)
            ];
        }
        return [];
    }


    private function limit($options)
    {
        if ($options[query::LIMIT] ?? false) {
            return [
                'limit' => $options[query::LIMIT],
            ];
        }
        return[];
    }

    private function sort($options)
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