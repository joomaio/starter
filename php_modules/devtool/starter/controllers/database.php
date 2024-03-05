<?php
namespace App\devtool\starter\controllers;

use SPT\Response;
use SPT\Web\ControllerMVVM;
 
class database extends ControllerMVVM
{
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