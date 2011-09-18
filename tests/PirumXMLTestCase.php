<?php
require_once 'PHPUnit/Autoload.php';
set_include_path(dirname(__DIR__) . '/src:' . get_include_path());

require_once 'MirrorPackage/XML.php';
require_once 'MirrorPackage/PirumXML.php';

use Lagged\PEAR\MirrorPackage\PirumXML as PirumXML;

class PirumXMLTestCase extends \PHPUnit_Framework_TestCase
{
    public function testGetChannelUrl()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/pirum.xml');

        $p = new PirumXML($xml);
        $this->assertEquals('http://easybib.github.com/pear', $p->getChannelUrl());
    }
}
