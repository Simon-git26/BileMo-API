# BileMo-API
Créer une API pour BileMo afin de développer leur vitrine de produits.


## Description du projet

Principales fonctionnalités demandées par le client:

  * consulter la liste des produits BileMo ;
  * consulter les détails d’un produit BileMo ;
  * consulter la liste des utilisateurs inscrits liés à un client sur le site web ;
  * consulter le détail d’un utilisateur inscrit lié à un client ;
  * ajouter un nouvel utilisateur lié à un client ;
  * supprimer un utilisateur ajouté par un client.
  

## Contraintes

Les clients de l’API doivent être authentifiés via Oauth ou JWT.
Pour le projet, j'ai choisi d'utiliser JWT


## Prérequis

Technologies: 
- PHP
- Composer
- Symfony (Version 5 recommandée car mieux documentée pour l'instant)

## Installation

  * Créer/Initialiser le projet Symfony dans le répertoire de votre serveur:
      * Wamp : Répertoire 'www'.
      * Mamp : Répertoire 'htdocs'.

  * Pour initialiser un projet Symfony, voir documentation Symfony ou 
  * [Initialisez votre projet Symfony, Openclassrooms](https://openclassrooms.com/fr/courses/7709361-construisez-une-api-rest-avec-symfony/7795085-initialisez-votre-projet-symfony)
      
  * Créez/Renommer le fichier '.env' en '.env.local puis configurer dans le '.env.local' la ligne DATABASE_URL de connexion à votre base.
  
  * Ensuite placez-vous dans votre répertoire et installer Composer (voir lien en bas du readme)

  * Installer le maker-bundle
  ```bash
  composer require symfony/maker-bundle --dev
  ```

  * Ajouter ORM Doctrine
  ```bash
  composer require orm
  ```

  * Une fois maker-bundle et Doctrine ORM ajoutés, vous pouvez créer votre premiere entity avec 
  ```bash
  php bin/console make:entity
  ```
  
    
* Création / Configuration de la base de données:

    ```bash
    php bin/console doctrine:database:create
    ```
    * A ce niveau, aller voir sur votre PhpMyAdmin ou DBeaver si votre base est présente. Si ce n'est pas le cas, verifier votre connexion de la const DATABASE_URL


    * Dire à Doctrine de transformer l’entité en véritable table
    ```bash
    php bin/console doctrine:schema:update --force
    ```


    * Pour faire des migrations: 
    ```bash
    php bin/console make:migration
    ```

    ```bash
    php bin/console doctrine:migrations:migrate
    ```
    
* Création des Fixtures pour des jeux de données rapide en base
    ```bash
    composer require orm-fixtures --dev
    ```

    Une fois le code de vos fixtures ecrit, les chargés :


    ```bash
    php bin/console doctrine:fixtures:load 
    ```

    

## Authentification avec JWT
* Installer le composant Security
    ```bash
    composer require security
    ```
* Créer un user 
    ```bash
    php bin/console make:user
    ```
    

* Génération des clés d'authentification JWT:

* Sur un terminal Git Bash

    ```bash
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 
    ```
    
    ```bash
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    ```
    
    * Renseignez et confirmez la pass phrase 'pass phrase de votre choix'  
    
    
* Démarrage du serveur de symfony:

    ```bash
    symfony server:start
    ```


## Liste des CURL pouvant être exécutés avec mon projet

# Login Token 
* Authentification:

    URL : http://127.0.0.1:8000/api/login_check
    Method : POST
    Body : json → {"username": "user@bilemo.com","password": "password"}

    * Il est aussi possible de s'authentifier avec différents identifiants comme pour un admin:
      Body : json → {"username": "admin@bilemo.com","password": "password"}

# Telehpone  

* Réupérer la liste des téléphones:

    URL : http://127.0.0.1:8000/api/phones
    Method : GET
    Token : Header → Authorization → bearer $TOKEN
    Body : json → {"page": 1,"limit": 5}


* Réupérer un téléphone:

    URL : http://127.0.0.1:8000/api/phones/96
    Method : GET
    Token : Header → Authorization → bearer $TOKEN

* Supprimer un téléphone:

    URL : http://127.0.0.1:8000/api/phones/96
    Method : DELETE
    Token : Header → Authorization → bearer $TOKEN

* Créer un téléphone:

    URL : http://127.0.0.1:8000/api/phone
    Method : POST
    Token : Header → Authorization → bearer $TOKEN
    Body : json → {"brand": "Test","model": "X9","color": "vert","price": 250,"description" : "Super resistant","storage": 20}

* Modifier un téléphone:

    URL : http://127.0.0.1:8000/api/phones/96
    Method : PUT
    Token : Header → Authorization → bearer $TOKEN
    Body : json → {"brand": "BlackBerry modifié","model": "G6", "color": "violet","price": 300,"description" : "Super telephone","storage": 264}


# Client

* Récuperer la liste des clients et leur user associés:

    URL : http://127.0.0.1:8000/api/clients
    Method : GET
    Token : Header → Authorization → bearer $TOKEN


# User

* Réupérer la liste des utilisateurs:

    URL : http://127.0.0.1:8000/api/users
    Method : GET
    Token : Header → Authorization → bearer $TOKEN
    Body : json → {"page": 3,"limit": 3}


* Réupérer un utilisateur:

    URL : http://127.0.0.1:8000/api/users/24
    Method : GET
    Token : Header → Authorization → bearer $TOKEN
    
* Réupérer la liste des users sur un client donné:

    URL : http://127.0.0.1:8000/api/clients/29/users
    Method : GET
    Token : Header → Authorization → bearer $TOKEN
    

* Ajouter un utilisateur à un client donné:

    URL : http://127.0.0.1:8000/api/user
    Method : POST
    Token : Header → Authorization → bearer $TOKEN
    Body : json → {"name": "user 150","id": 29}
    
    
* Supprimer un utilisateur:

    URL : http://127.0.0.1:8000/api/users/27
    Method : DELETE
    Token : Header → Authorization → bearer $TOKEN

## Outils utilisés

  * [Symfony](https://symfony.com/)
  * [Composer](https://getcomposer.org/)
  * [Postman](https://www.getpostman.com/)
  
  