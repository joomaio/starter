<?php
namespace Tests\ready;

use Tests\Test as TestCase;
use SPT\Application\Configuration;
use SPT\Extend\Pdo as PdoWrapper;
use SPT\Support\Loader;

class DBConnectTest extends TestCase
{
    public function testDB()
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $config = $container->get('config');
        $pdo = new PdoWrapper(
            $config->db,
        );

        $try = $pdo->connected ? true : false;
        if (!$try)
        {
            throw new \Exception( 'Incorrect database connection.');
        }

        $this->assertTrue($try);
    }

}