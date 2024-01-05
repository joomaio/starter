<?php
namespace App\devtool\starter\models;

use SPT\Container\Client as Base;
use SPT\Support\Loader;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

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
        
        putenv('COMPOSER_HOME=' . ROOT_PATH);
        putenv('COMPOSER_VENDOR_DIR=' . ROOT_PATH.'vendor');
        putenv('COMPOSER=' . ROOT_PATH.'composer.json');

        $input = new ArrayInput(array('command' => 'update'));
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $try = true;
        try 
        {
            $result = $application->run($input);
        } catch (\Throwable $th) 
        {
            $try = false; 
            $this->error = $th->getMessage();           
        }
        
        // Todo: cache vendor and test after run composer update
        return $try;
    }
}
