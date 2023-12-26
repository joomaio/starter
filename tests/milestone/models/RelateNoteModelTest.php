<?php
namespace Tests\milestone\models;

use Tests\Test as TestCase;

class RelateNoteModelTest extends TestCase
{ 
    private $RelateNoteModel;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->RelateNoteModel = $container->get('RelateNoteModel');
    }

    /**
     * @dataProvider dataRemoveByNote
     */
    public function testRemoveByNote($data, $result)
    {
        $try = $this->RelateNoteModel->removeByNote($data);
        $try = $try ? true : false;

        $this->assertEquals($try, $result);
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove($data, $result)
    {
        $try = $this->RelateNoteModel->remove($data);
        $this->assertEquals($try , $result);
    }

    public function dataRemoveByNote()
    {
        return [
            [0, false],
            [2, true],
        ];
    }

    public function dataRemove()
    {
        return [
            [0, false],
            [2, true],
        ];
    }

    /**
     * @dataProvider dataAddNote
     */
    public function testAddNote($notes, $request, $result)
    {
        $try = $this->RelateNoteModel->addNote($notes, $request);
        $this->assertEquals($try , $result);
    }

    public function dataAddNote()
    {
        return [
            [[], '', false],
            [[], '1', false],
            [[1], '1', true],
            [[1,2], '1', true],
        ];
    }

    /**
     * @dataProvider dataGetNotes
     */
    public function testGetNotes($request_id, $search, $result)
    {
        $try = $this->RelateNoteModel->getNotes($request_id, $search);
        $try = is_array($try) ? true : false;

        $this->assertEquals($try , $result);
    }

    public function dataGetNotes()
    {
        return [
            [1, '', true],
        ];
    }

    /**
     * @dataProvider dataUpdateAlias
     */
    public function testUpdateAlias($data, $result)
    {
        $try = $this->RelateNoteModel->updateAlias($data);
        $try = $try ? true : false;

        $this->assertEquals($try , $result);
    }

    public function dataUpdateAlias()
    {
        return [
            [[], false],
            [['id' => ''], false],
            [[
                'id' => 1,
                'alias' => 'Test',
            ], true],
        ];
    }

}
