rc-infosAPI [![Build Status](https://travis-ci.org/Azema/rc-infosAPI.png?branch=master)](https://travis-ci.org/Azema/rc-infosAPI)
===========

API pour les infos RC

Pour fonctionner, 
* Créer vous une base de données avec un utilisateur
* copier le fichier <code>config/autoload/local.php</code> et renseigner les informations de connexion à la base de données.
* Utiliser le fichier <code>sql/migrate/schema.txt</code> pour créer la structure de la base de données.
* positionnez vous dans le répertoire <code>public</code> et lancer la commande : <code>php -S localhost:8080</code>
* Ensuite, vous pourrez appeler l'URL [http://localhost:8080/api/clubs](http://localhost:8080/api/clubs) par exemple.
