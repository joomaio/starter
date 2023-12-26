<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminMilestones;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminMilestonesTest extends TestCase
{
    private $AdminMilestones;
    
    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();

        $this->AdminMilestones = new AdminMilestones($container);
    }

    public function testHome()
    {
        $try = $this->AdminMilestones->home();
        $this->assertIsArray($try);
    }

    public function testList()
    {
        $try = $this->AdminMilestones->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminMilestones->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminMilestones->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminMilestones->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminMilestones->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }
}
