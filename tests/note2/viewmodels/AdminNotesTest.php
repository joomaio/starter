<?php

namespace Tests\note2\viewmodels;

use App\plugins\note2\viewmodels\AdminNotes;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminNotesTest extends TestCase
{
    private $AdminNotes;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['id' => 1]);

        $this->AdminNotes = new AdminNotes($container);
    }

    public function testList()
    {
        $try = $this->AdminNotes->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminNotes->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminNotes->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminNotes->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminNotes->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }
}
