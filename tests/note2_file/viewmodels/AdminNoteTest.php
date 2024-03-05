<?php

/**
 * SPT software - ViewModel
 * 
 * @project: https://github.com/smpleader/spt
 * @author: Pham Minh - smpleader
 * @description: A simple View Model
 * 
 */

 namespace Tests\note2_file\viewmodels;

use App\plugins\note2_file\viewmodels\AdminNote;
 use Tests\Test as TestCase;

class AdminNoteTest extends TestCase
{
    private $AdminNote;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['id' => 1]);

        $this->AdminNote = new AdminNote($container);
    }

    public function testForm()
    {
        $try = $this->AdminNote->form();

        $this->assertIsArray($try);
    }

    public function testGetFormFields()
    {
        $try = $this->AdminNote->getFormFields();

        $this->assertIsArray($try);
    }
}
