<?php
namespace App\devtool\starter\registers;

use SPT\Application\IApp;
use SPT\File;

class Bootstrap
{
    public static function initialize( IApp $app)
    {
        $container = $app->getContainer();
        if (!$container->exists('file')) 
        {
            $container->set('file', new File());
        }

        if ($container->exists('OptionEntity')) 
        {
            $container->get('OptionEntity')->checkAvailability();
        }

        if ($container->exists('config')) 
        {
            $config = $container->get('config');
            $OptionModel = $container->get('OptionModel');
            $config->adminTheme = $OptionModel->get('admin_theme', $config->adminTheme);
            $config->defaultTheme = $OptionModel->get('default_theme', $config->defaultTheme);
        }
    }
}