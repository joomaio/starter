<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminMilestone;
use Tests\Test as TestCase;

class AdminMilestoneTest extends TestCase
{
    private $AdminMilestone;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();

        $this->AdminMilestone = new AdminMilestone($container);
    }

    public function testForm()
    {
        $try = $this->AdminMilestone->form();
        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminMilestone->getFormFields();
        $this->assertIsArray($try);
    }
}
