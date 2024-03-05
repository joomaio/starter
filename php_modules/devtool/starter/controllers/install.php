<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class install extends ControllerMVVM
{
    public function list()
    {
        $list = $this->StarterModel->getSolutions();
        foreach($list as $item)
        {
            echo $item->name." : ". $item->description. "\n";
        }

        return true;
    }

    public function install()
    {
        $args = $this->request->cli->getArgs();
        $solution = isset($args[1]) ? $args[1] : '';

        $try = $this->StarterCliModel->install($solution);
        
        if ($try)
        {
            echo "Install Done!\n";
            return true;
        }

        echo $this->StarterCliModel->getError() ."\n";

        return true;
    }
}