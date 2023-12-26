<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminDocument;
use Tests\Test as TestCase;

class AdminDocumentTest extends TestCase
{
    private $AdminDocument;
    
    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1]);

        $this->AdminDocument = new AdminDocument($container);
    }

    public function testForm()
    {
        $try = $this->AdminDocument->form();

        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminDocument->getFormFields();

        $this->assertIsArray($try);
    }
}


