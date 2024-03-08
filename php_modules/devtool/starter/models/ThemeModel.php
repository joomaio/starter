<?php
namespace App\devtool\starter\models;

use Doctrine\Common\Collections\Expr\Value;
use SPT\Container\Client as Base;
use ZipArchive;
use SPT\Support\Loader;

class ThemeModel extends Base
{
    use \SPT\Traits\ErrorString;

    private $themes;

    public function getThemes()
    {
        // Todo get storage solutions by link api
        if (!$this->themes) {
            $this->themes = [];
        }

        $theme_path = $this->app->get('themePath', '');
        if(!$theme_path)
        {
            return [];
        }

        foreach (new \DirectoryIterator($theme_path) as $item) 
        {
            if (!$item->isDot() && $item->isDir()) 
            {
                // case not installed yet plugins
                $info_path = $item->getPathname() . '/theme.json';
                if (file_exists($info_path)) 
                {
                    $info = file_get_contents($info_path);
                    $this->themes[$item->getBasename()] = $info;
                }
            }
        }

        return $this->themes;
    }

    public function install($data, $step = 0)
    {
        // check file install
        if($data['file'] && $data['file'][''])
    }
}
