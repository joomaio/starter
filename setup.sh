#!/bin/bash

# Step 1 Install source 
repository_url="https://github.com/joomaio/starter.git"

# Check if Git is installed
if ! command -v git &> /dev/null; then
    echo "Git is not installed. Please install Git before using this script."
    exit 1
fi

# Perform git clone
git clone "$repository_url"

# Check if git clone was successful
if [ $? -eq 0 ]; then
    echo "Git clone successful from $repository_url."
else
    echo "Git clone failed."
    exit 1
fi

# checkout to web branch (remove when after merge branch web)
cd starter
git checkout webapp
echo 'checkout to web';

# Run composer update
fpm=""
web_root_path=""
user=""

# Xử lý tham số dòng lệnh
while [ "$#" -gt 0 ]; do
    case "$1" in
        -fpm|--fpm)
            fpm="$2"
            shift 2
            ;;
        -web_root_path|--web_root_path)
            web_root_path="$2"
            shift 2
            ;;
        -user|--user)
            user="$2"
            shift 2
            ;;
    esac
done
if [ -n "$fpm" ]; then
    echo "Composer Install Start:"
    if ! command -v docker &> /dev/null; then
        echo "Setup failed. Docker is not installed"
        exit 1
    fi

    if ! docker info &> /dev/null; then
        echo "Setup failed. Docker daemon is not running"
        exit 1
    fi

    docker exec -it $fpm bash -c "cd $web_root_path/starter && composer install"

    if [ $? -eq 0 ]; then
        echo "Composer install done!"
    else
        echo "Setup failed. Composer install error"
        exit 1
    fi
else
    if ! command -v php &> /dev/null; then
        echo "Setup failed. PHP is not installed."
        exit 1
    fi

    composer install
    if [ $? -eq 0 ]; then
        echo "Composer install done!"
    else
        echo "Setup failed. Composer install error"
        exit 1
    fi
fi

# Run setup config
if [ ! -d "config.sample" ]; then
    echo "config sample not exist."
    exit 1
fi

if [ ! -d "config" ]; then
    mkdir -p "config"
fi

cp -r config.sample/* config

echo "Copy folder config done."

# setup database config
if [ ! -f "config/database.php" ]; then
    echo "File config not exists."
    exit 1
fi
echo "Setup database config:"
echo -n "Enter database host: "
read host
echo -n "Enter database username: "
read username
echo -n "Enter database password: "
read password
echo -n "Enter database name: "
read database
echo -n "Enter database prefix: "
read prefix
old_string="'host' => '',"
new_string="'host' => '$host',"
sed -i "s/$old_string/$new_string/g" "config/database.php"
old_string="'username' => '',"
new_string="'username' => '$username',"
sed -i "s/$old_string/$new_string/g" "config/database.php"
old_string="'password' => '',"
new_string="'password' => '$password',"
sed -i "s/$old_string/$new_string/g" "config/database.php"
old_string="'database' => '',"
new_string="'database' => '$database',"
sed -i "s/$old_string/$new_string/g" "config/database.php"
old_string="'prefix' => '',"
new_string="'prefix' => '$prefix',"
sed -i "s/$old_string/$new_string/g" "config/database.php"
echo "Config Done!"

# run setup permission
if [ -n "$user" ]; then
    chown -R "$user":"$user" ../starter
    if [ $? -eq 0 ]; then
        echo "Update permission done."
    else
        echo "Setup failed. Permission can't update"
        exit 1
    fi
fi

echo "Setup Done!"