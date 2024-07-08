#!/bin/bash

## Step 1 Check Php version
php_version=$(php -v 2>&1)
if [ $? -ne 0 ]; then
    echo "PHP is not installed!"
    exit 1
fi

current_version=$(echo "$php_version" | grep -oP '^PHP \K[^\s]+')
required_version="8.2"
if [ "$(printf '%s\n' "$required_version" "$current_version" | sort -V | head -n1)" = "$required_version" ]; then 
    # Next to step 2
else
    echo "PHP version is < 8.2"
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
    # next to step 3
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
    echo "Environment is ready"
    # next to step 4
else
    echo "Git version is < 2"
fi

## Step 4 composer install
composer require smpleader/spt smpleader/dtm

if [ $? -eq 0 ]; then
    echo "Composer install done!"
else
    echo "An error occurred while installing the packages."
    exit 1
fi

## Step 5 Base Structure Setup

## Step 6  Which app

## Step 7  Cli App

## Step 8  Web App

## Step 9 Theme

## Step 10 Solution

## Step 11 Solution

## Step 12 Continue ?

## Step 13 Done
echo "Septup done!"

