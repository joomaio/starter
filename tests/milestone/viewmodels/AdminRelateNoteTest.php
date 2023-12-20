<?php
namespace Tests\milestone\viewmodels;

use App\plugins\milestone\viewmodels\AdminRelateNote;
use Tests\Test as TestCase;

class AdminRelateNoteTest extends TestCase
{
    private $AdminRelateNote;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['request_id' => 1]);

        $this->AdminRelateNote = new AdminRelateNote($container);
    }

    public function testForm()
    {
        $try = $this->AdminRelateNote->form();
        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminRelateNote->getFormFields();
        $this->assertIsArray($try);
    }
}
