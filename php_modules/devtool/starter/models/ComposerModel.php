<?php
namespace App\devtool\starter\models;

use SPT\Container\Client as Base;
use SPT\Support\Loader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

class ComposerModel extends Base
{ 
    use \SPT\Traits\ErrorString;

    private $entities;

    public function update($cli = false)
    {
        if($cli)
        {
            exec("composer update", $output, $return_var);
            return true;
        }
        
        $composer_data = array(
            'url' => 'https://getcomposer.org/composer.phar',
            'dir' => __DIR__.'/../../../../',
            'bin' => __DIR__.'/../../../../composer.phar',
            'json' => __DIR__.'/../../../../composer.json'
        );

        copy($composer_data['url'],$composer_data['bin']);
        require_once "phar://{$composer_data['bin']}/src/bootstrap.php";

        chdir($composer_data['dir']);
        putenv("COMPOSER_HOME={$composer_data['dir']}");
        putenv("OSTYPE=OS400");

        // $input = new ArrayInput(array('command' => 'update'));
        // $application = new Application();
        // $application->setAutoExit(false);
        // $application->setCatchExceptions(false);

        $try = true;
        try 
        {
            // $result = $application->run($input);
            $process = new Process(['composer', 'update']);
            $process->run();
            echo $process->getOutput();
        } catch (\Throwable $th) 
        {
            $try = false; 
            $this->error = $th->getMessage();           
        }
        
        // Todo: cache vendor and test after run composer update
        return $try;
    }
}
