# bash script setup starter

# Getting started
Clone bash script
```
cd a_folder
git clone https://github.com/joomaio/starter.git
cd starter && git checkout to bash_script
```

Or download zip bash script from https://github.com/joomaio/starter/tree/bash_script

# Run bash script
```
sudo bash setup.sh -user user-name -fpm fpm-container-name -fpm_path folder_path_in_container
```
with
user-name: permission for starter work, ex: www-data
fpm-container-name: container name run php service. If empty, bash script will run without docker container
folder_path_in_container: is folder source run bash script in container.

When run bash script, you must enter database info:
```
Enter database host: 
Enter database username:
Enter database password:
Enter database name:
Enter database prefix:
```
