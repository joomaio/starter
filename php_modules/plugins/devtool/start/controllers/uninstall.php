<?php
namespace App\plugins\devtool\start\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class uninstall extends ControllerMVVM
{
    public function uninstall()
    {
        $args = $this->request->cli->getArgs();
        $solution = isset($args[1]) ? $args[1] : '';

        $try = $this->StartModel->uninstall($solution);
        if (!$try)
        {
            echo $this->StartModel->getError() ."\n";
        }
        else
        {
            echo "Uninstall Done!\n";
        }

        return true;
    }
}