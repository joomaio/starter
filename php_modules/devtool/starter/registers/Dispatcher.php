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

        $check = php_sapi_name();
        $app->set('theme', $app->cf('defaultTheme'));
        $container = $app->getContainer();
        if($check != 'cli')
        {
            // check asset key
            $StarterAccessModel = $container->get('StarterAccessModel');
            $access_key = $container->get('request')->get->get('access_key', '', 'string');
            $permission = $StarterAccessModel->checkAccess($access_key);
            if (!$permission)
            {
                $app->raiseError('Invalid request!');
            }
        }

        $controller = 'App\devtool\starter\controllers\\'. $cName;
        if(!class_exists($controller))
        {
            $app->raiseError('Invalid controller '. $cName);
        }

        $controller = new $controller($container);
        $controller->{$fName}();

        if ($check != 'cli')
        {
            $fName = 'to'. ucfirst($app->get('format', 'html'));
    
            return $app->finalize(
                $controller->{$fName}()
            );
        }
       
        exit(0);
    }
}