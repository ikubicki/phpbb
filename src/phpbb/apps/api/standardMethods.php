<?php

namespace phpbb\apps\api;

use phpbb\app;
use phpbb\db\errors\DuplicateError;
use phpbb\db\errors\FieldError;
use phpbb\errors\BadRequest;
use phpbb\request;
use phpbb\response;
use phpbb\errors\ResourceNotFound;

abstract class standardMethods
{

    const COLLECTION = 'undefined';

    protected app $app;

    public function __construct(app $app = null)
    {
        $this->app = $app;
    }

    abstract public function setup();

    public function getRecords(request $request, response $response, app $app)
    {
        $filters = [];
        if ($request->query('uuid')) {
            $filters['uuid'] = $request->query('uuid');
        }
        if ($request->query('name')) {
            $filters['name'] = $request->query('name');
        }
        if ($request->query('creator')) {
            $filters['creator'] = $request->query('creator');
        }
        $records = $app->plugin('db')->collection(static::COLLECTION)->find($filters);
        return $response->status($response::OK)->send($records);
    }

    public function createRecord(request $request, response $response, app $app)
    {
        try {
            $record = $app->plugin('db')->collection(static::COLLECTION)->create();
            $record->setMany($request->body->toArray());
            $record->save();
        }
        catch(DuplicateError) {
            throw new BadRequest("Name is already taken");
        }
        catch(FieldError $error) {
            throw new BadRequest($error->error);
        }
        return $response->status($response::OK)->send($record);
    }

    public function getRecord(request $request, response $response, app $app)
    {
        if (empty($request->uri->param('id'))) {
            throw new ResourceNotFound($request);
        }
        $collection = $app->plugin('db')->collection(static::COLLECTION);
        $record = $collection->findOne([
            'uuid' => $request->uri->param('id')
        ]);
        if (!$record) {
            throw new ResourceNotFound($request);
        }
        $data = $record->export();
        if ($request->query('references')) {
            foreach ($record->getReferences() as $field => $reference) {
                $data[$field] = $reference->getEntity($record);
            }
        }
        return $response->status($response::OK)->send($data);
    }

    public function patchRecord(request $request, response $response, app $app)
    {
        if (empty($request->uri->param('id'))) {
            throw new ResourceNotFound($request);
        }
        $record = $app->plugin('db')->collection(static::COLLECTION)->findOne([
            'uuid' => $request->uri->param('id')
        ]);
        if (!$record) {
            throw new ResourceNotFound($request);
        }
        $record->setMany($request->body->toArray());
        $record->save();
        return $response->status($response::OK)->send($record);
    }

    public function deleteRecord(request $request, response $response, app $app)
    {
        if (empty($request->uri->param('id'))) {
            throw new ResourceNotFound($request);
        }
        $record = $app->plugin('db')->collection(static::COLLECTION)->findOne([
            'uuid' => $request->uri->param('id')
        ]);
        if (!$record) {
            throw new ResourceNotFound($request);
        }
        $record->delete();
        return $response->status($response::NO_CONTENT);
    }
}