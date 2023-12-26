<?php
namespace Tests\tag\viewmodels;

use DTM\tag\viewmodels\AdminTags;
use SPT\Web\Gui\Listing;
use Tests\Test as TestCase;

class AdminTagsTest extends TestCase
{
    private $AdminTags;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();

        $this->AdminTags = new AdminTags($container);
    }

    public function testList()
    {
        $try = $this->AdminTags->list();
        $this->assertIsArray($try);
    }

    public function testGetColumns()
    {
        $try = $this->AdminTags->getColumns();
        $this->assertIsArray($try);
    }

    public function testFilter()
    {
        $try = $this->AdminTags->filter();
        $this->assertIsArray($try);
    }

    public function testGetFilterFields()
    {
        $try = $this->AdminTags->getFilterFields();
        $this->assertIsArray($try);
    }

    public function testRow()
    {
        $list = new Listing(['item'], 1, 1, []);
        $try = $this->AdminTags->row([], ['list' => $list]);
        $this->assertIsArray($try);
    }


}
