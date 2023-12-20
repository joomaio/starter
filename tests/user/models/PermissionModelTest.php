<?php
namespace Tests\user\models;

use Tests\Test as TestCase;

class PermissionModelTest extends TestCase
{
    private $PermissionModel;
    private $request;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $_SERVER['REQUEST_METHOD'] = 'get';
        $this->PermissionModel = $container->get('PermissionModel');
        $user = $container->get('user');
        $user->set('id', 1);
        $this->request = $container->get('request');
    }

    public function testGetAccess()
    {
        $try = $this->PermissionModel->getAccess();
        $this->assertIsArray($try);
    }

    public function testGetAccessByUser()
    {
        $try = $this->PermissionModel->getAccessByUser();
        $this->assertIsArray($try);
    }
}