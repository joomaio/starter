<?php
namespace App\plugins\devtool\start\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class install extends ControllerMVVM
{
    public function list()
    {
        $list = $this->StartModel->getSolutions();
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

        $try = $this->StartModel->install($solution);
        if (!$try)
        {
            echo $this->StartModel->getError() ."\n";
        }
        else
        {
            echo "Install Done!\n";
        }

        return true;
    }
}