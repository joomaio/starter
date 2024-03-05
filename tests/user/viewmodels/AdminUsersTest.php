<?php
namespace Tests\user\viewmodels;

use DTM\user\viewmodels\AdminUsers;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminUsersTest extends TestCase
{
    private $AdminUsers;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');

        $this->AdminUsers = new AdminUsers($container);
    }

    public function testList()
    {
        $try = $this->AdminUsers->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminUsers->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminUsers->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminUsers->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminUsers->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }
}
