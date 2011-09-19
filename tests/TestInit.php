<?php
require_once 'PHPUnit/Autoload.php';
require_once dirname(__DIR__) . '/src/MirrorPackage.php';

spl_autoload_register(array('Lagged\PEAR\MirrorPackage', 'autoload'));
