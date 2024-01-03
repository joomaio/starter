# starter
A powerful tool to start your campaign

## Getting started

Clone
```
cd a_folder
git clone https://github.com/joomaio/starter.git
```

Composer install
```
cd starter
composer install
```

Copy and rename folder config.sample into config
Edit file config/database.php to connect to the database

## With Webapp
Required: 
Must have a config/startup.php and data in starter
```
return [ 
    'starter' => [
        'access_key' => 'OPQzxeyQpU',
        'username' => 'starter',
        'password' => '4Gr6RlAHPp',
    ],
];
```

Access the link https://your_domain/starter?access_key={access_key}
With {access_key} taken from file config/starter starter => access_key

## With CLI
Run install solution
```
php spt install solution-code
```

Example install solution
```
php spt install pnote
```

Solution code exist:
pnote
psol
pubsol (Todo)
shopsol(Todo)

Run install data minium
```
php spt data-minium
```

Uninstall solution
```
php spt uninstall solution-code
```
