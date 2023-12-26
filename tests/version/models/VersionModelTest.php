<?php
namespace Tests\version\models;

use Tests\Test as TestCase;

class VersionModelTest extends TestCase
{ 
    private $VersionModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $OptionModel = $container->get('OptionModel');
        $OptionModel->set('version_level', 1);
        $OptionModel->set('version_level_deep', 2);
        
        $this->VersionModel = $container->get('VersionModel');

        $VersionEntity = $container->get('VersionEntity'); 

        // Prepare data
        if (!static::$data)
        {
            $find = $VersionEntity->findByPK(1);
            if(!$find)
            {
                $VersionEntity->add([
                    'id' => 1,
                    'name' => 'Test Version',
                    'release_date' => null,
                    'description' => '',
                    'version' => '0.1',
                    'status' => 1,
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ]);
            }
            static::$data = true;
        }
    }

    public function testGetVersion()
    {
        $try = $this->VersionModel->getVersion();
        $this->assertNotEmpty($try);
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->VersionModel->add($data);
        $try = $try ? true : false;
        
        $this->assertEquals($try, $result);
    }

    public function dataAdd()
    {
        return [
            [[
                'name' => '', 
                'release_date' => date('Y-m-d H:i:s'),
                'description' => '',
                'status' => 0,
            ], false],
            [[
                'name' => 'Test Version', 
                'release_date' => date('Y-m-d H:i:s'),
                'description' => '',
                'status' => 0,
            ], true],
        ];
    }

    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate($data, $result)
    {
        $try = $this->VersionModel->update($data);
        $try = $try ? true : false;
        
        $this->assertEquals($try, $result);
    }

    public function dataUpdate()
    {
        return [
            [[
                'id' => 0,
                'name' => '', 
                'description' => '', 
                'release_date' => date('Y-m-d H:i:s'),
                'status' => 0,
            ], false],
            [[
                'id' => 0,
                'name' => '', 
                'description' => '', 
                'release_date' => date('Y-m-d H:i:s'),
                'status' => 0,
            ], false],
            [[

                'id' => 1,
                'name' => 'Test Version 2', 
                'description' => '', 
                'release_date' => date('Y-m-d H:i:s'),
                'status' => 0,
            ], true],
        ];
    }

    /**
     * @dataProvider dataRemove
     */
    public function  testRemove($id, $result)
    {
        $try = $this->VersionModel->remove($id);
        $try = $try ? true : false;
        
        $this->assertEquals($try, $result);
    }

    public function dataRemove()
    {
        return [
            [0, false],
            [2, true],
        ];
    }
}
