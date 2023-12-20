<?php
namespace Tests\setting\viewmodels;

use DTM\setting\viewmodels\AdminSetting;
use Tests\Test as TestCase;

class AdminSettingTest extends TestCase
{
    private $AdminSetting;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->AdminSetting = new AdminSetting($container);
    }

    public function testForm()
    {
        $try = $this->AdminSetting->form();
        $this->assertIsArray($try);
    }
    
}