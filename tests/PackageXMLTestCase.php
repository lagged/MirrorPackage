<?php
require_once 'PHPUnit/Autoload.php';
set_include_path(dirname(__DIR__) . '/src:' . get_include_path());

require_once 'MirrorPackage/XML.php';
require_once 'MirrorPackage/PackageXML.php';

use Lagged\PEAR\MirrorPackage\PackageXML as PackageXML;

class PackageXMLTestCase extends \PHPUnit_Framework_TestCase
{
    public function testGetChannel()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/rediska-package.xml');

        $p = new PackageXML($xml);
        $this->assertEquals('pear.geometria-lab.net', $p->getChannel());
        $this->assertInstanceOf('Lagged\PEAR\MirrorPackage\PackageXML', $p->setChannel('easybib.github.com/pear'));
        $this->assertEquals('easybib.github.com/pear', $p->getChannel());
    }

    public static function xmlProvider()
    {
        return array(
            array('request2-package.xml'),
            array('honey-package.xml'),
        );
    }

    /**
     * @dataProvider xmlProvider
     */
    public function testGetDependencies($xml)
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/' . $xml);

        $p = new PackageXML($xml);
        $d = $p->getDependencies();

        $this->assertInternalType('array', $d);
        $this->assertArrayHasKey('required', $d);
        $this->assertArrayHasKey('optional', $d);

        $this->assertInternalType('array', $d['required']);
        $this->assertInternalType('array', $d['optional']);
    }
}
