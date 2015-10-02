<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Debug\Debug;


set_time_limit(0);

require_once __DIR__.'/autoload.php';

Debug::enable();
require_once __DIR__.'/AppKernel.php';


$kernel = new AppKernel('dev', true);

$application = new Application($kernel);
$application->run();

