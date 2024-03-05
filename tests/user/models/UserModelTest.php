<?php
namespace Tests\user\models;

use App\plugins\user\entities\GroupEntity;
use App\plugins\user\entities\UserEntity;
use App\plugins\user\entities\UserGroupEntity;
use App\plugins\user\models\UserGroupModel;
use App\plugins\user\models\UserModel;
use Tests\Test as TestCase;

class UserModelTest extends TestCase
{ 
    private $UserModel;
    static $data;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $this->UserModel = $container->get('UserModel');
        $UserEntity = $container->get('UserEntity');

        // Prepare data
        if (!static::$data)
        {
            $find = $UserEntity->findOne(['username' => 'admin']);
            if ($find)
            {
                $UserEntity->update([
                    'id' => $find['id'],
                    'password' => '123123',
                    'name' => 'admin',
                    'username' => 'admin',
                    'email' => 'admin@g.com',
                    'confirm_password' => '123123',
                    'status' => 1,
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ]);
            }

            $find = $UserEntity->findOne(['username' => 'admin_test']);
            if ($find)
            {
                $UserEntity->remove($find['id']);
            }

            $find = $UserEntity->findByPK(2);
            if(!$find)
            {
                $UserEntity->add([
                    'id' => 2,
                    'name' => 'admin 2',
                    'username' => 'admin2',
                    'email' => 'admin@g.com',
                    'password' => '123123',
                    'confirm_password' => '123123',
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

    /**
     * @dataProvider dataGetAccessByGroup
     */
    public function testGetAccessByGroup($data, $result)
    {
        $try = $this->UserModel->getAccessByGroup($data);
        $try = is_array($try) ? true : false;
        $this->assertEquals($try, $result);
    }

    public function dataGetAccessByGroup()
    {
        return [
            ['', false],
            [[1], true],
        ];
    }

    /**
     * @dataProvider dataAdd
     */
    public function testAdd($data, $result)
    {
        $try = $this->UserModel->add($data);
        $try = $try ? true : false;
        $this->assertEquals($try, $result);

    }

    public function dataAdd()
    {
        return [
            [[], false],
            [
                [
                    'username' => '',
                    'password' => '',
                    'email' => '',
                ], false],
            [
                [
                    'username' => '',
                    'password' => '',
                    'email' => 'admin@g.com',
                ], false],
            [
                [
                    'name' => 'Test',
                    'username' => 'admin_test',
                    'password' => '123123',
                    'confirm_password' => '123123',
                    'status' => 1,
                    'email' => 'admin2@g.com',
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ], true
            ],
        ];
    }

     /**
     * @dataProvider dataUpdate
     * @depends testAdd
     */
    public function testUpdate($data, $result)
    {
        $try = $this->UserModel->update($data);
        $this->assertEquals($try, $result);
    }

    public function dataUpdate()
    {
        return [
            [[], false],
            [
                [
                    'username' => '',
                    'password' => '',
                    'email' => '',
                    'id' => '',
                ], false],
            [
                [
                    'name' => 'Test',
                    'username' => 'Test',
                    'id' => 2,
                    'status' => 1,
                    'email' => 'admin3@g.com',
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ], true
            ],
        ];
    }

    /**
     * @dataProvider dataRemove
     */
    public function testRemove($data, $result)
    {
        $try = $this->UserModel->remove($data);
        $this->assertEquals($try, $result);
    }

    public function dataRemove()
    {
        return [
            [0, false],
            [3, true],
        ];
    }

    /**
     * @dataProvider dataLogin
     * @depends testAdd
     */
    public function testLogin($username, $password, $result)
    {
        $try = $this->UserModel->login($username, $password);
        $this->assertEquals($try, $result);
    }
    
    public function dataLogin()
    {
        return [
            ['', '123123', false],
            ['admin', '', false],
            ['admin', '123123', true],
        ];
    }
}
