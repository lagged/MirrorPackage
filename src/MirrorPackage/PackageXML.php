<?php
namespace Lagged\PEAR\MirrorPackage;

class PackageXML
{
    protected $xml;

    public function __construct($xml)
    {
        $this->xml = simplexml_load_string($xml);
    }

    public function getChannel()
    {
        return (string) $this->xml->channel;
    }

    /**
     * Get other PEAR package dependencies.
     *
     * Returns an associative array with 'required' and 'optional' keys.
     *
     * The response can be used to display a list of other packages
     * which we need to mirror later. Or maybe we implement something
     * like that as well?
     *
     * @return array
     */
    public function getDependencies()
    {
        $deps             = array();
        $deps['required'] = array();
        $deps['optional'] = array();

        $deps['required'] = $this->getPackages('required');
        $deps['optional'] = $this->getPackages('optional');

        return $deps;
    }

    public function setChannel($channel)
    {
        $this->xml->channel = $channel;
        return $this;
    }

    public function __toString()
    {
        return $this->xml->asXml();
    }

    /**
     * Checks if the package has dependencies and then makes sure an array
     * is always returned (SimpleXML doesn't stack elements, when there are
     * not more than one.)
     *
     * @param string $type
     *
     * @return array
     */
    protected function getPackages($type = 'required')
    {
        if (!isset($this->xml->dependencies->$type)) {
            return array();
        }
        if (is_array($this->xml->dependencies->$type)) {
            return $this->xml->dependencies->$type;
        }
        return array(
            $this->xml->dependencies->$type,
        );
    }
}
