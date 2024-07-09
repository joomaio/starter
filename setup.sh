#!/bin/bash
DIR="$(dirname "$(realpath "$0")")"
# Step 1 Check Php version
php_version=$(php -v 2>&1)
if [ $? -ne 0 ]; then
    echo "PHP is not installed!"
    exit 1
fi

current_version=$(echo "$php_version" | grep -oP '^PHP \K[^\s]+')
required_version="8.2"
if [ "$(printf '%s\n' "$required_version" "$current_version" | sort -V | head -n1)" = "$required_version" ]; then
    echo "PHP ready!"
else
    echo "PHP version is < 8.2!"
fi

## Step 2 check composer
composer_version=$(composer --version 2>&1)
if [ $? -ne 0 ]; then
    echo "Composer is not installed!"
    exit 1
fi

current_version=$(echo "$composer_version" | grep -oP 'Composer version \K[^\s]+')
required_version="2.0"
if [ "$(printf '%s\n' "$required_version" "$current_version" | sort -V | head -n1)" = "$required_version" ]; then 
    echo "Composer ready!"
else
    echo "Composer version is < 2.0"
fi

## Step 3 check git
git_version=$(git --version 2>&1)

if [ $? -ne 0 ]; then
    echo "Git is not installed!"
    exit 1
fi

current_version=$(echo "$git_version" | grep -oP 'git version \K[^\s]+')

required_version="2"
if [ "$(printf '%s\n' "$required_version" "$current_version" | sort -V | head -n1)" = "$required_version" ]; then 
    echo "Git ready!"
    # next to step 4
else
    echo "Git version is < 2"
fi

## Step 4 composer install
echo "Start composer install:"
composer require smpleader/spt smpleader/dtm
if [ $? -eq 0 ]; then
    echo "Composer install done!"
else
    echo "An error occurred while installing the packages."
    exit 1
fi

## Step 5 Base Structure Setup
echo "Start generate basic structure"
base_structure="$DIR/vendor/smpleader/dtm/boilerplates/base_structure"
if [ ! -d "$base_structure" ]; then
    echo "Error: Basic structure not found."
    exit 1
fi

cp -r "$base_structure"/* "$DIR"
if [ ! $? -eq 0 ]; then
    echo "Error: Basic structure cannot copy!"
    exit 1
fi

echo "Basic structure setup done!"
## Step 6  Which App
while true; do
    echo "Which application do you want to deploy?"
    echo "1. cli"
    echo "2. web"
    echo "3. plugin"
    echo "4. solution"
    echo "5. theme"
    read app

case "$app" in
    "cli")
        ## Step 7  Cli App
        cli_path="$DIR/vendor/smpleader/dtm/boilerplates/cli"
        cp -r "$cli_path" "$DIR"
        if [ ! $? -eq 0 ]; then
            echo "Error: Setup cli app failed!"
            exit 1
        fi
        echo "Setup cli app done!"
        ;;
    "web")
        ## Step 8  Web App
        web_path="$DIR/vendor/smpleader/dtm/boilerplates/web_public"
        cp -r "$web_path"/* "$DIR"
        if [ ! $? -eq 0 ]; then
            echo "Error: Setup web app failed!"
            exit 1
        fi
        # generate solution
        echo "Setup web app done!"
        ;;
    "plugin")
        ## Step 9 Plugin
        plugin_path="$DIR/vendor/smpleader/dtm/boilerplates/plugin"
        cp -r "$plugin_path"/* "$DIR"
        if [ ! $? -eq 0 ]; then
            echo "Error: Setup cli app failed!"
            exit 1
        fi
        echo "Setup web app done!"
        ;;
    "solution")
        ## Step 10 Solution
        solution_path="$DIR/vendor/smpleader/dtm/boilerplates/solution"
        cp -r "$solution_path"/* "$DIR/php_modules"
        if [ ! $? -eq 0 ]; then
            echo "Error: Setup cli app failed!"
            exit 1
        fi
        echo "Setup web app done!"
        ;;
    "theme")
        ## Step 11 Theme
        solution_path="$DIR/vendor/smpleader/dtm/boilerplates/solution"
        cp -r "$solution_path"/* "$DIR/php_modules"
        if [ ! $? -eq 0 ]; then
            echo "Error: Setup cli app failed!"
            exit 1
        fi
        echo "Setup web app done!"
        ;;
    *)
        echo "Invalid value. Please enter one of the following values: cli, web, plugin, solution, theme."
        continue
        ;;
esac
    ## Step 12 Continue ?
    echo -n "Do you want to continue? y/N: "
    read answer
    answer_lower=$(echo "$answer" | tr '[:upper:]' '[:lower:]')
    if [ "$answer_lower" == "y" ] || [ "$answer_lower" == "yes" ]; then
        continue
    fi
    break
done
## Step 13 Done
echo "Setup done!"