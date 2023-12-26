<?php
namespace App\plugins\devtool\starter\registers;

use SPT\Application\IApp; 
use SPT\File;

class Dispatcher
{
    public static function dispatch(IApp $app)
    {
        $cName = $app->get('controller');
        $fName = $app->get('function');

        $check = php_sapi_name();
        if($check != 'cli')
        {
            // check asset key
            $StarterModel = $app->getContainer()->get('StarterModel');
            $permission = $StartModel->checkAccess();
            if (!$permission)
            {
                $app->raiseError('Invalid request!');
            }
        }

        $controller = 'App\plugins\devtool\starter\controllers\\'. $cName;
        if(!class_exists($controller))
        {
            $app->raiseError('Invalid controller '. $cName);
        }

        $controller = new $controller($app->getContainer());
        $controller->{$fName}();
        
        if ($check != 'cli')
        {
            $app->set('theme', $app->cf('adminTheme'));

            $fName = 'to'. ucfirst($app->get('format', 'html'));
    
            return $app->finalize(
                $controller->{$fName}()
            );
        }
       
        exit(0);
    }
}