<?php
namespace App\plugins\devtool\start\models;

use SPT\Container\Client as Base;
use ZipArchive;
use SPT\Support\Loader;

class StartModel extends Base
{ 
    use \SPT\Traits\ErrorString;

    private $solutions;

    public function getSolutions()
    {
        // get file xml
        if(!$this->solutions)
        {
            $this->solutions = simplexml_load_file(ROOT_PATH .'solution.xml');
        }

        return $this->solutions;
    }

    public function install($solution, $required = false)
    {
        $solutions = $this->getSolutions();
        
        if (!$solution)
        {
            $this->error = 'Invalid Solution. Get solution name by command: php cli.php solution-list';
            return false;
        }

        $config = null;
        foreach($solutions as $item)
        {
            if ($item->name == $solution)
            {
                $config = (array) $item;
            }
        }

        if (!$config)
        {
            $this->error = 'Invalid Solution. Get solution name by command: php cli.php solution-list';
            return false;
        }

        if(isset($config['required']) && $config['required'])
        {
            $check = readline("Solution ". $config['name']. " required install ". $config['required'].". Do you want continue install solution ". $config['required'] ."(Y/n)? ");
            if (strtolower($check) =='n' || strtolower($check) == 'no')
            {
                $this->error = "Install Failed. Solution ". $config['name']. " required install ". $config['required'] ;
                return false;
            }
            else
            {
                $this->install($config['required'], true);
            }
        }

        echo "Start install ". $config['name']. ": \n";
        // Download zip solution
        if (!$config['link'])
        {
            $this->error = 'Invalid Solution link.';
            return false;
        }

        $solution_zip = $this->downloadSolution($config['link']);
        if (!$solution_zip)
        {
            return false;
        }
        echo "1. Download solution done!\n";

        // unzip solution
        $solution_folder = $this->unzipSolution($solution_zip);
        if (!$solution_folder)
        {
            return false;
        }

        echo "2. Unzip solution folder done!\n";
        
        // Install plugins
        $plugins = $this->getPlugins($solution_folder);
        echo "3. Start install plugin: \n";
        foreach($plugins as $item)
        {
            $try = $this->installPlugin($config, $item);
            if (!$try)
            {
                $this->clearInstall($solution_folder, $config);
                echo "- Install plugin ". basename($item)." failed:\n";
                return false;
            }
            echo "- Install plugin ". basename($item)." done!\n";
        }

        // update composer
        if(!$required)
        {
            echo "4. Start composer update:\n";
            $try = $this->updateComposer();
            if(!$try)
            {
                echo "Composer update failed!\n";
                return false;
            }
            else
            {
                echo "Composer update done!\n";
            }
        }
        
        // clear file install
        $this->clearInstall($solution_folder);

        return true;
    }

    public function downloadSolution($link)
    {
        if(!$link)
        {
            return false;
        }

        if (!file_exists(SPT_STORAGE_PATH))
        {
            if(!mkdir(SPT_STORAGE_PATH))
            {
                return false;
            }
        }

        $output_filename = SPT_STORAGE_PATH. "solution.zip";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        $result = curl_exec($ch);
        curl_close($ch);

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status_code != 200) {
            $this->error = "Download solution failed, error http: $status_code";
            return false;
        }
        
        $fp = fopen($output_filename, 'w');
        fwrite($fp, $result);
        fclose($fp);

        return SPT_STORAGE_PATH. "solution.zip";
    }

    public function unzipSolution($path)
    {
        if (!$path || !file_exists($path))
        {
            $this->error = 'Invalid file zip solution';
            return false;
        }

        // remove folder solution tmp
        if (file_exists(SPT_STORAGE_PATH. 'solutions'))
        {
            $this->removeDir(SPT_STORAGE_PATH. 'solutions');
        }

        // unzip
        $zip = new ZipArchive;
        $res = $zip->open($path);
        if ($res === TRUE) 
        {
            $zip->extractTo(SPT_STORAGE_PATH. 'solutions');
            $zip->close();
            return SPT_STORAGE_PATH. 'solutions';
        } 

        return false;
    }

    public function removeDir($dir)
    {
        if (!file_exists($dir))
        {
            return true;
        } 
    
        if (!is_dir($dir) || is_link($dir))
        {
            return unlink($dir);
        }
    
        foreach (scandir($dir) as $item) 
        {
            if ($item == '.' || $item == '..')
            {
                continue;
            } 

            if (!$this->removeDir($dir . "/" . $item)) 
            {
                if (!$this->removeDir($dir . "/" . $item))
                {
                    return false;
                }
            };

        }
    
        return rmdir($dir);
    }

    public function getPlugins($path, $folder_solution = false)
    {
        $packages = [];
        foreach(new \DirectoryIterator($path) as $item) 
        {
            if (!$item->isDot() && $item->isDir())
            {
                if(!$folder_solution)
                {
                    return $this->getPlugins($item->getPathname(), true);
                }

                $packages[$item->getBasename()] = $item->getPathname(); 
            }
        }

        return $packages;
    }

    public function installPlugin($solution, $plugin)
    {
        if(!file_exists(SPT_PLUGIN_PATH. $solution['name']))
        {
            if(!mkdir(SPT_PLUGIN_PATH. $solution['name']))
            {
                $this->error = "Error: Can't Create folder solution";
                return false;
            }
        }

        // copy folder
        $new_plugin = SPT_PLUGIN_PATH. $solution['name'].'/'. basename($plugin);
        $try = $this->recursiveCopy($plugin, $new_plugin);

        if (!$try)
        {
            $this->error = "Error: Can't create folder solution";
            return false;
        }
    
        // run installer
        $class = $this->app->getNameSpace(). '\\plugins\\'. $solution['name'].'\\'. basename($plugin) .'\\registers\\Installer';
        if(method_exists($class, 'install'))
        {
            $class::install($this->app);
        }

        // update asset file
        if(method_exists($class, 'assets'))
        {
            $assets = $class::assets();
            if ($assets && is_array($assets))
            {
                foreach($assets as $key => $asset)
                {
                    if(file_exists($new_plugin. '/'. $asset))
                    {
                        $try = $this->recursiveCopy($new_plugin. '/'. $asset, PUBLIC_PATH.'assets/'. $key);
                    }
                }
            }
        }

        return true;
    }

    public function recursiveCopy($source, $dest) 
    {
        $files = scandir($source);
        foreach ($files as $file) 
        {
            if ($file == "." || $file == "..") 
            {
                continue;
            }

            $sourcePath = $source . "/" . $file;
            $destPath = $dest . "/" . $file;
            if (is_dir($sourcePath)) 
            {
                if(!file_exists($destPath))
                {
                    mkdir($destPath, 0755, true);
                }
                $this->recursiveCopy($sourcePath, $destPath);
            } 
            else 
            {
                copy($sourcePath, $destPath);
            }
        }

        return true;
    }

    public function clearInstall($solution, $config = [])
    {
        if(file_exists($solution))
        {
            $try = $this->removeDir($solution);
        }

        if($config && file_exists(SPT_PLUGIN_PATH. $config['name']))
        {
            $try = $this->removeDir(SPT_PLUGIN_PATH. $config['name']);
        }

        if(file_exists(SPT_STORAGE_PATH. "solution.zip"))
        {
            $try = $this->removeDir(SPT_STORAGE_PATH. "solution.zip");
        }

        return true;
    }

    public function updateComposer()
    {
        // update composer
        exec("composer update", $output, $return_var);

        return true;
    }
     
    public function uninstall()
    {
        // Todo
        
        return true;
    }
}
