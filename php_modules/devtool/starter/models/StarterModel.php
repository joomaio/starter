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
        if (!$this->solutions) {
            $this->solutions = simplexml_load_file(ROOT_PATH . 'solution.xml');
        }

        $solutions = [];
        if ($this->solutions) {
            foreach ($this->solutions as $solution) {
                $tmp = (array) $solution;
                $tmp['status'] = false;
                if (file_exists(SPT_PLUGIN_PATH . $tmp['code'])) {
                    $tmp['status'] = true;

                    $tmp['plugins'] = $this->getPlugins(SPT_PLUGIN_PATH . $tmp['code'], true);

                    $class = $this->app->getNameSpace() . '\\' . $tmp['code'] . '\\' . basename($tmp['code']) . '\\registers\\Installer';
                    if (method_exists($class, 'registerButton')) {
                        if ($class::registerButton($this->app)) {
                            $tmp['button'] = $class::registerButton($this->app);
                        }
                    }
                }

                $solutions[] = $tmp;
            }
        }
        return $solutions;
    }

    public function installFileZipCli($package_path) 
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
        $info_list = simplexml_load_file($package_folder . '/' . $dir->getBasename() . '/information.xml');

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
        if ($package_info['type'] == 'solution') {
            $config = null;
            foreach ($solutions as $item) {
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

            if (file_exists(SPT_PLUGIN_PATH . $config['code'])) {
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
                if ($package_info['require']) {
                    $require_arr = explode(',', $package_info['require']);
                    foreach ($require_arr as $item) {
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
            $try = $this->installPlugin($package_info['solution'], $package_folder . '/' . $package_info['folder_name']);
            if (!$try) {
                $this->clearInstall($package_folder, $package_info['folder_name']);
                echo "Install plugin " . basename($package_info['folder_name']) . " failed";
                return $result;
            }
            echo "Install plugin " . basename($package_info['folder_name']) . " successfully";
        } else {
            $plugins = $this->getPlugins($package_folder);
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
                    echo "Install plugin " . basename($item) . " failed";
                    return $result;
                }
                echo "Install plugin " . basename($item) . " successfully";
            }
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
        foreach ($entities as $entity) {
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
        $this->clearInstall($package_path);
        $result['success'] = true;
        return $result;
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

        if (file_exists(SPT_PLUGIN_PATH . $config['code'])) {
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

        foreach ($plugins as $item) {
            $try = $this->installPlugin($config, $item);
            if (!$try) {
                $this->clearInstall($solution_folder, $config);
                $this->error = "- Install plugin " . basename($item) . " failed:\n";
                $result['message'] .= "<p>- Install plugin " . basename($item) . " failed:</p>";
                return $result;
            }
            if ($is_cli) {
                echo "- Install plugin " . basename($item) . " done!\n";
            } else {
                $result['message'] .= "<p>- Install plugin " . basename($item) . " successfully!</p>";
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

    public function getPlugins($path, $folder_solution = false)
    {
        $packages = [];
        foreach (new \DirectoryIterator($path) as $item) {
            if (!$item->isDot() && $item->isDir()) {
                if (!$folder_solution) {
                    return $this->getPlugins($item->getPathname(), true);
                }

                if ($item->getBasename() == 'registers') {
                    continue;
                }
                // get package info
                if (substr($path, -1) == '/') {
                    $path = substr($path, 0, -1);
                }
                $tmp = explode('/', $path);
                $solution_name = end($tmp);

                $class = $this->app->getNameSpace() . '\\' . $solution_name . '\\' . $item->getBasename() . '\\registers\\Installer';

                if (method_exists($class, 'info')) {
                    $packages[$item->getBasename()] = $class::info();
                    $packages[$item->getBasename()]['path'] = $item->getPathname();
                } else {
                    $packages[$item->getBasename()] = $item->getPathname();
                }
            }
        }
        return $packages;
    }

    public function installPlugin($solution, $plugin)
    {
        $package_name = is_string($solution) ? $solution : $solution['code'];
        if (!file_exists(SPT_PLUGIN_PATH . $package_name)) {
            $try = mkdir(SPT_PLUGIN_PATH . $package_name);
            if (!$try) {
                $this->error = "Error: Can't Create folder solution";
                return false;
            }
        }

        // copy folder
        $new_plugin = SPT_PLUGIN_PATH . $package_name . '/' . basename($plugin);
        if (file_exists($new_plugin)) {
            $this->file->removeFolder($new_plugin);
        }

        $try = $this->file->copyFolder($plugin, $new_plugin);
        if (!$try) {
            $this->error = "Error: Can't create folder solution";
            return false;
        }

        // run installer
        $class = $this->app->getNameSpace() . '\\' . $package_name . '\\' . basename($plugin) . '\\registers\\Installer';
        if (method_exists($class, 'install')) {
            $class::install($this->app);
        }

        // update asset file
        if (method_exists($class, 'assets')) {
            $assets = $class::assets();
            if ($assets && is_array($assets)) {
                foreach ($assets as $key => $asset) {
                    if (file_exists($new_plugin . '/' . $asset)) {
                        $try = $this->file->copyFolder($new_plugin . '/' . $asset, PUBLIC_PATH . 'assets/' . $key);
                    }
                }
            }
        }

        // create super user
        if (method_exists($class, 'createSuperUser')) {
            $super_user_groups = [];
            $user_groups = $this->GroupEntity->list(0, 0, []);
            foreach ($user_groups as $group) {
                if (str_contains($group['access'], 'user_manager')) {
                    $super_user_groups[] = $group['id'];
                }
            }

            if (count($super_user_groups) == 0) {
                $access = $this->PermissionModel->getAccess();

                // Create group
                $group = [
                    'name' => 'Super',
                    'description' => 'Super Group',
                    'access' => json_encode($access),
                    'status' => 1,
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'modified_by' => 0,
                    'modified_at' => date('Y-m-d H:i:s')
                ];

                $created_group = $this->GroupEntity->add($group);

                if (!$created_group) {
                    $this->error = 'Create Group Failed';
                    return false;
                }
            }

            $super_users = $this->UserGroupEntity->list(0, 0, ['group_id IN (' . implode(',', $super_user_groups) . ')']);

            if (count($super_user_groups) == 0 || count($super_users) == 0) {
                echo "Plugin Pnote requires to create super admin. \n";
                $enter_info = true;
                while ($enter_info) {
                    $name = readline("Enter your name:\n");
                    $username = readline("Enter your username:\n");
                    $email = readline("Enter your email:\n");
                    $password = readline("Enter your password (At least 6 characters):\n");

                    $user = [
                        'username' => $username,
                        'name' => $name,
                        'email' => $email,
                        'status' => 1,
                        'password' => $password,
                        'confirm_password' => $password,
                        'created_by' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'modified_by' => 0,
                        'modified_at' => date('Y-m-d H:i:s')
                    ];

                    $validate = $this->UserEntity->validate($user);
                    if (!$validate) {
                        echo "Information you entered is invalid. Please enter again! \n";
                    } else {
                        $enter_info = false;
                    }
                }

                $created_user = $this->UserEntity->add($user);
                if (!$created_user) {
                    $this->error = 'Create User Failed';
                    return false;
                }

                if (count($super_user_groups) == 0) {
                    $group_id = $created_group;
                } else {
                    $group_id = $super_user_groups[0];
                }

                $created_user_group = $this->UserGroupEntity->add([
                    'group_id' => $group_id,
                    'user_id' => $created_user,
                ]);

                if (!$created_user_group) {
                    $this->error = 'Create User Group Failed';
                    return false;
                }

            }
        }

        return true;
    }

    public function clearInstall($solution, $config = [])
    {
        if (file_exists($solution)) {
            $try = $this->file->removeFolder($solution);
        }

        $package_path = is_string($config) ? $config : $config['code'];
        if ($config && file_exists(SPT_PLUGIN_PATH . $package_path)) {
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $package_path);
        }

        if (file_exists(SPT_STORAGE_PATH . "solution.zip")) {
            $try = unlink(SPT_STORAGE_PATH . "solution.zip");
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

        if (!$solution) {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        $config = null;
        foreach ($solutions as $item) {
            if ($item['code'] == $solution) {
                $config = (array) $item;
            }
        }

        if (!$config) {
            $this->error = 'Invalid Solution.';
            $result['message'] = '<h4>Invalid Solution.</h4>';
            return $result;
        }

        if (!file_exists(SPT_PLUGIN_PATH . $solution)) {
            $this->error = 'Uninstall Failed. Cannot find installed solution ' . $solution;
            $result['message'] = '<h4>Uninstall Failed. Cannot find installed solution ' . $solution . '.</h4>';
            return $result;
        }

        // start uninstall
        if ($is_cli) {
            echo "Start uninstall solution " . $solution . "\n";
            echo "1. Uninstall plugins: \n";
        } else {
            $result['message'] .= "<h4>1/2. Uninstall plugins: </h4>";
        }

        $plugins = $this->getPlugins(SPT_PLUGIN_PATH . $solution, true);
        foreach ($plugins as $plugin) {
            $try = $this->uninstallPlugin($plugin['path'], $solution);
            if (!$try) {
                if ($is_cli) {
                    echo "- Uninstall plugin " . $plugin['folder_name'] . " failed:\n";
                    return false;
                } else {
                    $result['message'] .= "<p>- Uninstall plugin " . $plugin['folder_name'] . " failed:</p>";
                    return $result;
                }
            }

            if ($is_cli) {
                echo "- Uninstall plugin " . $plugin['folder_name'] . " done!\n";
            } else {
                $result['message'] .= "<p>- Uninstall plugin " . $plugin['folder_name'] . " successfully!</p>";
            }
        }
        $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $solution);

        if ($is_cli) {
            echo "2. Start composer update:\n";
        } else {
            $result['message'] .= "<h4>2/2. Run composer update:</h4>";
        }

        $try = $this->ComposerModel->update($is_cli);

        if (!$try['success']) {
            if ($is_cli) {
                echo "Composer update failed!\n";
                return false;
            } else {
                $result['message'] .= "<p>Composer update failed!</p>";
                return $result;
            }
        } else {
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
        return true;
    }

    public function isCli()
    {
        $check = php_sapi_name();
        return $check === 'cli';
    }

    public function prepare_install($data, $required = false)
    {
        $solutions = $this->getSolutions();
        $result = array(
            'success' => false,
            'message' => '',
            'data' => '',
        );

        if (!$data['solution']) {
            $result['message'] = '<h4>Invalid Solution</h4>';
            return $result;
        }

        if ($data['type'] == 'solution') {
            $config = null;
            foreach ($solutions as $item) {
                if ($item['code'] == $data['solution']) {
                    $config = $item;
                }
            }

            if (!$config) {
                $result['message'] = '<h4>Invalid Solution</h4>';
                return $result;
            }

            if (isset($config['required']) && $config['required'] && !file_exists(SPT_PLUGIN_PATH . $config['required'])) {
                $this->install($config['required'], true);
            }

            if (file_exists(SPT_PLUGIN_PATH . $config['code'])) {
                $result['message'] = "<h4>Solution " . $config['code'] . " already exists</h4>";
                return $result;
            }
        } else {
            if (file_exists(SPT_PLUGIN_PATH . $data['solution'] . '/' . $data['package'])) {
                $result['message'] = "<h4>Plugin " . $data['package'] . " already exists</h4>";
                return $result;
            } else {
                if ($data['require']) {
                    $require_arr = explode(',', $data['require']);
                    foreach ($require_arr as $item) {
                        $this->install($item, true);
                    }
                }
            }
        }


        $result['data'] = $data['type'] == 'solution' ? $config['link'] : '';
        $result['success'] = true;
        $result['message'] .= $data['action'] == 'upload' ? '<h4>2/5. Check install availability</h4>' : '<h4>1/6. Check install availability</h4>';
        return $result;
    }

    public function prepare_uninstall($package, $type, $solution)
    {
        $solutions = $this->getSolutions();
        $result = array(
            'success' => false,
            'message' => '<h4>1/3. Check uninstall availability</h4>',
            'data' => '',
        );

        if (!$package) {
            $result['message'] .= '<h4>Invalid Package</h4>';
            return $result;
        }

        $config = null;
        foreach ($solutions as $item) {
            if ($item['code'] == $solution) {
                if ($type == 'solution') {
                    $config = (array) $item;
                } else {
                    if (array_key_exists($package, $item['plugins'])) {
                        $config = (array) $item['plugins'][$package];
                    }
                }

            }
        }

        if (!$config) {
            $result['message'] .= '<h4>Invalid Package</h4>';
            return $result;
        }

        $result["success"] = true;
        $result["data"] = $type == 'solution' ? $solution : $solution . '/' . $package;
        return $result;
    }

    public function download_solution($link)
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

        $result['data'] = $solution_zip;
        $result['success'] = true;
        return $result;
    }

    public function unzip_solution($solution_zip, $upload = false)
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
        $solution_folder = $this->unzipSolution($solution_zip);

        if (!$solution_folder) {
            $result['message'] .= '<p>Can`t read file solution</p>';
            return $result;
        }

        if ($upload) {
            $dir = new \DirectoryIterator($solution_folder);
            foreach($dir as $item)
            {
                if ($item->isDir() && !$item->isDot()) {
                    $folder_name = $item->getBasename();
                }
            }

            if (!$folder_name) 
            {
                $result['message'] .= '<p>Can`t read file solution</p>';
                return $result;
            }

            $info_list = simplexml_load_file($solution_folder . '/' . $folder_name . '/information.xml');

            foreach ($info_list as $idx => $info) {
                $tmp = (array) $info;
                if (array_key_exists(0, $tmp)) {
                    $result['info'][$idx] = $tmp[0];
                } else {
                    $result['info'][$idx] = '';
                }
            }

            $result['message'] = '<h4>1/5. Unzip package folder</h4>';
        }

        $result['data'] = $solution_folder;
        $result['success'] = true;
        return $result;
    }

    public function install_plugins($data)
    {
        $result = array(
            'success' => false,
            'message' => '',
        );

        $result['message'] .= $data['action'] == 'upload' ? '<h4>3/5. Start install plugins</h4>' : '<h4>4/6. Start install plugins</h4>';
        if ($data['type'] == 'plugin') {
            $try = $this->installPlugin($data['solution'], $data['package_path'] . '/' . $data['package']);
            if (!$try) {
                $this->clearInstall($data['package_path'], $data['package']);
                $result['message'] .= "<p>Install plugin " . basename($data['package']) . " failed</p>";
                return $result;
            }
            $result['message'] .= "<p>Install plugin " . basename($data['package']) . " successfully</p>";
        } else {
            // Install plugins
            $plugins = $this->getPlugins($data['package_path']);

            $solutions = $this->getSolutions();
            $config = null;
            foreach ($solutions as $item) {
                if ($item['code'] == $data['solution']) {
                    $config = $item;
                }
            }

            foreach ($plugins as $item) {
                $try = $this->installPlugin($config, $item);
                if (!$try) {
                    $this->clearInstall($data['package_path'], $config);
                    $result['message'] .= "<p>Install plugin " . basename($item) . " failed</p>";
                    return $result;
                }
                $result['message'] .= "<p>Install plugin " . basename($item) . " successfully</p>";
            }
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

    public function uninstall_plugins($data)
    {
        $result = array(
            'success' => false,
            'message' => '<h4>2/3. Uninstall plugins</h4>',
        );

        if ($data['type'] == 'solution') {
            $plugins = $this->getPlugins(SPT_PLUGIN_PATH . $data['solution'], true);

            foreach ($plugins as $plugin) {
                $try = $this->uninstallPlugin($plugin['path'], $data['solution']);
                if (!$try) {
                    $result['message'] .= "<p>Uninstall plugin " . basename($plugin['path']) . " failed</p>";
                    return $result;
                }

                $result['message'] .= "<p>Uninstall plugin " . basename($plugin['path']) . " successfully</p>";
            }
            $try = $this->file->removeFolder(SPT_PLUGIN_PATH . $data['solution']);
        } else {
            $package_path = SPT_PLUGIN_PATH . $data['solution'] . '/' . $data['package'];
            $try = $this->uninstallPlugin($package_path, $data['solution']);
            if (!$try) {
                $result['message'] .= "<p>Uninstall plugin " . $data['package'] . " failed</p>";
                return $result;
            }

            $result['message'] .= "<p>Uninstall plugin " . $data['package'] . " successfully</p>";
            $try = $this->file->removeFolder($package_path);
        }

        $result['success'] = true;
        return $result;
    }

    public function generate_data_structure($upload = false)
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

    public function composer_update($install)
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
}
