<?php
namespace App\plugins\devtool\starter\models;

use SPT\Container\Client as Base;
use ZipArchive;
use SPT\Support\Loader;

class StarterAccessModel extends Base
{ 
    use \SPT\Traits\ErrorString;

    public function checkAccess($key)
    {
        // check setting
        $starter = $this->config->starter;
        if (!$starter || !isset($starter['access_key']) || !$starter['access_key'])
        {
            return false;
        }

        $access_key = $starter['access_key'];
        if($access_key != $key)
        {
            return false;
        }

        return true;
    }
}
