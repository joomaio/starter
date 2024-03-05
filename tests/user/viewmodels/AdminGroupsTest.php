<?php
namespace Tests\user\viewmodels;

use DTM\user\viewmodels\AdminGroups;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminGroupsTest extends TestCase
{
    private $AdminGroups;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');

        $this->AdminGroups = new AdminGroups($container);
    }

    public function testList()
    {
        $try = $this->AdminGroups->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminGroups->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminGroups->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminGroups->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminGroups->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }
}
