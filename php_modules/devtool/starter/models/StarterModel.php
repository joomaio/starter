<?php
namespace App\devtool\starter\models;

use Doctrine\Common\Collections\Expr\Value;
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
        $result = array(
            'success' => false,
            'message' => '',
        );
        
        if (!$solution)
        {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        $config = null;
        foreach($solutions as $item)
        {
            if ($item['code'] == $solution)
            {
                $config = $item;
            }
        }

        if (!$config)
        {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        if(isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH.$config['required']))
        {
            if ($is_cli)
            {
                $check = readline("Solution ". $config['code']. " required install ". $config['required'].". Do you want continue install solution ". $config['required'] ."(Y/n)? ");
                if (strtolower($check) =='n' || strtolower($check) == 'no')
                {
                    $this->error = "<h4>Install Failed. Solution ". $config['code']. " required install ". $config['required'] . '</h4>' ;
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

        if (file_exists(SPT_PLUGIN_PATH.$config['code']))
        {
            if ($is_cli)
            {
                $check = readline($config['code']. ' already exists, Do you still want to install it (Y/n)? ');
                if (strtolower($check) == 'n' || strtolower($check) == 'no')
                {
                    $this->error = "Stop Install";
                    return false;
                }
            }
            else
            {
                $this->error = "Solution". $config['code']. " already exists!";
                $result['message'] = "<h4>Solution". $config['code']. " already exists!</h4>";
                return $result;
            }
        }
        
        if ($is_cli)
        {
            echo "Start install ". $config['code']. ": \n";
        }

        // Download zip solution
        if (!$config['link'])
        {
            $this->error = 'Invalid Solution link.';
            $result['message'] .= '<p>Invalid Solution link.</p>';
            return $result;
        }

        $solution_zip = $this->downloadSolution($config['link']);
        if (!$solution_zip)
        {
            $this->error = 'Download Solution Failed';
            $result['message'] .= '<p>Download Solution Failed.</p>';
            return $result;
        }

        if ($is_cli) 
        {
            echo "1. Download solution done!\n";
        } else {
            $result['message'] .= '<h4>Download solution</h4>';
        }

        // unzip solution
        $solution_folder = $this->unzipSolution($solution_zip);
        if (!$solution_folder)
        {
            $this->error = 'Can`t read file solution';
            $result['message'] .= '<p>Can`t read file solution</p>';
            return $result;
        }

        if ($is_cli) 
        {
            echo "2. Unzip solution folder done!\n";
        } else {
            $result['message'] .= "<h4>Unzip solution folder</h4>";
        }
        
        // Install plugins
        $plugins = $this->getPlugins($solution_folder);
        if ($is_cli) 
        {
            echo "3. Start install plugin: \n";
        } else {
            $result['message'] .= "<h4>Start install plugin: </h4>";
        }

        foreach($plugins as $item)
        {
            $try = $this->installPlugin($config, $item);
            if (!$try)
            {
                $this->clearInstall($solution_folder, $config);
                $this->error = "- Install plugin ". basename($item)." failed:\n";
                $result['message'] .= "<p>- Install plugin ". basename($item)." failed:</p>";
                return $result;
            }
            if ($is_cli) 
            {
                echo "- Install plugin ". basename($item)." done!\n";
            } else {
                $result['message'] .= "<p>- Install plugin ". basename($item)." successfully!</p>";
            }
        }

        if ($is_cli) 
        {
            echo "4. Start generate data structure:\n";
        } else {
            $result['message'] .= "<h4>Start generate data structure:</h4>";
        }

        // generate database
        $entities = $this->DbToolModel->getEntities();
        foreach($entities as $entity)
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'successfully' : 'failed';
            if ($is_cli) 
            {
                echo str_pad($entity, 30) . $status ."\n";
            } else {
                $result['message'] .= '<p>' . str_pad($entity, 30) . $status ."</p>";
            }
        }
        if ($is_cli) 
        {
            echo "Generate data structure done\n";
        } else {
            $result['message'] .= "<p>Generate data structure successfully.</p>";
        }

        // update composer
        if(!$required)
        {
            if ($is_cli) 
            {
                echo "5. Start composer update:\n";
            } else {
                $result['message'] .= "<h4>Run composer update:</h4>";
            }
            $try = $this->ComposerModel->update($is_cli);
            if(!$try['success'])
            {
                if ($is_cli) 
                {
                    echo "Composer update failed!\n";
                } else {
                    $result['message'] .= "<p>Composer update failed!</p>";
                }
                return $result;
            }
            else
            {
                if ($is_cli) 
                {
                    echo "Composer update done!\n";
                } else {
                    $result["message"] .= $try['message'];
                    $result['message'] .= "<p>Composer update succesfully!</p>";
                }
            }
        }

        // clear file install
        $this->clearInstall($solution_folder);

        $result['success'] = true;
        $result['message'] .= "<h4>Install successfully!</h4>";
        return $result;
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
        if(!file_exists(SPT_PLUGIN_PATH. $solution['code']))
        {
            if(!mkdir(SPT_PLUGIN_PATH. $solution['code']))
            {
                $this->error = "Error: Can't Create folder solution";
                return false;
            }
        }

        // copy folder
        $new_plugin = SPT_PLUGIN_PATH. $solution['code'].'/'. basename($plugin);
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
        $class = $this->app->getNameSpace(). '\\'. $solution['code'].'\\'. basename($plugin) .'\\registers\\Installer';
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

        if($config && file_exists(SPT_PLUGIN_PATH. $config['code']))
        {
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH. $config['code']);
        }

        if(file_exists(SPT_STORAGE_PATH. "solution.zip"))
        {
            $try = unlink(SPT_STORAGE_PATH. "solution.zip");
        }

        return true;
    }

    public function uninstall($solution)
    {
        $solutions = $this->getSolutions();
        $is_cli = $this->isCli();
        $result = array(
            'success' => false,
            'message' => '',
        );
        
        if (!$solution)
        {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        $config = null;
        foreach($solutions as $item)
        {
            if ($item['code'] == $solution)
            {
                $config = (array) $item;
            }
        }

        if (!$config)
        {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        if(!file_exists(SPT_PLUGIN_PATH.$solution))
        {
            $this->error = 'Uninstall Failed. Cannot find installed solution '. $solution;
            $result['message'] = '<h4>Uninstall Failed. Cannot find installed solution '. $solution . '.</h4>';
            return $result;
        }
        
        // start uninstall
        if ($is_cli) 
        {
            echo "Start uninstall solution ". $solution ."\n";
            echo "1. Uninstall plugins: \n";
        } else {
            $result['message'] .= "<h4>Uninstall plugins: </h4>";
        }

        $plugins = $this->getPlugins(SPT_PLUGIN_PATH.$solution, true);
        foreach ($plugins as $plugin)
        {
            $try = $this->uninstallPlugin($plugin, $solution);
            if (!$try)
            {
                if ($is_cli) {
                    echo "- Uninstall plugin ". basename($plugin)." failed:\n";
                    return false;
                } else {
                    $result['message'] .= "<p>- Uninstall plugin ". basename($plugin)." failed:</p>";
                    return $result;
                }
            }

            if ($is_cli) {
                echo "- Uninstall plugin ". basename($plugin)." done!\n";
            } else {
                $result['message'] .= "<p>- Uninstall plugin ". basename($plugin)." successfully!</p>";
            }
        }
        $try = $this->file->removeFolder(SPT_PLUGIN_PATH.$solution);

        if ($is_cli) {
            echo "2. Start composer update:\n";
        } else {
            $result['message'] .= "<h4>Run composer update:</h4>";
        }

        $try = $this->ComposerModel->update($is_cli);

        echo gettype( $try );
        if(!$try['success'])
        {
            if ($is_cli) {
                echo "Composer update failed!\n";
                return false;
            } else {
                $result['message'] .= "<p>Composer update failed!</p>";
                return $result;
            }
        }
        else
        {
            if ($is_cli) {
                echo "Composer update successfully!\n";
                return true;
            } else {
                $result["message"] .= $try['message'];
                $result['message'] .= "<p>Composer update done!</p>";
                $result['message'] .= "<h4>Uninstall successfully!</h4>";
                $result['success'] = true;
                return $result;
            }
        }
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
