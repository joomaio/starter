<?php
namespace Tests\tag\viewmodels;

use DTM\tag\viewmodels\AdminTag;
use Tests\Test as TestCase;

class AdminTagTest extends TestCase
{
    private $AdminTag;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['id' => 1]);

        $this->AdminTag = new AdminTag($container);
    }

    public function testForm()
    {
        $try = $this->AdminTag->form();
        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminTag->getFormFields();
        $this->assertIsArray($try);
    }
}
