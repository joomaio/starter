<?php
namespace Tests\note2\viewmodels;

use App\plugins\note2\viewmodels\AdminNoteWidget;
use Tests\Test as TestCase;

class AdminNoteWidgetTest extends TestCase
{
    private $AdminNoteWidget;

    protected function setUp(): void
    {
        $app = $this->prepareApp();
        $container = $app->getContainer();
        $request = $container->get('request');
        $request->set('urlVars', ['id' => 1]);

        $this->AdminNoteWidget = new AdminNoteWidget($container);
    }
    
    public function testPopup_new()
    {
        $try = $this->AdminNoteWidget->popup_new();
        $this->assertIsArray($try);
    }
}
