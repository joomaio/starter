<?php
namespace App\devtool\starter\models;

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
        $user_starter = $this->session->get('starter_user_'. $access_key, '');
        if($access_key != $key && !$user_starter)
        {
            return false;
        }

        return true;
    }

    public function login($username, $password)
    {
        if(!$username || !$password)
        {
            $this->error = 'Invalid username and password';
            return false;
        }

        $starter = $this->config->starter;
        if (!$starter || !isset($starter['username']) || !isset($starter['username']))
        {
            $this->error = 'Invalid username and password';  
            return false;
        } 

        $access_key = $starter['access_key'];
        if($username == $starter['username'] && $password == $starter['password'])
        {
            $this->session->set('starter_user_'. $access_key, true);
            return true;
        }

        return false;
    } 

    public function user()
    {
        $starter = $this->config->starter;
        if (!$starter || !isset($starter['access_key']) || !$starter['access_key'])
        {
            return false;
        }

        $access_key = $starter['access_key'];
        $user_starter = $this->session->get('starter_user_'. $access_key, '');

        return $user_starter;
    }
}
