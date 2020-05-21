# Install 
```bash
git clone https://github.com/Weder77/UF_WEB_B2
composer install
npm install
```

#### Modify database connection info in the .env
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### Install fixtures
```bash
php bin/console doctrine:fixtures:load 
```


# Start SASS
```bash
npm run watch
```

# Start the website
```bash
php -S localhost:8000 -t public
```
#### The website is available at `localhost:8000/`
