<?php

namespace phpbb\serializer;

use phpbb\response;
use XMLWriter;

class xml extends abstraction
{

    protected response $response;

    public function __toString(): string
    {
        $xml = $this->createDocument();
        $this->load($xml, 'response', $this->response->body);
        return $xml->outputMemory(true);
    }

    private function createDocument()
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0');
        return $xml;
    }

    private function load(XMLWriter $xml, $key, $data): XMLWriter {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (is_array($data)) {
            $xml->startElement($key);
            foreach($data as $property => $value) {
                $this->load($xml, $property, $value);
            }
            $xml->endElement();
        }
        else {
            $xml->writeElement($key, $data);
        }
        return $xml;
    }
}