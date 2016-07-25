<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 19.05.16
 * Time: 15:26
 */
namespace App;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__."/../../vendor/autoload.php";

class EmFactory
{
    protected $appConfig;
    protected $isDevMode;

    public function __construct($appConfig, $isDevMode)
    {
        $this->appConfig = $appConfig;
        $this->isDevMode = $isDevMode;
    }

    public function createEntityManager()
    {

        $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/Entity"), $this->isDevMode);


// database configuration parameters
        $conn = array(
            'driver' => $this->appConfig->dbConn->driver,
            'dbname' => $this->appConfig->dbConn->dbname,
            'user' => $this->appConfig->dbConn->user,
            'password' => $this->appConfig->dbConn->password,
            'host' => $this->appConfig->dbConn->host
        );

// obtaining the entity manager
        return EntityManager::create($conn, $config);

    }


}

