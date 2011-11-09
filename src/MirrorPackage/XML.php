<?php
namespace Lagged\PEAR\MirrorPackage;

abstract class XML
{
    protected $xml;

    /**
     * __construct
     *
     * @param string $xml Path to the xml file.
     *
     * @return $this
     * @uses   self::setXml()
     */
    public function __construct($xml)
    {
        $this->setXml($xml);
    }

    /**
     * XML string and transform it into SimpleXML.
     *
     * @param string $xml Path to the xml file.
     *
     * @return $this
     * @throws \InvalidArgumentException When the file is not readable.
     * @throws \RuntimeException When parsing fails.
     */
    public function setXml($xml)
    {
        if (!is_readable($xml)) {
            throw new \InvalidArgumentException("File {$xml} is not readable.");
        }

        libxml_use_internal_errors(true);
        $this->xml = simplexml_load_file($xml);
        if ($this->xml === false) {
            $msg  = 'Could not load: ' . $xml;
            $msg .= ' Errors: ';
            foreach (libxml_get_errors() as $error) {
                $msg .= $error->message . ', ';
            }
            libxml_clear_errors();
            throw new \RuntimeException($msg);
        }
        return $this;
    }
}
