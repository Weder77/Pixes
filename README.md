# Présentation
Pixes est un site d'achat de jeux vidéos (comme instant gaming, mais en mieux!). Le principe est assez simple, un utilisateur peut acheter des jeux, il reçoit une facture avec son code etc ... Un espace admin est disponible avec un CRUD sur les jeux, commentaires, tags, plateformes etc ...

Il tourne sous Symfony 4, et nous utilisons SASS.

Les membres de l'équipe sont : 
- JEAN-CHARLES Dany
- GRELET Théo

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
