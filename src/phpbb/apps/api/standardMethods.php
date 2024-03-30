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

    /**
     * @var app $app
     */
    protected app $app;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param ?app $app
     */
    public function __construct(?app $app)
    {
        $this->app = $app;
    }

    /**
     * Setups application routes
     * 
     * @author ikubicki
     * @return void
     */
    abstract public function setup();

    /**
     * Handlers GET /{resource} request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     */
    public function getRecords(request $request, response $response, app $app): response
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

    /**
     * Handlers POST /{resource} request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws BadRequest
     */
    public function createRecord(request $request, response $response, app $app): response
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

    /**
     * Handlers GET /{resource}/:id request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function getRecord(request $request, response $response, app $app): response
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

    /**
     * Handlers PATCH /{resource}/:id request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function patchRecord(request $request, response $response, app $app): response
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

    /**
     * Handlers DELETE /{resource}/:id request
     * 
     * @author ikubicki
     * @param request $request
     * @param response $response
     * @param app $app
     * @return response
     * @throws ResourceNotFound
     */
    public function deleteRecord(request $request, response $response, app $app): response
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