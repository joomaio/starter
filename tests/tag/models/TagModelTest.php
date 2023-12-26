<?php
namespace Tests\tag\models;

use Tests\Test as TestCase;

class TagModelTest extends TestCase
{ 
    private $TagModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->TagModel = $container->get('TagModel');
        $TagEntity = $container->get('TagEntity');

        //create data sample
        if (!static::$data)
        {
            $find = $TagEntity->findOne(['name' => 'Test Tag']);
            if ($find)
            {
                $TagEntity->remove($find['id']);
            }
    
            $find = $TagEntity->findByPK(1);
            if (!$find)
            {
                $TagEntity->add([
                    'id' => 1,
                    'name' => 'Tag',
                    'description' => '',
                    'parent_id' => 0 ,
                ]);
            }

            static::$data = true;
        }
        
    }

    /**
     * @dataProvider dataRemove
     * @depends testAdd
     * @depends testUpdate
     */
    public function testRemove($id, $result)
    {
        $try = $this->TagModel->remove($id);
        $this->assertEquals($try, $result);
    }

    public function dataRemove()
    {
        return [
            [2, true],
            [3, true],
        ];
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->TagModel->add($data);
        $try = $try ? true : false;
        
        $this->assertEquals($try, $result);
    }

    public function dataAdd()
    {
        return [
            [[

            ], false],
            [[
               'name' => 'Test Tag', 
               'description' => '', 
               'parent_id' => 0, 
            ], true],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate($data, $result)
    {
        $try = $this->TagModel->update($data);
        $this->assertEquals($try, $result);
    }

    public function dataUpdate()
    {
        return [
            [[

            ], false],
            [[
                'name' => 'Test Tag Update', 
                'description' => '', 
                'id' => '',
                'parent_id' => 0, 
            ], false],
            [[
               'name' => 'Test Tag Update', 
               'description' => '', 
               'parent_id' => 0, 
               'id' => 1,
            ], true],
        ];
    }

    /**
     * @dataProvider dataSearch
     */
    public function testSearch($search, $ignores)
    {
        $try = $this->TagModel->search($search, $ignores);
        $this->assertIsArray($try);
    }

    public function dataSearch()
    {
        return [
            ['', ''],
            ['test', ''],
            ['test', []],
            ['test', ['1']],
        ];
    }
}
