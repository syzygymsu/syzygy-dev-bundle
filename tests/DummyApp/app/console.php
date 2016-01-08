<?php

$loader = require __DIR__ . '/../../../../../vendor/autoload.php';
$loader->set('AppBundle\\', __DIR__ . '/../src');

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

require __DIR__ . '/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$kernel = new AppKernel('dev', true);
$application = new Application($kernel);
$application->run(new ArgvInput());
