<?php
namespace Tests\milestone\models;

use Tests\Test as TestCase;

class DocumentModelTest extends TestCase
{ 
    private $DocumentModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->DocumentModel = $container->get('DocumentModel');
        $HistoryEntity = $container->get('HistoryEntity');
        $DocumentEntity = $container->get('DocumentEntity');

        if (!static::$data)
        {
            $history = $HistoryEntity->findByPK(1);
            if (!$history)
            {
                $try = $HistoryEntity->add([
                    'id' => 1,
                    'object_id' => 1,
                    'object' => 'request',
                    'data' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 1,
                ]);
            }
            else
            {
                $HistoryEntity->update([
                    'object_id' => 1,
                    'object' => 'request',
                    'id' => 1,
                ]);
            }

            $document = $DocumentEntity->findOne(['request_id' => 1]);
            if (!$document)
            {
                $DocumentEntity->add([
                    'request_id' => 1,
                    'description' => 'description',
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ]);
            }

            static::$data = true;
        }
    }

    /**
     * @dataProvider dataSave
     */
    public function testSave($data, $result)
    {
        $try = $this->DocumentModel->save($data);
        $try = $try ? true : false;

        $this->assertEquals($try, $result);
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove($data, $result)
    {
        $try = $this->DocumentModel->remove($data);
        $this->assertEquals($try , $result);
    }

    public function dataSave()
    {
        return [
            [[
                'request_id' => '',
            ], false],
            [[
                'request_id' => 1,
                'description' => 'This is description',
            ], false],
        ];
    }

    public function dataRemove()
    {
        return [
            [2, true],
        ];
    }

    /**
     * @dataProvider dataGetHistory
     */
    public function testGetHistory($data, $result)
    {
        $try = $this->DocumentModel->getHistory($data);
        $try = is_array($try) ? true : false;

        $this->assertEquals($try, $result);
    }

    public function dataGetHistory()
    {
        return [
            [0, false],
            [1, true],
        ];
    }

    /**
     * @dataProvider dataRollBack
     */
    public function testRollBack($data, $result)
    {
        $try = $this->DocumentModel->rollback($data);
        $try = $try ? true : false;

        $this->assertEquals($try, $result);
    }

    public function dataRollBack()
    {
        return [
            // [0, false],
            [1, true],
        ];
    }
}
