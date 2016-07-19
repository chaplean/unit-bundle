<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require_once __DIR__  . '/../vendor/autoload.php';

if (class_exists('\Doctrine\Common\Annotations\AnnotationRegistry')) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
}

return $loader;
