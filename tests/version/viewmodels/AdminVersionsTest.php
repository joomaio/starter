<?php
namespace Tests\version\viewmodels;

use App\plugins\version\viewmodels\AdminVersions;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminVersionsTest extends TestCase
{
    private $AdminVersions;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['version_id' => 1]);

        $this->AdminVersions = new AdminVersions($container);
    }

    public function testList()
    {
        $try = $this->AdminVersions->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminVersions->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminVersions->filter();
        $this->assertIsArray($try);
    }

    public function getFilterFields()
    {
        $try = $this->AdminVersions->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminVersions->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }


}
