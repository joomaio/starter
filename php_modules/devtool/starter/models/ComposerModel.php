<?php
namespace App\devtool\starter\models;

use SPT\Container\Client as Base;
use SPT\Support\Loader;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ComposerModel extends Base
{ 
    use \SPT\Traits\ErrorString;

    private $entities;

    public function update($cli = false)
    {
        $result = array(
            'success' => false,
            'message' => ''
        );

        if($cli)
        {
            exec("composer update", $output, $return_var);
            $result['success'] = true;
            return $result;
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

        $input = new ArrayInput(array('command' => 'update'));
        $output = new BufferedOutput();
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        try 
        {
            $application->run($input, $output);
            
            $message = $output->fetch();
            $result['success'] = true;
            $result['message'] = nl2br($message);
        } catch (\Throwable $th) 
        {
            $result['message'] = $th->getMessage();
            $this->error = $th->getMessage();           
        }
        
        // Todo: cache vendor and test after run composer update
        return $result;
    }
}
