<?php
namespace Tests\note2_html\models;

use Tests\Test as TestCase;

class NoteHtmlModelTest extends TestCase
{ 
    private $NoteHtmlModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->NoteHtmlModel = $container->get('NoteHtmlModel');
        $Note2Entity = $container->get('Note2Entity');

        if (!static::$data)
        {
            $find = $Note2Entity->findOne(['title' => 'test html']);
            if ($find)
            {
                $Note2Entity->remove($find['id']);
            }

            $find = $Note2Entity->findByPK(2);
            if(!$find)
            {
                $Note2Entity->add([
                    'title' => 'test html3',
                    'public_id' => '',
                    'id' => 2,
                    'alias' => '',
                    'data' => 'test html',
                    'tags' => '',
                    'type' => 'html',
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
        $try = $this->NoteHtmlModel->replaceContent('Test');
        $this->assertIsString($try);
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->NoteHtmlModel->add($data);
        $this->assertEquals($try , $result);
    }

    public function dataAdd()
    {
        return [
            [[
                'title' => '', 
                'data' => 'test html', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0,  
            ], false],
            [[
                'title' => 'test html', 
                'data' => 'test html', 
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
        $try = $this->NoteHtmlModel->update($data);
        $this->assertEquals($try , $result);
    }

    public function dataUpdate()
    {
        return [
            [[
                'id' => 0,
                'title' => 'test html3', 
                'data' => 'test html', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], false],
            [[
               'title' => '', 
                'id' => 0,
                'data' => 'test html', 
                'tags' => [], 
                'notice' => '', 
                'status' => 0, 
            ], false],
            [[
                'id' => 2,
                'title' => 'test html3', 
                'data' => 'test html', 
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
        $try = $this->NoteHtmlModel->remove($id);
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
        $try = $this->NoteHtmlModel->getDetail($id);
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
