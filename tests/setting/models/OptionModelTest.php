<?php
namespace Tests\setting\models;

use Tests\Test as TestCase;

class OptionModelTest extends TestCase 
{ 
    private $OptionModel;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->OptionModel = $container->get('OptionModel');
    }

    public function testGet()
    {
        $try = $this->OptionModel->get('key');
        $this->assertNotFalse($try);
    }

    public function testSet()
    {
        $try = $this->OptionModel->set('key', 'data');
        $this->assertNotFalse($try);
    }
}
