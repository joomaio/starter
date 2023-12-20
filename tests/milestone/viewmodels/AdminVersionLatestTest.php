<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminVersionLatest;
use Tests\Test as TestCase;
use SPT\Web\Gui\Listing;

class AdminVersionLatestTest extends TestCase
{
    private $AdminVersionLatest;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1]);

        $VersionEntity = $container->get('VersionEntity');
        $check = $VersionEntity->list(0 , 1);
        if (!$check)
        {
            $VersionEntity->add([
                'name' => 'Test Version', 
                'release_date' => null,
                'version' => '1',
                'description' => '',
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1,
                'modified_at' => date('Y-m-d H:i:s'),
                'modified_by' => 1,
            ]);
        }
        $this->AdminVersionLatest = new AdminVersionLatest($container);
    }

    public function testList()
    {
        $try = $this->AdminVersionLatest->list();
        $this->assertIsArray($try);
    }

}
