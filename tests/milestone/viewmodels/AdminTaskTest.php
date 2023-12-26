<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminTask;
use Tests\Test as TestCase;

class AdminTaskTest extends TestCase
{
    private $AdminTask;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1]);
        
        $this->AdminTask = new AdminTask($container);
    }

    public function testForm()
    {
        $try = $this->AdminTask->form();
        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminTask->getFormFields();
        $this->assertIsArray($try);
    }
}
