<?php
namespace Tests\report\models;

use Tests\Test as TestCase;

class ReportModelTest extends TestCase
{ 
    private $ReportModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $container->get('request')->set('urlVars', ['id' => 1]);
        $this->ReportModel = $container->get('ReportModel');

    }

    public function testGetTypes()
    {
        $try = $this->ReportModel->getTypes();
        $this->assertIsArray($try);
    }

    public function updateStatus($data)
    {
        if (!$data || !is_array($data) || !$data['id']) {
            return false;
        }

        $try = $this->ReportEntity->update([
            'id' => $data['id'],
            'status' => $data['status'],
        ]);

        return $try;
    }

    public function remove($id)
    {
        if (!$id) {
            return false;
        }

        $types = $this->getTypes();
        $find = $this->ReportEntity->findByPK($id);
        if ($find) 
        {
            $type = isset($types[$find['type']]) ? $types[$find['type']] : [];
        }

        if (isset($type['remove_object'])) {
            $remove_object = $this->container->get($type['remove_object']);
        }

        if (is_object($remove_object)) 
        {
            if ($remove_object->remove($id)) 
            {
                return true;
            }
        } 
        else 
        {
            if ($this->ReportEntity->remove($id)) 
            {
                return true;
            }
        }

        return false;
    }
}
