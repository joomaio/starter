<?php
/**
 * SPT software - Demo application
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: How we start an MVC
 * 
 */
namespace Tests;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    static $app;

    public function prepareApp()
    {
        if (static::$app) return static::$app;
        define('ROOT_PATH', __DIR__ . '/../');
        define('APP_PATH', ROOT_PATH. 'php_modules/');
        define('PUBLIC_PATH', ROOT_PATH . 'public/');
        define('MEDIA_PATH', PUBLIC_PATH. 'media/');
        define('SPT_STORAGE_PATH', PUBLIC_PATH);

        require ROOT_PATH. 'vendor/autoload.php';

        $_SERVER['HTTP_HOST'] = 'Test';
        $_SERVER['REQUEST_URI'] = '/';

        static::$app = new \Tests\libraries\App(
            new \SPT\Container\Joomla,
            PUBLIC_PATH,
            APP_PATH. 'plugins/',
            APP_PATH. 'config.php',
            'App'
        );
        return static::$app;
    }
}