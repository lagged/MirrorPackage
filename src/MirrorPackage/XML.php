<?php
namespace Lagged\PEAR\MirrorPackage;

abstract class XML
{
    protected $xml;

    public function __construct($xml)
    {
        $this->xml = simplexml_load_string($xml);
    }
}
