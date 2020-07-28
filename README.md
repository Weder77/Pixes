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

# Some screenshots
Home page 
![homepage1](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/1.png)

Shopping Card
![shopping-card](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/17.png)

Buy & Bill & Comment
![account](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/18.png)
![bill](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/19.png)
![comment](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/20.png)



Admin Pannel
![dashboard](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/23.png)
![sells](https://github.com/Weder77/UF_WEB_B2/blob/master/screenshots/24.png)



