<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace this with the path to your own project bootstrap file.
//require_once 'bootstrap.php';
define('APP_PATH', __DIR__. '/php_modules/');
//define('STORAGE_PATH', __DIR__. '/storage/');

require __DIR__. '/vendor/autoload.php';


// CLI: php vendor/bin/doctrine orm:schema-tool:create
// more details: https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/tools.html

// replace with mechanism to retrieve EntityManager in your app
$entityManager = \DTM\plugins\dtm\models\Doctrine\EntityManager::getInstance();

return ConsoleRunner::createHelperSet($entityManager);