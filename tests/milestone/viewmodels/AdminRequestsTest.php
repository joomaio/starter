<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminRequests;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminRequestsTest extends TestCase
{
    private $AdminRequests;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1, 'milestone_id' => 1]);

        $this->AdminRequests = new AdminRequests($container);
    }

    public function testList()
    {
        $try = $this->AdminRequests->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminRequests->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminRequests->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminRequests->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminRequests->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }


}
