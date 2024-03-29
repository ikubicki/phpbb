<?php

namespace phpbb;

use phpbb\serializer\abstraction;

/**
 * Output serializer
 */
class serializer
{

    /**
     * @var response $response
     */
    private response $response;

    /**
     * @var abstraction $serializer
     */
    private abstraction $serializer;

    /**
     * @var bool $buffer
     */
    private static bool $buffer = false;

    /**
     * The constructor
     * 
     * @author ikubicki
     * @param response $response
     */
    public function __construct(response $response)
    {
        $this->response = $response;
        switch($response->type) {
            case 'application/json':
                $this->serializer = new serializer\json($response);
                break;
            case 'application/xml':
                $this->serializer = new serializer\xml($response);
                break;
            default:
                $this->serializer = new serializer\stream($response);
                break;
        }
    }

    /**
     * Prints the output
     * 
     * @author ikubicki
     */
    public function output()
    {
        self::clean();
        self::start($this->response->request);
        if (!headers_sent()) {
            http_response_code($this->response->status ?: 200);
            if ($this->response->type) {
                header('Content-Type: ' . $this->response->type);
            }
            foreach($this->response->cookies as $name => $cookie) {
                setcookie($name, $cookie['value'], $cookie['options'] ?? []);
            }
            foreach($this->response->headers as $header => $value) {
                header("$header: $value");
            }
        }
        echo $this->serializer->__toString();
        self::flush();
    }

    /**
     * Returns output buffer handler name
     * 
     * @author ikubicki
     * @param request $request
     * @return ?string
     */
    private static function getBufferHandler(request $request = null): ?string
    {
        if ($request && $request->useGzip()) {
            return 'ob_gzhandler';
        }
        return null;
    }

    /**
     * Starts the output buffer
     * 
     * @static
     * @author ikubicki
     * @param ?request $request
     */
    public static function start(?request $request = null)
    {
        if (!self::$buffer) {
            self::$buffer = ob_start(self::getBufferHandler($request));
        }
    }

    /**
     * Cleans the buffer
     * 
     * @author ikubicki
     */
    public static function clean()
    {
        if (self::$buffer) {
            ob_clean();
        }
    }

    /**
     * Flushes the buffer
     * 
     * @author ikubicki
     */
    public static function flush()
    {
        if (self::$buffer) {
            ob_flush();
        }
    }

    /**
     * Calls serializer output method
     * 
     * @author ikubicki
     * @param response $response
     */
    public static function serialize(response $response)
    {
        (new serializer($response))->output();
    }
}