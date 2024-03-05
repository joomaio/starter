<?php
namespace Tests\milestone\models;

use Tests\Test as TestCase;

class RequestModelTest extends TestCase
{ 
    private $RequestModel;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->RequestModel = $container->get('RequestModel');
    }

    /**
     * @dataProvider dataRemove
     * @depends testAdd
     * @depends testUpdate
     */
    public function testRemove($id, $result)
    {
        $try = $this->RequestModel->remove($id);
        
        $this->assertEquals($try, $result);
    }   

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->RequestModel->add($data);
        $this->assertEquals($try , $result);
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate($data, $result)
    {
        $try = $this->RequestModel->update($data);
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
            [[
                'tags' => '',
            ], false],
            [[
                'milestone_id' => 1,
                'title' => 'Test Request',
                'tags' => '',
                'assignment' => '',
                'description' => 'This is Test Request',
                'start_at' => null,
                'finished_at' => null,
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
            [[
                'tags' => '',
            ], false],
            [[
                'title' => 'Test',
                'id' => '',
                'tags' => '',
            ], false],
            [[
                'id' => 1,
                'milestone_id' => 1,
                'title' => 'Test Request',
                'tags' => '',
                'assignment' => '',
                'description' => 'This is Test Request',
                'start_at' => null,
                'finished_at' => null,
                'modified_by' => 0,
                'modified_at' => date('Y-m-d H:i:s')
            ], true],
        ];
    }

    /**
     * @dataProvider dataGetTag
     */
    public function testGetTag($data, $result)
    {
        $try = $this->RequestModel->getTag($data);
        
        $this->assertEquals($try, $result);
    } 

    public function dataGetTag()
    {
        return [
            ['1,2', '1,2'],
            ['', ''],
        ];
    }

    public function excerpt()
    {
        $try = $this->RequestModel->excerpt('Example Text');
        $this->assertIsString($try);
    }

    /**
     * @dataProvider dataGetVersionNote
     */
    public function testGetVersionNote($data, $result)
    {
        $try = $this->RequestModel->getVersionNote($data);
        
        $try = is_array($try) ? true : false;
        $this->assertEquals($try, $result);
    }

    public function dataGetVersionNote()
    {
        return [
            [0, false],
            [1, true],
        ];
    }

    /**
     * @dataProvider dataRemoveVersion
     */
    public function testRemoveVersion($data, $result)
    {
        $try = $this->RequestModel->removeVersion($data);

        $this->assertEquals($try, $result);
    }

    public function dataRemoveVersion()
    {
        return [
            [2, true],
            ['', false],
        ];
    }

}
