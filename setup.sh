#!/bin/bash

# Step 1 Install source 
repository_url="https://github.com/joomaio/starter.git"

fpm=""
web_root_path=""
user=""
document_root=""

# Read params
while [ "$#" -gt 0 ]; do
    case "$1" in
        -fpm|--fpm)
            fpm="$2"
            shift 2
            ;;
        -document_root|--document_root)
            document_root="$2"
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
        --)
            shift
            break
            ;;
        *)
            break
            ;;
    esac
done

script_path=$(readlink -f "$0")
script_dir=$(dirname "$script_path")

if [ -z "$document_root" ]; then
    echo -n "Enter document root (Enter to skip, default is $script_dir/starter): "
    read document_root
    if [ -z "$document_root" ]; then
        $document_root = "$script_dir/starter"
    fi
fi

# Check if Git is installed
if ! command -v git &> /dev/null; then
    echo "Git is not installed. Please install Git before using this script."
    exit 1
fi

# Perform git clone
git clone "$repository_url" $document_root

# Check if git clone was successful
if [ $? -eq 0 ]; then
    echo "Git clone successful from $repository_url."
else
    echo "Git clone failed."
    exit 1
fi

# checkout to web branch (remove when after merge branch web)
cd $document_root

# run setup composer install
if [ -z "$fpm" ]; then
    echo -n "Enter fpm docker container name (Enter to skip): "
    read fpm
fi

if [ -z "$web_root_path" ]; then
    echo -n "Enter web root path (Enter to skip): "
    read web_root_path
fi

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

    docker exec -it $fpm bash -c "cd $web_root_path && composer install"

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

# generate secret key
RANDOM_STRING=$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 10)
echo "Generate secret key done."
old_string="'secrect' => 'sid',"
new_string="'secrect' => '$RANDOM_STRING',"
sed -i "s/$old_string/$new_string/g" "config/general.php"

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

# Config starter
echo "Setup starter config"
echo -n "Enter access key (default is random string): "
read access_key
if [ -z "$access_key" ]; then
  access_key=$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 10)
fi
old_string="'access_key' => 'OPQzxeyQpU',"
new_string="'access_key' => '$access_key',"
sed -i "s/$old_string/$new_string/g" "config/starter.php"

echo -n "Enter username starter (default is starter): "
read username
if [ -z "$username" ]; then
  username="starter"
fi
old_string="'username' => 'starter',"
new_string="'username' => '$username',"
sed -i "s/$old_string/$new_string/g" "config/starter.php"

echo -n "Enter password starter (default is random string): "
read password
if [ -z "$password" ]; then
  password=$(tr -dc 'A-Za-z0-9' </dev/urandom | head -c 10)
fi
old_string="'password' => '4Gr6RlAHPp',"
new_string="'password' => '$password',"
sed -i "s/$old_string/$new_string/g" "config/starter.php"

echo "Config Done!"

# run setup permission
if [ -z "$user" ]; then
    echo -n "Change user permission (Enter to skip): "
    read user
fi

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