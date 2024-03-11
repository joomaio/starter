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
    private $skip = ['devtool'];

    public function getSolutions()
    {
        // Todo get storage solutions by link api
        if (!$this->solutions) {
            $this->solutions = [];
        }

        foreach (new \DirectoryIterator(SPT_PLUGIN_PATH) as $item) 
        {
            if (!$item->isDot() && $item->isDir()) 
            {
                // case not installed yet plugins
                if(!in_array($item->getBasename(), $this->skip))
                {
                    $info_path = $item->getPathname() . '/solution.php';
                    if (file_exists($info_path)) 
                    {
                        $info = include $info_path;
                        $this->solutions[$item->getBasename()] = $info;
                    }
                }
            }
        }

        if ($this->solutions) 
        {
            foreach ($this->solutions as $key => &$solution) 
            {
                $solution['status'] = file_exists(SPT_PLUGIN_PATH . $key);
                $solution['plugins'] = $this->getPlugins(SPT_PLUGIN_PATH . $key, true, $key);
            }
        }

        return $this->solutions;
    }

    public function install($solution, $required = false)
    {
        $solutions = $this->getSolutions();
        $is_cli = $this->isCli();
        $result = array(
            'success' => false,
            'message' => '',
        );

        if (!$solution) {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        $config = null;
        foreach ($solutions as $item) {
            if ($item['code'] == $solution) {
                $config = $item;
            }
        }

        if (!$config) {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        if (isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH . $config['required'])) {
            if ($is_cli) {
                $check = readline("Solution " . $config['code'] . " required install " . $config['required'] . ". Do you want continue install solution " . $config['required'] . "(Y/n)? ");
                if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                    $this->error = "<h4>Install Failed. Solution " . $config['code'] . " required install " . $config['required'] . '</h4>';
                    return false;
                } else {
                    $this->install($config['required'], true);
                }
            } else {
                $this->install($config['required'], true);
            }
        }

        if (file_exists(SPT_PLUGIN_PATH . $config['code'] . '/solution.php')) {
            if ($is_cli) {
                $check = readline($config['code'] . ' already exists, Do you still want to install it (Y/n)? ');
                if (strtolower($check) == 'n' || strtolower($check) == 'no') {
                    $this->error = "Stop Install";
                    return false;
                }
            } else {
                $this->error = "Solution" . $config['code'] . " already exists!";
                $result['message'] = "<h4>Solution" . $config['code'] . " already exists!</h4>";
                return $result;
            }
        }

        if ($is_cli) {
            echo "Start install " . $config['code'] . ": \n";
        }

        // Download zip solution
        if (!$config['link']) {
            $this->error = 'Invalid Solution link.';
            $result['message'] .= '<p>Invalid Solution link.</p>';
            return $result;
        }

        $solution_zip = $this->downloadSolution($config['link']);
        if (!$solution_zip) {
            $this->error = 'Download Solution Failed';
            $result['message'] .= '<p>Download Solution Failed.</p>';
            return $result;
        }

        if ($is_cli) {
            echo "1. Download solution done!\n";
        } else {
            $result['message'] .= '<h4>1/5. Download solution</h4>';
        }

        // unzip solution
        $solution_folder = $this->unzipSolution($solution_zip);
        if (!$solution_folder) {
            $this->error = 'Can`t read file solution';
            $result['message'] .= '<p>Can`t read file solution</p>';
            return $result;
        }

        if ($is_cli) {
            echo "2. Unzip solution folder done!\n";
        } else {
            $result['message'] .= "<h4>2/5. Unzip solution folder</h4>";
        }

        // Install plugins
        $plugins = $this->getPlugins($solution_folder);
        if ($is_cli) {
            echo "3. Start install plugin: \n";
        } else {
            $result['message'] .= "<h4>3/5. Start install plugin: </h4>";
        }

        foreach (new \DirectoryIterator($solution_folder) as $item) {
            if (!$item->isDot() && $item->isDir()) {
                $temp_folder = $item->getBasename();
            }
        }
        copy($solution_folder . '/' . $temp_folder . '/plugin.php', SPT_PLUGIN_PATH . $config['code'] . '/plugin.php');

        foreach ($plugins as $item) {
            $try = $this->installPlugin($config, $item);
            if (!$try) {
                $this->clearInstall($solution_folder, $config);
                $this->error = "- Install plugin " . $item['folder_name'] . " failed:\n";
                $result['message'] .= "<p>- Install plugin " . $item['folder_name'] . " failed:</p>";
                return $result;
            }
            if ($is_cli) {
                echo "- Install plugin " . $item['folder_name'] . " done!\n";
            } else {
                $result['message'] .= "<p>- Install plugin " . $item['folder_name'] . " successfully!</p>";
            }
        }

        if ($is_cli) {
            echo "4. Start generate data structure:\n";
        } else {
            $result['message'] .= "<h4>4/5. Start generate data structure:</h4>";
        }

        // generate database
        $entities = $this->DbToolModel->getEntities();
        foreach ($entities as $entity) {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'successfully' : 'failed';
            if ($is_cli) {
                echo str_pad($entity, 30) . $status . "\n";
            } else {
                $result['message'] .= '<p>' . str_pad($entity, 30) . $status . "</p>";
            }
        }
        if ($is_cli) {
            echo "Generate data structure done\n";
        } else {
            $result['message'] .= "<p>Generate data structure successfully.</p>";
        }

        // update composer
        if (!$required) {
            if ($is_cli) {
                echo "5. Start composer update:\n";
            } else {
                $result['message'] .= "<h4>5/5. Run composer update:</h4>";
            }
            $try = $this->ComposerModel->update($is_cli);
            if (!$try['success']) {
                if ($is_cli) {
                    echo "Composer update failed!\n";
                } else {
                    $result['message'] .= "<p>Composer update failed!</p>";
                }
                return $result;
            } else {
                if ($is_cli) {
                    echo "Composer update done!\n";
                } else {
                    $result["message"] .= $try['message'];
                    $result['message'] .= "<p>Composer update successfully!</p>";
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
        if (!$link) {
            return false;
        }

        if (!file_exists(SPT_STORAGE_PATH)) {
            $try = mkdir(SPT_STORAGE_PATH);
            if (!$try) {
                return false;
            }
        }

        $output_filename = SPT_STORAGE_PATH . "solution.zip";
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

        return SPT_STORAGE_PATH . "solution.zip";
    }

    public function unzipSolution($path)
    {
        if (!$path || !file_exists($path)) {
            $this->error = 'Invalid file zip solution';
            return false;
        }

        // remove folder solution tmp
        if (file_exists(SPT_STORAGE_PATH . 'solutions')) {
            $this->file->removeFolder(SPT_STORAGE_PATH . 'solutions');
        }

        // unzip
        $zip = new ZipArchive;
        $res = $zip->open($path);
        if ($res === TRUE) {
            $zip->extractTo(SPT_STORAGE_PATH . 'solutions');
            $zip->close();
            return SPT_STORAGE_PATH . 'solutions';
        }

        return false;
    }

    public function getPlugins($path)
    {
        $packages = [];
        foreach (new \DirectoryIterator($path) as $item) 
        {
            if (!$item->isDot() && $item->isDir()) 
            {
                $info_solution = $item->getPathname() . '/solution.php';
                if (file_exists($info_solution)) 
                {
                    return $this->getPlugins($item->getPathname());
                }

                // case not installed yet plugins
                $info_path = $item->getPathname() . '/plugin.php';
                if (file_exists($info_path)) 
                {
                    $packages[$item->getBasename()] = include $info_path;
                    $packages[$item->getBasename()]['path'] = $item->getPathname();
                }
            }
        }
        
        return $packages;
    }

    public function installPlugin($solution, $plugin)
    {
        $package_name = is_string($solution) ? $solution : $solution['folder_name'];
        if (!file_exists(SPT_PLUGIN_PATH . $package_name)) 
        {
            $try = mkdir(SPT_PLUGIN_PATH . $package_name);
            if (!$try) 
            {
                $this->error = "Error: Can't Create folder solution";
                return false;
            }
        }

        // check dependency
        if(!$this->checkDependencies($plugin['dependencies'] ?? []))
        {
            $this->error = "Install failed, must install: ". implode(', ', $plugin['dependencies']);
            return false;
        }

        // copy folder
        $new_plugin = SPT_PLUGIN_PATH . $package_name . '/' . $plugin['folder_name'];
        if (file_exists($new_plugin)) 
        {
            $this->file->removeFolder($new_plugin);
        }
        $try = $this->file->copyFolder($plugin['path'], $new_plugin);
        if (!$try) 
        {
            $this->error = "Error: Can't create folder solution";
            return false;
        }

        // run installer
        $class = $this->app->getNameSpace() . '\\' . $package_name . '\\' . $plugin['folder_name'] . '\\registers\\Installer';
        if (method_exists($class, 'install')) 
        {
            $class::install($this->app);
        }

        // update asset file
        if (method_exists($class, 'assets')) 
        {
            $assets = $class::assets();
            if ($assets && is_array($assets)) 
            {
                foreach ($assets as $key => $asset) 
                {
                    if (file_exists($new_plugin . '/' . $asset)) 
                    {
                        if(file_exists(PUBLIC_PATH . 'assets/' . $key))
                        {
                            $this->file->removeFolder(PUBLIC_PATH . 'assets/' . $key);
                        }
                        $try = $this->file->copyFolder($new_plugin . '/' . $asset, PUBLIC_PATH . 'assets/' . $key);
                    }
                }
            }
        }

        $is_cli = $this->isCli();

        // create super user
        if (method_exists($class, 'afterInstall')) 
        {
            $class::afterInstall($this->app, $is_cli);
        }

        return true;
    }

    public function clearInstall($solution, $config = [])
    {
        if (file_exists($solution)) {
            $try = $this->file->removeFolder($solution);
        }

        $package_path = is_string($config) ? $config : $config['folder_name'];
        if ($config && file_exists(SPT_PLUGIN_PATH . $package_path)) 
        {
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $package_path);
        }

        if (file_exists(SPT_STORAGE_PATH . "solution.zip")) 
        {
            $try = unlink(SPT_STORAGE_PATH . "solution.zip");
        }

        return true;
    }

    public function uninstallPlugin($plugin, $solution)
    {
        // check folder plugin
        if (!file_exists($plugin)) {
            $this->error = 'Plugin Invalid';
            return false;
        }
        // run uninstall
        $class = $this->app->getNameSpace() . '\\' . $solution . '\\' . basename($plugin) . '\\registers\\Installer';
        if (method_exists($class, 'uninstall')) {
            $class::uninstall($this->app);
        }

        // update asset file
        if (method_exists($class, 'assets')) {
            $assets = $class::assets();
            if ($assets && is_array($assets)) {
                foreach ($assets as $key => $asset) {
                    if (file_exists(PUBLIC_PATH . 'assets/' . $key)) {
                        $this->file->removeFolder(PUBLIC_PATH . 'assets/' . $key);
                    }
                }
            }
        }

        $try = $this->file->removeFolder($plugin);
        if(!$try)
        {
            $this->error = 'Can`t remove plugin';
        }
        return $try;
    }

    public function isCli()
    {
        $check = php_sapi_name();
        return $check === 'cli';
    }

    public function prepareInstall($data, $required = false)
    {
        $result = array(
            'success' => false,
            'message' => '',
            'data' => '',
        );

        if (!$data['info']) {
            $result['message'] = '<h4>Invalid Solution</h4>';
            return $result;
        }

        $try = $this->checkDependencies($data['info']['dependencies'] ?? []);
        if(!$try)
        {
            $result['message'] = '<h4>Install fail, must install : '. implode(', ', $data['info']['dependencies']). '</h4>';
            return $result;
        }

        $result['data'] = $data['type'] == 'solution' ? $data['info']['solution'] : '';
        $result['success'] = true;
        $result['message'] = $data['action'] == 'upload' ? '<h4>2/5. Check install availability</h4>' : '<h4>1/6. Check install availability</h4>';
        return $result;
    }

    public function prepareUninstall($package, $type, $solution)
    {
        $result = array(
            'success' => false,
            'message' => '<h4>1/3. Check uninstall availability</h4>',
            'data' => '',
        );

        if (!$package) {
            $result['message'] .= '<h4>Invalid Package</h4>';
            return $result;
        }

        // check invalid solution
        if(!$this->checkValidPackage($solution, $type == 'solution' ? '' : $package))
        {
            $result['message'] .= '<h4>Invalid Package</h4>';
            return $result;
        }

        // check dependencies
        $package = $type == 'solution' ? $solution : $solution.'/'. $package;
        if(!$this->checkUninstall($package))
        {
            $result['message'] .= '<h4>Uninstall failed, '. $this->error .' is depending on '. $package .'</h4>';
            return $result;
        }

        $result["success"] = true;
        $result["data"] = $type == 'solution' ? $solution : $solution . '/' . $package;
        return $result;
    }

    public function downloadZipSolution($link)
    {
        $result = array(
            'success' => false,
            'message' => '<h4>2/6. Download solution</h4>',
            'data' => '',
        );
        // Download zip solution
        if (!$link) {
            $result['message'] .= '<p>Invalid Solution link</p>';
            return $result;
        }

        $solution_zip = $this->downloadSolution($link);
        if (!$solution_zip) {
            $result['message'] .= '<p>Download Solution Failed</p>';
            return $result;
        }

        $result['data'] = basename($solution_zip);
        $result['success'] = true;
        return $result;
    }

    public function unzipZipSolution($solution_zip, $upload = false)
    {
        $result = array(
            'success' => false,
            'message' => '<h4>3/6. Unzip solution folder</h4>',
            'data' => '',
            'info' => []
        );

        if (!file_exists(SPT_STORAGE_PATH)) {
            $try = mkdir(SPT_STORAGE_PATH);
            if (!$try) {
                return false;
            }
        }

        // unzip solution
        $solution_folder = $this->unzipSolution(SPT_STORAGE_PATH . $solution_zip);

        if (!$solution_folder) {
            $result['message'] .= '<p>Can`t read file solution</p>';
            return $result;
        }

        if ($upload) {
            $dir = new \DirectoryIterator($solution_folder);
            foreach ($dir as $item) {
                if ($item->isDir() && !$item->isDot()) {
                    $folder_name = $item->getBasename();
                }
            }

            if (!$folder_name) {
                $result['message'] .= '<p>Can`t read file solution</p>';
                return $result;
            }

            if(file_exists($solution_folder . '/' . $folder_name . '/solution.php'))
            {
                $result['info'] = include $solution_folder . '/' . $folder_name . '/solution.php';
            }
            elseif(file_exists($solution_folder . '/' . $folder_name . '/plugin.php'))
            {
                $result['info'] = include $solution_folder . '/' . $folder_name . '/plugin.php';
            }

            if(!$result['info'])
            {
                $result['message'] .= '<p>Can`t read file package info</p>';
                return $result;
            }

            $result['message'] = '<h4>1/5. Unzip package folder</h4>';
        }

        $result['data'] = basename($solution_folder). '/'. $folder_name;
        $result['success'] = true;
        return $result;
    }

    public function installPlugins($data)
    {
        $result = array(
            'success' => false,
            'message' => '',
        );

        $data['folder_name'] = $data['package'];
        $data['package_path'] = SPT_STORAGE_PATH . $data['package_path'];
        $result['message'] .= $data['action'] == 'upload' ? '<h4>3/5. Start install plugins</h4>' : '<h4>4/6. Start install plugins</h4>';
        if ($data['type'] == 'plugin') 
        {
            $data['path'] = $data['package_path'];
            $try = $this->installPlugin($data['solution'], $data);
            if (!$try) 
            {
                $this->clearInstall($data['package_path'], $data['package']);
                $result['message'] .= "<p>Install plugin " . basename($data['package']) . " failed</p>";
                return $result;
            }

            $this->registerSolution($data['solution'] ?? '');
            $result['message'] .= "<p>Install plugin " . basename($data['package']) . " successfully</p>";
        } 
        else 
        {
            $plugins = $this->getPlugins($data['package_path']);
            if(!file_exists($data['package_path'].'/solution.php'))
            {
                $result['message'] .= "<p>Invalid solution info</p>";
                return $result;
            }

            $info = include $data['package_path'].'/solution.php';
            if(!$info)
            {
                $result['message'] .= "<p>Invalid solution info</p>";
                return $result;
            }

            foreach ($plugins as $item) 
            {
                $try = $this->installPlugin($info, $item);
                if (!$try) 
                {
                    $this->clearInstall($data['package_path'], $info);
                    $result['message'] .= "<p>Install plugin " . $item['folder_name'] . " failed</p>";
                    return $result;
                }

                $result['message'] .= "<p>Install plugin " . $item['folder_name'] . " successfully</p>";
            }

            copy($data['package_path'] . '/solution.php', SPT_PLUGIN_PATH . $info['folder_name'] . '/solution.php');
        }

        if (is_dir(SPT_STORAGE_PATH . 'solutions')) {
            $this->file->removeFolder(SPT_STORAGE_PATH . 'solutions');
        }

        if (file_exists(SPT_STORAGE_PATH . "solution.zip")) {
            unlink(SPT_STORAGE_PATH . "solution.zip");
        }

        $result['success'] = true;
        return $result;
    }

    public function uninstallPlugins($data)
    {
        $result = array(
            'success' => false,
            'message' => '<h4>2/3. Uninstall plugins</h4>',
        );
        
        if ($data['type'] == 'solution') 
        {
            $plugins = $this->getPlugins(SPT_PLUGIN_PATH . $data['solution'], true);
            foreach ($plugins as $plugin) 
            {
                $try = $this->uninstallPlugin($plugin['path'], $data['solution']);
                if (!$try) 
                {
                    $result['message'] .= "<p>Uninstall plugin " . basename($plugin['path']) . " failed</p>";
                    return $result;
                }

                $result['message'] .= "<p>Uninstall plugin " . basename($plugin['path']) . " successfully</p>";
            }
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $data['solution']);
        } 
        else 
        {
            $package_path = SPT_PLUGIN_PATH . $data['solution'] . '/' . $data['package'];
            $try = $this->uninstallPlugin($package_path, $data['solution']);
            if (!$try) 
            {
                $result['message'] .= "<p>Uninstall plugin " . $data['package'] . " failed</p>";
                return $result;
            }

            $result['message'] .= "<p>Uninstall plugin " . $data['package'] . " successfully</p>";
            $try = $this->file->removeFolder($package_path);
        }

        $result['success'] = true;
        return $result;
    }

    public function generateDataStructure($upload = false)
    {
        $result = array(
            'success' => false,
            'message' => '',
        );

        $result['message'] .= $upload ? '<h4>4/5. Start generate data structure</h4>' : '<h4>5/6. Start generate data structure</h4>';
        // generate database
        $entities = $this->DbToolModel->getEntities();
        foreach ($entities as $entity) {
            $try = $this->{$entity}->checkAvailability();
            $status = $try !== false ? 'successfully' : 'failed';
            $result['message'] .= '<p>' . str_pad($entity, 30) . $status . "</p>";

        }
        $result['message'] .= "<p>Generate data structure successfully</p>";

        $result['success'] = true;
        return $result;
    }

    public function composerUpdate($install)
    {
        $result = array(
            'success' => false,
            'message' => '',
        );

        switch ($install) {
            case 'install':
                $result['message'] .= '<h4>6/6. Run composer update</h4>';
                break;
            case 'upload':
                $result['message'] .= '<h4>5/5. Run composer update</h4>';
                break;
            default:
                $result['message'] .= '<h4>3/3. Run composer update</h4>';
        }

        $try = $this->ComposerModel->update();
        if (!$try['success']) {
            $result['message'] .= "<p>Composer update failed</p>";
            return $result;
        } else {
            $result['message'] .= $try['message'];
            $result['message'] .= "<p>Composer update done</p>";
        }

        $result['success'] = true;
        return $result;
    }

    public function checkDependencies($dependencies, $plugins = [])
    {
        $solutions = $this->getSolutions();
        $themes = $this->ThemeModel->getThemes();

        if(!$dependencies)
        {
            return true;
        }

        $dependencies = is_string($dependencies) ? [$dependencies] : $dependencies;
        $try = true;
        foreach($dependencies as $item)
        {
            $arr = explode('/', $item);
            $solution = $arr[0] ?? '';
            $plugin = $arr[1] ?? '';
            if($solution && $plugin)
            {
                continue;
            }

            if($solution == 'themes' && isset($themes[$plugin]) && $themes[$plugin])
            {
                continue;
            }

            if(!isset($solutions[$solution]) || !$solutions[$solution] || ($plugin && !isset($solutions[$solution]['plugins'][$plugin])))
            {
                $try = false;
                break;
            }
        }

        return $try;
    }

    public function checkValidPackage($solution, $plugin)
    {
        if(!file_exists(SPT_PLUGIN_PATH. $solution.'/solution.php'))
        {
            return false;
        }

        $solution_info = include SPT_PLUGIN_PATH. $solution.'/solution.php';
        if(!$solution_info)
        {
            return false;
        }

        if($plugin)
        {
            if(!file_exists(SPT_PLUGIN_PATH. $solution. '/'. $plugin .'/plugin.php'))
            {
                return false;
            }
    
            $plugin_info = include SPT_PLUGIN_PATH. $solution. '/'. $plugin .'/plugin.php';
            if(!$plugin_info)
            {
                return false;
            }
        }

        return true;
    }

    public function checkUninstall($package)
    {
        $solutions = $this->getSolutions();
        foreach($solutions as $item)
        {
            if(isset($item['dependencies']))
            {
                if(in_array($package, $item['dependencies']))
                {
                    $this->error = $item['name'];
                    return false;
                }
            }

            if(isset($item['plugins']) && $item['plugins'])
            {
                foreach($item['plugins'] as $plugin)
                {
                    if(isset($plugin['dependencies']))
                    {
                        if(in_array($package, $plugin['dependencies']))
                        {
                            $this->error = $plugin['name'];
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function loadButton()
    {
        $buttons = [];
        $this->app->plgLoad('installer', 'registerButton', function($result) use (&$buttons) {
            if($result && $result['solution'])
            {
                $buttons[$result['solution']] = $result;
            }
        });

        return $buttons;
    }

    public function registerSolution($solution)
    {
        // check invalid
        if(file_exists(SPT_PLUGIN_PATH. $solution.'/solution.php'))
        {
            return true;
        }

        $solution_info = [
            'tags' => [],
            'type' => 'solution',
            'solution' => $solution,
            'folder_name' => $solution,
            'name' => $solution,
            'dependencies' => [],
        ];

        file_put_contents(SPT_PLUGIN_PATH. $solution.'/solution.php', '<?php return ' . var_export($solution_info, true) . ';');
    
        return true;
    }

    public function updateConfig($data)
    {
        $fields = ['admin_theme', 'default_theme'];
        foreach($fields as $field)
        {
            $this->OptionModel->set($field, $data[$field] ?? '');
        }

        return true;
    }
}
