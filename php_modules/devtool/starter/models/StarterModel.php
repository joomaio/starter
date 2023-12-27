<?php
namespace App\devtool\starter\models;

use SPT\Container\Client as Base;
use ZipArchive;
use SPT\Support\Loader;

class StarterModel extends Base
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

        $solutions = [];
        if ($this->solutions)
        {
            foreach($this->solutions as $solution)
            {
                $tmp = (array) $solution;
                $tmp['status'] = false;
                if(file_exists(SPT_PLUGIN_PATH.$tmp['code']))
                {
                    $tmp['status'] = true;
                }

                $solutions[] = $tmp;
            }
        }

        return $solutions;
    }

    public function install($solution, $required = false)
    {
        $solutions = $this->getSolutions();
        $is_cli = $this->isCli();
        
        if (!$solution)
        {
            $this->error = 'Invalid Solution.';
            return false;
        }

        $config = null;
        foreach($solutions as $item)
        {
            if ($item['name'] == $solution)
            {
                $config = $item;
            }
        }

        if (!$config)
        {
            $this->error = 'Invalid Solution.';
            return false;
        }

        if(isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH.$config['required']))
        {
            if ($is_cli)
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
            else
            {
                $this->install($config['required'], true);
            }
        }

        if (file_exists(SPT_PLUGIN_PATH.$config['name']))
        {
            if ($is_cli)
            {
                $check = readline($config['name']. ' already exists, Do you still want to install it (Y/n)? ');
                if (strtolower($check) == 'n' || strtolower($check) == 'no')
                {
                    $this->error = "Stop Install";
                    return false;
                }
            }
            else
            {
                $this->error = "Solution". $config['name']. " already exists!";
                return false;
            }
        }
        
        if ($is_cli)
        {
            echo "Start install ". $config['name']. ": \n";
        }

        // Download zip solution
        if (!$config['link'])
        {
            $this->error = 'Invalid Solution link.';
            return false;
        }

        $solution_zip = $this->downloadSolution($config['link']);
        if (!$solution_zip)
        {
            $this->error = 'Download Solution Failed';
            return false;
        }

        echo $is_cli ? "1. Download solution done!\n" : '';

        // unzip solution
        $solution_folder = $this->unzipSolution($solution_zip);
        if (!$solution_folder)
        {
            $this->error = 'Can`t read file solution';
            return false;
        }

        echo $is_cli ? "2. Unzip solution folder done!\n" : '';
        
        // Install plugins
        $plugins = $this->getPlugins($solution_folder);
        echo $is_cli ? "3. Start install plugin: \n" : '';
        foreach($plugins as $item)
        {
            $try = $this->installPlugin($config, $item);
            if (!$try)
            {
                $this->clearInstall($solution_folder, $config);
                $this->error = "- Install plugin ". basename($item)." failed:\n";
                return false;
            }
            echo $is_cli ? "- Install plugin ". basename($item)." done!\n" : '';
        }

        echo $is_cli ? "4. Start generate data structure:\n" : '';
        // generate database
        $entities = $this->DbToolModel->getEntities();
        foreach($entities as $entity)
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'success' : 'failed';
            echo $is_cli ? str_pad($entity, 30) . $status ."\n" : '';
        }
        echo $is_cli ? "Generate data structure done\n" : '';

        // update composer
        if(!$required)
        {
            echo $is_cli ? "5. Start composer update:\n" : '';
            if($is_cli)
            {
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
            $this->file->removeFolder(SPT_STORAGE_PATH. 'solutions');
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
        if (file_exists($new_plugin))
        {
            $this->file->removeFolder($new_plugin);
        }
        
        $try = $this->file->copyFolder($plugin, $new_plugin);

        if (!$try)
        {
            $this->error = "Error: Can't create folder solution";
            return false;
        }
    
        // run installer
        $class = $this->app->getNameSpace(). '\\'. $solution['name'].'\\'. basename($plugin) .'\\registers\\Installer';
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
                        $try = $this->file->copyFolder($new_plugin. '/'. $asset, PUBLIC_PATH.'assets/'. $key);
                    }
                }
            }
        }

        return true;
    }

    public function clearInstall($solution, $config = [])
    {
        if(file_exists($solution))
        {
            $try = $this->file->removeFolder($solution);
        }

        if($config && file_exists(SPT_PLUGIN_PATH. $config['name']))
        {
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH. $config['name']);
        }

        if(file_exists(SPT_STORAGE_PATH. "solution.zip"))
        {
            $try = unlink(SPT_STORAGE_PATH. "solution.zip");
        }

        return true;
    }

    public function updateComposer()
    {
        // update composer
        exec("composer update", $output, $return_var);

        return true;
    }
     
    public function uninstall($solution)
    {
        $solutions = $this->getSolutions();
        
        if (!$solution)
        {
            $this->error = 'Invalid Solution.';
            return false;
        }

        $config = null;
        foreach($solutions as $item)
        {
            if ($item['name'] == $solution)
            {
                $config = (array) $item;
            }
        }

        if (!$config)
        {
            $this->error = 'Invalid Solution.';
            return false;
        }

        if(!file_exists(SPT_PLUGIN_PATH.$solution))
        {
            $this->error = "Uninstall Failed. Cannot find installed solution ". $solution;
            return false;
        }
        
        // start uninstall
        echo "Start uninstall solution ". $solution ."\n";
        echo "1. Uninstall plugins: \n";
        $plugins = $this->getPlugins(SPT_PLUGIN_PATH.$solution, true);
        foreach ($plugins as $plugin)
        {
            $try = $this->uninstallPlugin($plugin, $solution);
            if (!$try)
            {
                echo "- Uninstall plugin ". basename($plugin)." failed:\n";
                return false;
            }
            echo "- Uninstall plugin ". basename($plugin)." done!\n";
        }
        $try = $this->file->removeFolder(SPT_PLUGIN_PATH.$solution);

        echo "2. Start composer update:\n";
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

        return true;
    }

    public function uninstallPlugin($plugin, $solution)
    {
        // check folder plugin
        if (!file_exists($plugin))
        {
            $this->error = 'Plugin Invalid';
            return false;
        }
    
        // run uninstall
        $class = $this->app->getNameSpace(). '\\'. $solution.'\\'. basename($plugin) .'\\registers\\Installer';
        if(method_exists($class, 'uninstall'))
        {
            $class::uninstall($this->app);
        }

        // update asset file
        if(method_exists($class, 'assets'))
        {
            $assets = $class::assets();
            if ($assets && is_array($assets))
            {
                foreach($assets as $key => $asset)
                {
                    if (file_exists(PUBLIC_PATH.'assets/'. $key))
                    {
                        $this->file->removeFolder(PUBLIC_PATH.'assets/'. $key);
                    }
                }
            }
        }

        $try = $this->file->removeFolder($plugin);
        return true;
    }

    public function isCli()
    {
        $check = php_sapi_name();
        return $check === 'cli';
    }
}
