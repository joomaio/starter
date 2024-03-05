<?php
/**
 * SPT software - SDM Application
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: A web application based Joomla container
 * @version: 0.8
 * 
 */

 namespace Tests\libraries;
 
use SPT\Router\ArrayEndpoint as Router;
use SPT\Request\Base as Request;
use SPT\Response; 
use SPT\Query;
use SPT\Support\Loader;
use SPT\Extend\Pdo as PdoWrapper;
use SPT\Session\Instance as Session;
use SPT\Session\PhpSession;
use SPT\Session\DatabaseSession;
use SPT\Session\DatabaseSessionEntity;
use SPT\User\Instance as UserInstance;
use SPT\User\SPT\User as UserAdapter;
use DTM\user\entities\UserEntity;

use DTM\core\libraries\SDM as Base;
use SPT\Application\Configuration;
use SPT\Application\Token;

class App extends Base
{
    protected function prepareDb()
    {
        try{
            if(!$this->config->exists('db_test'))
            {
                throw new \Exception('Connection failed.', 500); 
            }
            $pdo = new PdoWrapper( $this->config->db_test );
            if(!$pdo->connected)
            {
                throw new \Exception('Connection failed.', 500); 
            }

            $this->container->set('query', new Query( $pdo, ['#__'=>  $this->config->db_test['prefix']]));
        } 
        catch(\Exception $e) 
        {
            $this->raiseError( $e->getMessage() );
        }
    }
}