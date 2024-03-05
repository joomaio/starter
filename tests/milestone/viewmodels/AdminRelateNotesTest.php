<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminRelateNotes;
use Tests\Test as TestCase;

class AdminRelateNotesTest extends TestCase
{   
    private $AdminRelateNotes;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1]);

        $this->AdminRelateNotes = new AdminRelateNotes($container);
    }

    public function testJavascript()
    {
        $try = $this->AdminRelateNotes->javascript();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminRelateNotes->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminRelateNotes->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminRelateNotes->getFilterFields();
        $this->assertIsArray($try);
    }
}
