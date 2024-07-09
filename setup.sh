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
base_structure="$DIR/vendor/smpleader/dtm/src/core"
echo $base_structure;
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
        echo ""
        echo "Setup cli app done!"
        ;;
    "web")
        ## Step 8  Web App
        echo "Bạn đã chọn tùy chọn 2"
        ;;
    "plugin")
        ## Step 9 Theme
        echo "Bạn đã chọn tùy chọn 3"
        ;;
    "solution")
        ## Step 10 Solution
        echo "Bạn đã chọn tùy chọn 4"
        ;;
    "theme")
        ## Step 11 Solution
        echo "Bạn đã chọn tùy chọn 5"
        ;;
    *)
        ## Step 12 Continue ?
        echo "Invalid value. Please enter one of the following values: cli, web, plugin, solution, theme."
        continue;
        ;;
esac
done
## Step 13 Done