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
        $solutions = $this->getSolutions();
        $result = array(
            'success' => false,
            'message' => '',
        );

        echo "Start install package: \n";

        // unzip solution
        $package_folder = $this->unzipSolution($package_path);
        if (!$package_folder) {
            echo "Can`t read file solution";
            return $result;
        }

        $dir = new \DirectoryIterator($package_folder);
        foreach ($dir as $item) {
            if ($item->isDir() && !$item->isDot()) {
                $folder_name = $item->getBasename();
            }
        }

        if (!$folder_name) {
            echo "Can`t read file solution";
            return $result;
        }
        $info_list = include $package_folder . '/' . $folder_name . '/solution.php';

        foreach ($info_list as $idx => $info) {
            $tmp = (array) $info;
            if (array_key_exists(0, $tmp)) {
                $package_info[$idx] = $tmp[0];
            } else {
                $package_info[$idx] = '';
            }
        }

        echo "1. Unzip solution folder done!\n";

        // check package exist
        if ($package_info['type'] == 'solution') 
        {
            $config = null;
            foreach ($solutions as $item) 
            {
                if ($item['code'] == $package_info['solution']) {
                    $config = $item;
                }
            }

            if (!$config) {
                echo 'Invalid Solution';
                return $result;
            }

            if (isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH . $config['required'])) {
                $check = readline("Solution " . $config['code'] . " required install " . $config['required'] . ". Do you want continue install solution " . $config['required'] . "(Y/n)? ");
                if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                    echo "Install Failed. Solution " . $config['code'] . " required install " . $config['required'];
                    return $result;
                } else {
                    $this->install($config['required'], true);
                }
            }

            if (file_exists(SPT_PLUGIN_PATH . $config['code'] . '/solution.php')) {
                $check = readline($config['code'] . ' already exists, Do you still want to install it (Y/n)? ');
                if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                    echo "Stop Install";
                    return false;
                }
            }
        } else {
            if (file_exists(SPT_PLUGIN_PATH . $package_info['solution'] . '/' . $package_info['folder_name'])) {
                $check = readline($package_info['folder_name'] . ' already exists, Do you still want to install it (Y/n)? ');
                if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                    echo "Stop Install";
                    return $result;
                }
            } else {
                if ($package_info['required']) {
                    foreach ($package_info['required'] as $item) {
                        $this->install($item, true);
                    }
                }
            }
        }

        if (isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH . $config['required'])) {

            $check = readline("Solution " . $config['code'] . " required install " . $config['required'] . ". Do you want continue install solution " . $config['required'] . "(Y/n)? ");
            if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                $this->error = "<h4>Install Failed. Solution " . $config['code'] . " required install " . $config['required'] . '</h4>';
                return false;
            } else {
                $this->install($config['required'], true);
            }
        }

        // Install plugins
        $package_folder = $this->unzipSolution($package_path);
        echo "2. Start install plugin: \n";
        if ($package_info['type'] == 'plugin') {
            $package_info['path'] = $package_folder . '/' . $package_info['folder_name'];
            $try = $this->installPlugin($package_info['solution'], $package_info);
            if (!$try) {
                $this->clearInstall($package_folder, $package_info['folder_name']);
                echo "Install plugin " . basename($package_info['folder_name']) . " failed";
                return $result;
            }
            echo "Install plugin " . basename($package_info['folder_name']) . " successfully";
        } else {
            $plugins = $this->StarterModel->getPlugins($package_folder);
            $config = null;
            foreach ($solutions as $item) {
                if ($item['code'] == $package_info['solution']) {
                    $config = $item;
                }
            }

            foreach ($plugins as $item) {
                $try = $this->installPlugin($config, $item);
                if (!$try) {
                    $this->clearInstall($package_info['package_path'], $config);
                    echo "Install plugin " . $item['folder_name'] . " failed";
                    return $result;
                }
                echo "Install plugin " . $item['folder_name'] . " successfully";
            }
            foreach (new \DirectoryIterator($package_folder) as $item) {
                if (!$item->isDot() && $item->isDir()) {
                    $temp_folder = $item->getBasename();
                }
            }
            copy($package_folder . '/' . $temp_folder . '/solution.php', SPT_PLUGIN_PATH . $config['code'] . '/solution.php');
        }

        if (is_dir(SPT_STORAGE_PATH . 'solutions')) {
            $this->file->removeFolder(SPT_STORAGE_PATH . 'solutions');
        }

        if (file_exists(SPT_STORAGE_PATH . "solution.zip")) {
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
        if (!$try['success']) {
            echo "Composer update failed!\n";
            return $result;
        } else {
            echo "Composer update done!\n";
        }

        // clear file install
        $this->clearInstall($package_path, '');
        $result['success'] = true;
        return $result;
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
        $solution_folder = $this->StarterModel->unzipSolution($solution_zip);
        if (!$solution_folder) {
            $this->error = 'Can`t read file solution';
            return false;
        }

        echo "2. Unzip solution folder done!\n";

        // check info
        foreach (new \DirectoryIterator($solution_folder) as $item) 
        {
            if (!$item->isDot() && $item->isDir()) {
                $temp_folder = $item->getBasename();
            }
        }

        if(!file_exists($solution_folder. '/'. $temp_folder .'/solution.php'))
        {
            if (!$solution_folder) {
                $this->error = 'Failed Solution info not found!';
                $this->StarterModel->clearInstall($solution_folder, '');
                return false;
            }
        }
        
        $solution_info = include_once($solution_folder. '/'. $temp_folder .'/solution.php');
        if (!$solution_info)
        {
            $this->error = 'Failed Solution info not found!';
            $this->clearInstall($solution_folder, '');
            return false;
        }

        // check dependencies
        if($solution_info['dependencies'])
        {
            $try = $this->StarterModel->checkDependencies($solution_info['dependencies']);
            if(!$try)
            {
                $this->error = 'Install fail, must install : '. implode(', ', $solution_info['dependencies']);
                $this->clearInstall($solution_folder, '');
                return false;
            }
        }
        
        // Install plugins
        $plugins = $this->StarterModel->getPlugins($solution_folder, false, $solution_info['folder_name'] ?? '');
        echo "3. Start install plugin: \n";

        copy($solution_folder . '/' . $temp_folder . '/solution.php', SPT_PLUGIN_PATH . $solution_info['folder_name'] . '/solution.php');

        foreach ($plugins as $item) 
        {
            $try = $this->StarterModel->installPlugin($solution_info, $item);
            if (!$try) 
            {
                $this->StarterModel->clearInstall($solution_folder, $solution_info);
                $this->error = "- Install plugin " . $item['folder_name'] . " failed:\n";
                return $result;
            }

            echo "- Install plugin " . $item['folder_name'] . " done!\n";
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
        $this->StarterModel->clearInstall($solution_folder, '');

        return true;
    }
}
