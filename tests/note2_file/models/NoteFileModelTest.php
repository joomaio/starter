<?php
/**
 * SPT software - Model
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: Just a basic model
 * 
 */

namespace Tests\note2_file\models;

use Tests\note2_file\libraries\File;
use Tests\Test as TestCase;

class NoteFileModelTest extends TestCase
{ 
    private $NoteFileModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $file = new File();
        $container->set('file', $file);
        $container->get('request')->set('urlVars', ['id' => 1]);
        $this->NoteFileModel = $container->get('NoteFileModel');
        $FileEntity = $container->get('FileEntity');
        $Note2Entity = $container->get('Note2Entity');

        if (!static::$data)
        {
            $find = $Note2Entity->findByPK(2);
            if (!$find)
            {
                $Note2Entity->add([
                    'id' => 2,
                    'title' => 'test file',
                    'public_id' => '',
                    'alias' => '',
                    'data' => '',
                    'tags' => '',
                    'type' => 'file',
                    'status' => 0,
                    'note_ids' => '',
                    'notice' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 0,
                    'locked_at' => date('Y-m-d H:i:s'),
                    'locked_by' => 0,
                ]);
            }

            $find = $FileEntity->findOne(['note_id' => 2]);
            if (!$find)
            {
                $FileEntity->add([
                    'note_id' => 2,
                    'path' => 'media/attachments/' . date('Y/m/d'). '/test.png',
                    'file_type' => 'image',
                ]);
            }

            static::$data = true;
        }
    }
    
    public function testCreateFolderSave()
    {
        $try = $this->NoteFileModel->createFolderSave();
        $this->assertNotEmpty($try);
    }

    /**
     * @dataProvider dataUpload
     */
    public function testUpload($file, $result)
    {
        $try = $this->NoteFileModel->upload($file);
        $try = $try ? true : false;
        $this->assertEquals($try, $result);
    }

    public function dataUpload()
    {
        return [
            [[], false],
            [[
                'name' => ''
            ], false],
            [[
                'name' => 'test.txt',
                'tmp_name' => ''
            ], false],
            [[
                'name' => 'test.txt',
                'tmp_name' => 'test.txt'
            ], true],
        ];
    }

    public function testGetCurrentId()
    {
        $try = $this->NoteFileModel->getCurrentId();
        $this->assertEquals($try, 1);
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->NoteFileModel->add($data);
        $try = $try ? true : false;
        $this->assertEquals($try, $result);
    }

    public function dataAdd()
    {
        return [
            [[
                'file' => [
                    'name' => '',
                ],
            ], false],
            [[
                'file' => [
                    'name' => '',
                ],
                'title' => '',
            ], false],
            [[
                'title' => 'test',
                'file' => [
                    'name' => '',
                ],
            ], false],
            [[
                'title' => 'test',
                'file' => [
                    'name' => '',
                ],
            ], false],
            [[
                'title' => 'test',
                'file' => [
                    'name' => 'test.txt',
                    'tmp_name' => '',
                ]
            ], false],
            [[
                'title' => 'test',
                'file' => [
                    'name' => 'test.txt',
                    'tmp_name' => 'test.txt',
                ],
                'notice' => 'test',
            ], true],
        ];
    }
    
    /**
     * @dataProvider dataUpdate
     * @depends testAdd
     */
    public function testUpdate($data, $result)
    {
        $try = $this->NoteFileModel->update($data);
        $try = $try ? true : false;
        $this->assertEquals($try, $result);
    }

    public function dataUpdate()
    {
        return [
            [[
                'title' => 'test',
                'id' => 0,
            ], false],
            [[
                'file' => [
                    'name' => ''
                ],
                'title' => 'test',
                'id' => 2,
            ], true],
        ];
    }

    /**
     * @dataProvider dataRemove
     * @depends testAdd
     * @depends testUpdate
     * @depends testGetDetail
     */
    public function testRemove($id, $result)
    {
        $try = $this->NoteFileModel->remove($id);
        
        $this->assertEquals($try, $result);
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
     * @depends testAdd
     * @depends testUpdate
     */
    public function testGetDetail($id, $result)
    {
        $try = $this->NoteFileModel->getDetail($id);
        $try = $try ? true : false;
        $this->assertEquals($try, $result);
    } 

    public function dataGetDetail()
    {
        return [
            [0, false],
            [2, true],
        ];
    }
}
