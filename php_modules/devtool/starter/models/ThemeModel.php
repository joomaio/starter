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
                    $info = json_decode($info, true);
                    $this->themes[$item->getBasename()] = $info;
                }
            }
        }

        return $this->themes;
    }

    public function install($data, $step = 0, $timestamp = 0)
    {
        // check file install
        if(!$step)
        {
            $try = false;
            if($data['file'] && $data['file']['tmp_name'])
            {
                // check valid file upload
                $try = $this->checkValidFile($data['file'], true);
            }
            else
            {
                // check valid url file
                $try = $this->checkValidFile($data['url']);
            }

            $timestamp = strtotime('now');
            $this->session->set('installerTheme', $timestamp);
            return [
                'status' => $try,
                'timestamp' => $timestamp,
                'totalStep' => 3,
                'step' => 1,
            ];
        }

        if ($step)
        {
            // check timestamp
            $tmp = $this->session->get('installerTheme', '');
            if($tmp != $timestamp)
            {
                return false;
            }
        }

        switch ($step) {
            case 2:
                // Unzip file
                $try = $this->unZipTheme('theme.zip');
                if(!$try)
                {
                    return false;
                }
                break;
            case 3:
                // Unzip file
                $try = $this->installTheme('theme');
                if(!$try)
                {
                    return false;
                }
                break;
            default:
                break;
        }

        return true;
    }

    public function checkValidFile($file, $upload = false)
    {
        if(!$file)
        {
            return false;
        }

        if ($upload)
        {
            if(!$file['tmp_name'])
            {
                return false;
            }

            $tmp = explode('.', $file['name']);
            $file_ext = strtolower(end($tmp));
            $expensions = array('zip');
            if (in_array($file_ext, $expensions) === false) 
            {
                $this->error = 'Only .zip files are allowed.';
                return false;
            }

            move_uploaded_file($file, SPT_STORAGE_PATH . "theme.zip");
            return "theme.zip";
        }

        $try = $this->downloadByUrl($file, 'theme.zip');
        if(!$try)
        {
            return false;
        } 

        return "theme.zip";
    }

    public function downloadByUrl($link, $file = '')
    {
        if(!$link)
        {
            return false;
        }

        if (!file_exists(SPT_STORAGE_PATH)) 
        {
            $try = mkdir(SPT_STORAGE_PATH);
            if (!$try) 
            {
                return false;
            }
        }

        $output_filename = SPT_STORAGE_PATH . $file;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        $result = curl_exec($ch);
        curl_close($ch);

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status_code != 200) 
        {
            $this->error = "Download solution failed, error http: $status_code";
            return false;
        }

        $fp = fopen($output_filename, 'w');
        fwrite($fp, $result);
        fclose($fp);

        return SPT_STORAGE_PATH . $file;
    }

    public function unZipTheme($file)
    {
        $path = SPT_STORAGE_PATH. $file;
        if(!file_exists($path))
        {
            $this->error = 'Invalid file theme';
            return false;
        }

        if (file_exists(SPT_STORAGE_PATH . 'theme')) 
        {
            $this->file->removeFolder(SPT_STORAGE_PATH . 'theme');
        }

        $zip = new ZipArchive;
        $res = $zip->open($path);
        if ($res === TRUE) 
        {
            $zip->extractTo(SPT_STORAGE_PATH . 'theme');
            $zip->close();
            return SPT_STORAGE_PATH . 'theme';
        }

        return false;
    }

    public function installTheme($folder)
    {
        $path = SPT_STORAGE_PATH. $folder;
        if(!$folder || !file_exists($path))
        {
            $this->error = 'Invalid folder theme';
            return false;
        }

        // load install theme info
        $try = $this->getInstallTheme($path);
        if(!$try)
        {
            $this->error = "Invalid theme config";
            return false;
        }

        // download theme
        $path = $try['folder'];
        $infor = $try['infor'];

        $try = $this->StarterModel->checkDependencies($infor['dependencies'] ?? []);
        if(!$try)
        {
            $this->error = "Can't install theme! Need install: ". implode(', ', $infor['dependencies']);
            return false;
        }

        // copy folder theme
        $theme_path = $this->app->get('themePath', '');
        if(!$infor['folder'])
        {
            $this->error = "Can't read folder theme";
            return false;
        }
        $new_theme = $theme_path. '/' . $infor['folder'];
        if (file_exists($new_theme)) 
        {
            $this->file->removeFolder($new_theme);
        }

        // Check dependencies
        $try = $this->file->copyFolder($path, $new_theme);
        if(!$try)
        {
            $this->error = "Can't copy folder theme!";
            return false;
        }

        // install assets file
        $assets = $infor['assets'] ?? 'assets';
        $public_assets = PUBLIC_PATH. $infor['folder'];
        if(file_exists($new_theme.'/'. $assets))
        {
            if (file_exists($public_assets)) 
            {
                $this->file->removeFolder($public_assets);
            }
    
            $try = $this->file->copyFolder($new_theme.'/'. $assets, $public_assets);
            if($try)
            {
                $this->file->removeFolder($new_theme.'/'. $assets);
            }
        }

        $this->clearInstall();
        return true;
    }

    public function getInstallTheme($folder)
    {
        if(!$folder || !file_exists($folder))
        {
            $this->error = 'Invalid folder theme';
            return false;
        }

        $infor = false;
        if (!file_exists($folder.'/theme.json'))
        {
            foreach (new \DirectoryIterator($folder) as $item) 
            {
                if (!$item->isDot() && $item->isDir()) 
                {
                    $try = $this->getInstallTheme($item->getPathname()); 
                    if($try)
                    {
                        return $try;
                    }
                }
            }

            return false;
        }

        $infor = file_get_contents($folder.'/theme.json');
        $infor = json_decode($infor, true);

        return [
            'infor' => $infor,
            'folder' => $folder
        ];
    }

    public function clearInstall()
    {
        // remove storage
        if(file_exists(SPT_STORAGE_PATH.'theme'))
        {
            $this->file->removeFolder(SPT_STORAGE_PATH.'theme');
        }

        if(file_exists(SPT_STORAGE_PATH.'theme.zip'))
        {
            unlink(SPT_STORAGE_PATH.'theme.zip');
        }

        return true;
    }

    public function uninstall($theme, $step = 0, $timestamp = 0)
    {
        $themes = $this->getThemes();
        if (!isset($themes[$theme]))
        {
            $this->error = 'Invalid theme';
            return false;
        }

        if(!$step)
        {
            // check dependencies
            $try = $this->StarterModel->checkUninstall('themes/'. $theme);
            if(!$try)
            {
                $this->error = 'Uninstall failed, package existence depends on the theme.';
                return false;
            }

            $admin_theme = $this->OptionModel->get('admin_theme', '');
            $default_theme = $this->OptionModel->get('default_theme', '');
            if($admin_theme == $theme || $default_theme == $theme)
            {
                $this->error = 'Uninstall failed, theme is in use.';
                return false;
            }

            $timestamp = strtotime('now');
            $this->session->set('uninstallerTheme', $timestamp);

            return [
                'status' => $try,
                'timestamp' => $timestamp,
                'totalStep' => 2,
                'step' => 1,
            ];
        }

        if ($step)
        {
            // check timestamp
            $tmp = $this->session->get('uninstallerTheme', '');
            if($tmp != $timestamp)
            {
                return false;
            }
        }

        $try = $this->uninstallTheme($theme);
        if(!$try)
        {
            return false;
        }

        return true;
    }

    public function uninstallTheme($theme)
    {
        $themes = $this->getThemes();
        if (!isset($themes[$theme]))
        {
            $this->error = 'Invalid theme';
            return false;
        }

        $theme_path = $this->app->get('themePath', '');
        // uninstall theme folder
        $try = $this->file->removeFolder($theme_path.'/'. $theme);
        if(!$try)
        {
            $this->error = "Can't remove theme";
            return false;
        }
        else
        {
            // remove file assets
            $try = $this->file->removeFolder(PUBLIC_PATH.'/'. $theme);
        }

        return true;
    }
}
