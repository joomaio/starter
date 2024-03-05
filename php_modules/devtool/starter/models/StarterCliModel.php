<?php
namespace App\devtool\starter\models;

use Doctrine\Common\Collections\Expr\Value;
use SPT\Container\Client as Base;
use ZipArchive;
use SPT\Support\Loader;

class StarterCliModel extends Base
{
    use \SPT\Traits\ErrorString;

    public function installFileZip($package_path)
    {
        $result = array(
            'success' => false,
            'message' => '',
        );

        echo "Start install package: \n";

        // unzip solution
        $package_folder = $this->StarterModel->unzipSolution($package_path);
        if (!$package_folder) 
        {
            echo "Can`t read file solution";
            return false;
        }

        $dir = new \DirectoryIterator($package_folder);
        foreach ($dir as $item)
        {
            if ($item->isDir() && !$item->isDot()) 
            {
                $folder_name = $item->getBasename();
            }
        }

        if (!$folder_name) 
        {
            echo "Can`t read file solution";
            return false;
        }
        
        $info_list = [];
        if(file_exists($package_folder . '/' . $folder_name . '/solution.php'))
        {
            $info_list = include $package_folder . '/' . $folder_name . '/solution.php';
        }
        elseif($package_folder . '/' . $folder_name . '/plugin.php')
        {
            $info_list = include $package_folder . '/' . $folder_name . '/plugin.php';
        }
        
        if(!$info_list)
        {
            $this->error = 'Failed package info not found!';
            $this->StarterModel->clearInstall($package_folder, '');
            return false;
        }

        foreach ($info_list as $idx => $info) 
        {
            $tmp = (array) $info;
            if (array_key_exists(0, $tmp)) 
            {
                $package_info[$idx] = $tmp[0];
            } 
            else 
            {
                $package_info[$idx] = '';
            }
        }

        echo "1. Unzip solution folder done!\n";

        // check package exist
        if ($package_info['type'] == 'solution') 
        {
            $try = $this->StarterModel->checkDependencies($package_info['dependencies'] ?? []);
            if(!$try)
            {
                $this->error = 'Install fail, must install : '. implode(', ', $package_info['dependencies']);
                $this->StarterModel->clearInstall($package_folder, '');
                return false;
            }
        }
        else 
        {
            $try = $this->StarterModel->checkDependencies($package_info['dependencies'] ?? []);
            if(!$try)
            {
                $this->error = 'Install fail, must install : '. implode(', ', $package_info['dependencies']);
                $this->StarterModel->clearInstall($package_folder, '');
                return false;
            }
        }

        // Install plugins
        echo "2. Start install plugin: \n";
        if ($package_info['type'] == 'plugin') 
        {
            $package_info['path'] = $package_folder . '/' . $folder_name;
            $try = $this->StarterModel->installPlugin($package_info['solution'], $package_info);
            if (!$try) 
            {
                $this->StarterModel->clearInstall($package_folder, $folder_name);
                echo "Install plugin " . basename($package_info['folder_name']) . " failed";
                return false;
            }

            $this->StarterModel->registerSolution($package_info['solution']);
            echo "Install plugin " . basename($package_info['folder_name']) . " successfully \n";
        } 
        else 
        {
            $plugins = $this->StarterModel->getPlugins($package_folder);

            foreach ($plugins as $item) 
            {
                $try = $this->StarterModel->installPlugin($package_info, $item);
                if (!$try) 
                {
                    $this->StarterModel->clearInstall($package_info['package_path'], $package_info);
                    echo "Install plugin " . $item['folder_name'] . " failed";
                    return false;
                }
                echo "Install plugin " . $item['folder_name'] . " successfully \n";
            }
            
            foreach (new \DirectoryIterator($package_folder) as $item) 
            {
                if (!$item->isDot() && $item->isDir()) {
                    $temp_folder = $item->getBasename();
                }
            }
            copy($package_folder . '/' . $temp_folder . '/solution.php', SPT_PLUGIN_PATH . $package_info['folder_name'] . '/solution.php');
        }

        if (is_dir(SPT_STORAGE_PATH . 'solutions')) 
        {
            $this->file->removeFolder(SPT_STORAGE_PATH . 'solutions');
        }

        if (file_exists(SPT_STORAGE_PATH . "solution.zip")) 
        {
            unlink(SPT_STORAGE_PATH . "solution.zip");
        }

        // generate database
        echo "3. Start generate data structure:\n";
        $entities = $this->DbToolModel->getEntities();
        foreach ($entities as $entity) 
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'successfully' : 'failed';
            echo str_pad($entity, 30) . $status . "\n";
        }
        echo "Generate data structure done\n";

        // update composer
        echo "4. Start composer update:\n";
        $try = $this->ComposerModel->update(true);
        if (!$try['success']) 
        {
            echo "Composer update failed!\n";
            return false;
        } else {
            echo "Composer update done!\n";
        }

        // clear file install
        $this->StarterModel->clearInstall($package_path, '');
        return false;
    }

    public function install($solution)
    {
        if (!$solution) {
            $this->error = 'Invalid Solution.';
            return false;
        }

        // todo install solution by solution-name

        // install solution by file zip
        if(file_exists($solution))
        {
            $try = $this->installFileZip($solution);
            return $try;
        }
        
        // install solution by link url
        $try = $this->installByUrl($solution);
        return $try;
    }

    public function installByUrl($solution_link)
    {
        if(!$solution_link)
        {
            $this->error = 'Invalid Solution.';
            return false;
        }

        echo "Start install " . $solution_link . ": \n";

        // Dowload solutions
        $solution_zip = $this->StarterModel->downloadSolution($solution_link);
        
        if (!$solution_zip) {
            $this->error = 'Download Solution Failed';
            return false;
        }

        echo "1. Download solution done!\n";

        // unzip solution
        $package_folder = $this->StarterModel->unzipSolution($solution_zip);
        if (!$package_folder) {
            $this->error = 'Can`t read file solution';
            return false;
        }

        echo "2. Unzip solution folder done!\n";

        // check info
        foreach (new \DirectoryIterator($package_folder) as $item) 
        {
            if (!$item->isDot() && $item->isDir()) {
                $temp_folder = $item->getBasename();
                break;
            }
        }

        $package_info = [];
        if(file_exists($package_folder . '/' . $temp_folder . '/solution.php'))
        {
            $package_info = include $package_folder . '/' . $temp_folder . '/solution.php';
        }
        elseif($package_folder . '/' . $temp_folder . '/plugin.php')
        {
            $package_info = include $package_folder . '/' . $temp_folder . '/plugin.php';
        }

        if(!$package_info)
        {
            $this->error = 'Failed package info not found!';
            $this->StarterModel->clearInstall($package_folder, '');
            return false;
        }
        
        // check dependencies
        $try = $this->StarterModel->checkDependencies($package_info['dependencies'] ?? []);
        if(!$try)
        {
            $this->error = 'Install fail, must install : '. implode(', ', $package_info['dependencies']);
            $this->StarterModel->clearInstall($package_folder, '');
            return false;
        }
        
        // Install plugins
        echo "3. Start install plugin: \n";
        if($package_info['type'] == 'solution')
        {
            $plugins = $this->StarterModel->getPlugins($package_folder, false, $package_info['folder_name'] ?? '');

            if(!file_exists(SPT_PLUGIN_PATH . $package_info['folder_name']))
            {
                if(!mkdir(SPT_PLUGIN_PATH . $package_info['folder_name']))
                {
                    $this->error = 'Can`t create folder solution';
                    $this->StarterModel->clearInstall($package_folder, '');
                    return false;
                }
            }
            
            copy($package_folder . '/' . $temp_folder . '/solution.php', SPT_PLUGIN_PATH . $package_info['folder_name'] . '/solution.php');
    
            foreach ($plugins as $item) 
            {
                $try = $this->StarterModel->installPlugin($package_info, $item);
                if (!$try) 
                {
                    $this->StarterModel->clearInstall($package_folder, $package_info);
                    $this->error = "- Install plugin " . $item['folder_name'] . " failed:\n";
                    return false;
                }
    
                echo "- Install plugin " . $item['folder_name'] . " done!\n";
            }
        }
        else
        {
            $package_info['path'] = $package_folder . '/' . $temp_folder;
            $try = $this->StarterModel->installPlugin($package_info['solution'], $package_info);
            if (!$try) 
            {
                $this->StarterModel->clearInstall($package_folder, $temp_folder);
                echo "Install plugin " . basename($package_info['folder_name']) . " failed";
                return false;
            }

            $this->StarterModel->registerSolution($package_info['solution']);
            echo "Install plugin " . basename($package_info['folder_name']) . " successfully \n";
        }
        

        echo "4. Start generate data structure:\n";

        // generate database
        $entities = $this->DbToolModel->getEntities();
        foreach ($entities as $entity) 
        {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'successfully' : 'failed';
            echo str_pad($entity, 30) . $status . "\n";
        }

        echo "Generate data structure done\n";

        // update composer
        echo "5. Start composer update:\n";
        $try = $this->ComposerModel->update(true);
        if (!$try['success']) 
        {
            echo "Composer update failed!\n";
            return false;
        } 
        else 
        {
            echo "Composer update done!\n";
        }

        // clear file install
        $this->StarterModel->clearInstall($package_folder, '');

        return true;
    }

    public function uninstall($package)
    {
        if (!$package) 
        {
            $this->error = 'Invalid Package.';
            return false;
        }

        $arr = explode('/', $package);
        $solution = $arr[0] ?? '';
        $plugin = $arr[1] ?? '';
        if(!$this->StarterModel->checkValidPackage($solution, $plugin))
        {
            $this->error = 'Invalid Package.';
            return false;
        }

        if(!$this->StarterModel->checkUninstall($package))
        {
            $this->error = 'Uninstall Failed, '. $this->StarterModel->getError() .' is depending on '.$package;
            return false;
        }

        echo "Start uninstall " . $package . "\n";
        if(!$plugin)
        {
            echo "1. Uninstall plugins: \n";
            $plugins = $this->StarterModel->getPlugins(SPT_PLUGIN_PATH . $solution, true);
            foreach ($plugins as $plugin) 
            {
                $try = $this->StarterModel->uninstallPlugin($plugin['path'], $solution);
                if (!$try) 
                {
                    echo "- Uninstall plugin " . $plugin['folder_name'] . " failed:\n";
                    return false;
                }
    
                echo "- Uninstall plugin " . $plugin['folder_name'] . " done!\n";
            }
        }
        else
        {
            $try = $this->StarterModel->uninstallPlugin(SPT_PLUGIN_PATH . $package, $solution);
        }

        
        $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $package);

        echo "2. Start composer update:\n";

        $try = $this->ComposerModel->update(true);

        if (!$try['success']) 
        {
            echo "Composer update failed!\n";
            return false;
        } 
        else 
        {
            echo "Composer update successfully!\n";
            return true;
        }
    }
}
