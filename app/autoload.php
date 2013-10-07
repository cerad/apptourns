<?php

//  Doctrine\Common\Annotations\AnnotationRegistry;
//  Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add('Cerad',   __DIR__  . '/../../cerad2/src',true);

// AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
