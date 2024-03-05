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
sudo bash setup.sh -user user-name -fpm fpm-container-name -web_root_path web_path -document_root document_root_path
```
with
user-name: permission for starter work, ex: www-data
fpm-container-name: container name run php service. If empty, bash script will run without docker container
web_root_path: Absolute path to the app in the container for the web service. If you don't have a web container, you can ignore this information
document_root_path: absolute path to app.The default will be the starter folder contained in the bash run folder.

When run bash script, you must enter database info:
```
Enter database host: 
Enter database username:
Enter database password:
Enter database name:
Enter database prefix:
```

starter info:
```
Enter access key (default is random string): 
Enter username starter (default is starter): 
Enter password starter (default is random string): 
```
