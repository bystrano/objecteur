Objets Persistants
==================

_Objets Persistants_ est un plugin pour le CMS SPIP, il fournit une API pour gérer des objets éditoriaux nécessaires au bon fonctionnement du site.
Il ne fait rien en soi, mais est conçu pour être utilisé par d'autres plugins.

Ce plugin répond à un besoin courant lors du développement de sites SPIP.
On écrit des squelettes et du code php qui dépendent d'objets éditoriaux.
Ça peut être une rubrique "Hors-menu", ou un groupe de mots-clés qui caractérise un objet éditorial.
On a alors tendance à créer l'objet dans l'espace privé de SPIP, puis à utiliser son `id` dans le code, ce qui pose plusieurs problèmes.

Le code devient dépendant de la base de données, et ça complique le développement.
On ne peux pas repartir de zéro sans recréer ces objets à la main et mettre à jour les identifiants dans le code.
`TODO` Ce plugin fournit des fonctions pour créer ces objets dans les fonctions d'administration des plugins et retrouver ensuite leurs identifiants.

Il peut aussi arriver que des utilisateurs du site suppriment des objets éditoriaux nécessaires au fonctionnement du site.
`TODO` Pour éviter ce genre de problèmes, on interdit aux non-webmestres de supprimer les objets éditoriaux persistants créés via l'API.

Créer des objets persistants
----------------------------

On peut créer des objets persistants avec la fonction `objets_persistants_creer` qui se trouve dans le fichier `inc/objets_persistants.php`.
Cette fonction reçoit deux paramètres, `$nom_meta`, qui est le nom de la meta qui sera utilisée pour enregistrer les identifiants, et `$objets`, qui est un tableau qui définit les objets éditoriaux persistants.
