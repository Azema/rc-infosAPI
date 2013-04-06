rc-infosAPI
===========

rc-infosAPI est une API qui servira les informations du monde des voitures radio-commandées,
comme les leagues, les clubs, les pistes, les pilotes recencés.

Cette API est de type REST et utilise le format hal+json comme sortie.

Elle est actuellement visible sur : [https://rcinfos.sourceslist.org/api/clubs](https://rcinfos.sourceslist.org/api/clubs)

Elle n'est pour le moment pas encore utilisable en production, mais elle donne un aperçu de ce que l'on peut faire avec.
Seul la lecture est actuellement pris en compte. Je n'ai pas encore géré l'écriture.

Elle fonctionne avec Zend Framework 1.12.

La base de données est gérée avec [Phigrate](https://github.com/Azema/Phigrate)

J'ai mis en place quelques données pour que l'API puisse servir des informations et pour faire joujou avec.

Prochaines étapes:
- [ ] Ajouter les tests de la BDD
- [ ] Ajouter les tests de l'API
- [ ] Ajouter les tests de la bibliothèque
- [ ] Ajouter les pistes des clubs
- [ ] Ajouter les pilotes
- [ ] Ajouter les championnats
- [ ] Ajouter les pages de documentation des ressources
- [ ] Ajouter l'authentification
- [ ] Encore pleins d'autres choses à faire
