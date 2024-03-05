<?php
namespace Tests\milestone\models;

use Tests\Test as TestCase;

class MilestoneModelTest extends TestCase
{ 
    private $MilestoneModel;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->MilestoneModel = $container->get('MilestoneModel');
    }

    /**
     * @dataProvider dataRemove
     * @depends testAdd
     * @depends testUpdate
     */
    public function testRemove($id, $result)
    {
        $try = $this->MilestoneModel->remove($id);
        
        $this->assertEquals($try, $result);
    }   

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->MilestoneModel->add($data);
        $this->assertEquals($try , $result);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate($data, $result)
    {
        $try = $this->MilestoneModel->update($data);
        $this->assertEquals($try , $result);
    }

    public function dataRemove()
    {
        return [
            [2, true],
        ];
    }

    public function dataAdd()
    {
        return [
            [[], false],
            [[
                'title' => 'Test Milestone',
                'description' => 'This is test milestone',
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => date('Y-m-d H:i:s'),
                'status' => 1,
                'created_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'modified_by' => 0,
                'modified_at' => date('Y-m-d H:i:s')
            ], true],
        ];
    }

    public function dataUpdate()
    {
        return [
            [[], false],
            [[
                'title' => 'Test',
                'id' => '',
            ], false],
            [[
                'id' => 1,
                'title' => 'Test Milestone',
                'description' => 'This is test milestone',
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => date('Y-m-d H:i:s'),
                'status' => 1,
                'modified_by' => 0,
                'modified_at' => date('Y-m-d H:i:s')
            ], true],
        ];
    }
}
