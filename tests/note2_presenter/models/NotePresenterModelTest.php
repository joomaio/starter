<?php
namespace Tests\note2_presenter\models;

use Tests\Test as TestCase;

class NotePresenterModelTest extends TestCase
{ 
    private $NotePresenterModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->NotePresenterModel = $container->get('NotePresenterModel');
        $Note2Entity = $container->get('Note2Entity');

        if (!static::$data)
        {
            $find = $Note2Entity->findOne(['title' => 'test presenter']);
            if ($find)
            {
                $Note2Entity->remove($find['id']);
            }

            $find = $Note2Entity->findByPK(3);
            if(!$find)
            {
                $Note2Entity->add([
                    'title' => 'test presenter3',
                    'public_id' => '',
                    'id' => 2,
                    'alias' => '',
                    'data' => 'test presenter',
                    'tags' => '',
                    'type' => 'presenter',
                    'note_ids' => '',
                    'notice' => '',
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 0,
                    'locked_at' => date('Y-m-d H:i:s'),
                    'locked_by' => 0,
                ]);
            }
            static::$data = true;
        }
    }

    public function testReplaceContent()
    {
        $try = $this->NotePresenterModel->replaceContent('Test');
        $this->assertIsString($try);
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->NotePresenterModel->add($data);
        $this->assertEquals($try , $result);
    }

    public function dataAdd()
    {
        return [
            [[
                'title' => '', 
                'data' => 'test presenter', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], false],
            [[
                'title' => 'test presenter', 
                'data' => 'test presenter', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], true],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate($data, $result)
    {
        $try = $this->NotePresenterModel->update($data);
        $this->assertEquals($try , $result);
    }

    public function dataUpdate()
    {
        return [
            [[
                'id' => 0,
                'title' => 'test presenter3', 
                'data' => 'test presenter', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], false],
            [[
                'id' => 3,
                'title' => '', 
                'data' => 'test presenter', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0,          
            ], false],
            [[
                'id' => 3,
                'title' => 'test presenter3', 
                'data' => 'test presenter', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], true],
        ];
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove($id, $result)
    {
        $try = $this->NotePresenterModel->remove($id);
        $this->assertEquals($try , $result);
    }

    public function dataRemove()
    {
        return [
            [0, false],
            [2, true],
        ];
    }

    /**
     * @dataProvider dataGetDetail
     */
    public function testGetDetail($id, $result)
    {
        $try = $this->NotePresenterModel->getDetail($id);
        $try = is_array($try) ? true : false;
        $this->assertEquals($try , $result);
    }

    public function dataGetDetail()
    {
        return [
            [0, true],
            [2, true],
        ];
    }
    
}
