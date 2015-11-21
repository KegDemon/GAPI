<?php

require_once('./config.php');

use Core\Core;

$Core = new Core();

$loader = new Twig_Loader_Filesystem('./templates');

$twig = new Twig_Environment($loader, array(
    'cache' => './tmp',
    'debug' => true
));

#echo "<pre>";
#var_export($Core->unfiltered());
#echo "</pre>";

d($Core->unfiltered());

#echo $twig->render('page.html.twig', array('data' => $Core->getData(), 'titles' => $Core->getTitles(), 'dates' => $dates));
