<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;

class cli extends ControllerMVVM
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

        if (file_exists($solution))
        {
            $try = $this->StarterModel->installFileZipCli($solution);
        } else {
            $try = $this->StarterModel->install($solution);
        }
        
        if ($try['success'])
        {
            echo "Install Done!\n";
        }

        return true;
    }

    public function uninstall()
    {
        $args = $this->request->cli->getArgs();
        $solution = isset($args[1]) ? $args[1] : '';

        $try = $this->StarterModel->uninstall($solution);
        if (!$try)
        {
            echo $this->StarterModel->getError() ."\n";
        }
        else
        {
            echo "Uninstall Done!\n";
        }

        return true;
    }

    public function checkavailability()
    {
        $entities = $this->DbToolModel->getEntities();
        foreach($entities as $entity)
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'success' : 'failed';
            echo str_pad($entity, 30) . $status ."\n";
        }

        echo "done.\n";
    }

    public function generatedata()
    {
        $entities = $this->DbToolModel->getEntities();
        foreach($entities as $entity)
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'success' : 'failed';
            echo str_pad($entity, 30) . $status ."\n";
        }
        echo "Generate data structure done\n";

        $try = $this->DbToolModel->truncate();
        if (!$try)
        {
            echo $this->DbToolModel->getError() . "\n";
            return ;
        }
        echo "Truncate table done\n";

        $try = $this->DbToolModel->generate();
        if (!$try)
        {
            echo $this->DbToolModel->getError(). "\n";
            return ;
        }

        echo "Generate data done\n";
        
        $try = $this->DbToolModel->setFolderUpload();
        if (!$try)
        {
            echo $this->DbToolModel->getError(). "\n";
            return ;
        }

        echo "Setup folder upload\n";
    }
}