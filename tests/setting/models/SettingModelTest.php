<?php
namespace Tests\setting\models;

use Tests\Test as TestCase;

class SettingModelTest extends TestCase 
{ 
    private $SettingModel;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->SettingModel = $container->get('SettingModel');
    }

    public function testGetTypes()
    {
        $try = $this->SettingModel->getTypes();
        $this->assertIsArray($try);
    }
}
