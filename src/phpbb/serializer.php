<?php

namespace phpbb;

use phpbb\serializer\abstraction;

class serializer
{
    private response $response;
    private abstraction $serializer;
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

    public function output() 
    {
        ob_start($this->getBufferHandler());
        foreach($this->response->headers as $header => $value) {
            header("$header: $value");
        }
        echo $this->serializer->__toString();
        ob_flush();
        exit;
    }

    private function getBufferHandler(): ?string
    {
        if ($this->response->request->useGzip()) {
            return 'ob_gzhandler';
        }
        return null;
    }

    public static function serialize($response)
    {
        (new serializer($response))->output();
    }
}