<?php
namespace Lagged\PEAR\MirrorPackage;

/**
 * @desc Import abstract class
 */
use Lagged\PEAR\MirrorPackage\XML as XML;

class PirumXML extends XML
{
    public function getChannelUrl()
    {
        return (string) $this->xml->url;
    }
}
