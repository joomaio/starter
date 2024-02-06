<?php
/**
 * SPT software - Pubilc application
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: How public an application 
 * 
 */

define('ROOT_PATH', __DIR__ . '/../');
define('APP_PATH', ROOT_PATH. 'php_modules/');
define('PUBLIC_PATH', __DIR__ . '/');
define('MEDIA_PATH', PUBLIC_PATH. 'media/');
define('SPT_VENDOR_PATH', ROOT_PATH. 'vendor/');
define('SPT_STORAGE_PATH', ROOT_PATH. 'storage/');

// disable warning
error_reporting(E_ALL & ~E_WARNING);

require ROOT_PATH. 'vendor/autoload.php';

$app = new \DTM\core\libraries\SDM(
    new \SPT\Container\Joomla,
    PUBLIC_PATH,
    APP_PATH,
    ROOT_PATH. 'config',
    'App'
);

$app->execute(ROOT_PATH. 'themes');
