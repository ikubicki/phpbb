<?php

namespace phpbb\apps\router;

use Throwable;

class error implements route
{
    public $callback;
    public $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
        $this->callback = function($request, $response) use ($throwable) {
            $code = $throwable->getCode() ?: $response::SERVER_ERROR;
            return $response->status($code)->send([
                'error' => $throwable->getMessage(),
            ]);
        };
    }
}