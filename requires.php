<?php

/*
 * These files are needed to make sure 
 * things keep working correctly
 */
require_once './Google/Client.php';
require_once './Google/Service/Analytics.php';
require_once './Google/Http/Batch.php';
require_once './libs/kint-0.9/Kint.class.php';
require_once './libs/Twig/Autoloader.php';

Twig_Autoloader::register();