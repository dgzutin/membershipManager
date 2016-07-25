<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 14.05.16
 * Time: 12:55
 */

// cli-config.php
use App\EmFactory;


$appConfig = file_get_contents(__DIR__."/src/App/config/config.json");
$jsonConfig = json_decode($appConfig);

$em = new EmFactory($jsonConfig, true);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em->createEntityManager());