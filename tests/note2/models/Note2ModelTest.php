<?php
namespace Tests\note2\models;

use Tests\Test as TestCase;

class Note2ModelTest extends TestCase
{ 
    private $Note2Model;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $container->get('request')->set('urlVars', ['id' => 1]);
        $this->Note2Model = $container->get('Note2Model');
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove($id, $result)
    {
        $try = $this->Note2Model->remove($id);
        
        $this->assertEquals($try, $result);
    } 

    public function dataRemove()
    {
        return [
            [0, false],
            [2, true],
        ];
    }

    public function testSearchAjax()
    {
        $try = $this->Note2Model->searchAjax('test', []);

        $this->assertIsArray($try);
    }
}
