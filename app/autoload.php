<?php

//  Doctrine\Common\Annotations\AnnotationRegistry;
//  Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

/* ======================================================
 * TODO: PSR4 Research might allow me to explicitly set a bundle path
 * Might also be possible to directly manupilate psr0 paths to get CeradCoreBundle first
 * 
 * vendor/composer/ClassLoader.php is where the loaing happens
 */
/* ======================================================
 * I tried to set Cerad\Bundle\CoreBundle but the Cerad prefix overrides it
 * But this works as expected
 * 
 * Path Cerad C:\home\ahundiak\zayso2016\apptourns\app\..\..
 * Path Cerad C:\home\ahundiak\zayso2016\apptourns\app\..\..cerad2/src
 * Path Cerad C:\home\ahundiak\zayso2016\apptourns\vendor/cerad/cerad/src
 */

// Using DIRECTORY_SEPARATOR is not really needed
// ader->add('Cerad',sprintf("%s%s..%s..cerad2/src",__DIR__,DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR),true);
// ader->add('Cerad',sprintf("%s%s..%s..",          __DIR__,DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR),true);

// Order is important
$loader->add('Cerad', __DIR__ . '/../../cerad2/src',true);

//17 Feb 2014 - See README file.
//$loader->add('Cerad', __DIR__ . '/../..',           true);

// AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
