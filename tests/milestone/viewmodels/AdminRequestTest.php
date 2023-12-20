<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminRequest;
use Tests\Test as TestCase;

class AdminRequestTest extends TestCase
{
    private $AdminRequest;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1, 'milestone_id' => 1]);

        $this->AdminRequest = new AdminRequest($container);
    }

    public function testForm()
    {
        $try = $this->AdminRequest->form();
        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminRequest->getFormFields();
        $this->assertIsArray($try);
    }

    public function testDetail_request()
    {
        $try = $this->AdminRequest->detail_request();
        $this->assertIsArray($try);
    }
}
