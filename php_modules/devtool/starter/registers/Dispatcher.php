<?php
namespace App\devtool\starter\registers;

use SPT\Application\IApp; 
use SPT\File;

class Dispatcher
{
    public static function dispatch(IApp $app)
    {
        $cName = $app->get('controller');
        $fName = $app->get('function');

        $controller = 'App\devtool\starter\controllers\\'. $cName;
        if(!class_exists($controller))
        {
            $app->raiseError('Invalid controller '. $cName);
        }

        $controller = new $controller($app->getContainer());
        $controller->{$fName}();
        
        exit(0);
    }
}