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
Ce plugin fournit des fonctions pour créer et `TODO`supprimer ces objets dans les fonctions d'administration des plugins et retrouver ensuite leurs identifiants dans du code php ou des squelettes.

Il peut aussi arriver que des utilisateurs du site suppriment des objets éditoriaux nécessaires au fonctionnement du site.
`TODO` Pour éviter ce genre de problèmes, on interdit aux non-webmestres de supprimer les objets éditoriaux persistants créés via l'API.

Créer des objets persistants
----------------------------

On peut créer des objets persistants avec la fonction `maj_objets_persistants` qui se trouve dans le fichier `inc/objets_persistants.php`.
Cette fonction reçoit deux paramètres, `$nom_meta`, qui est le nom de la meta qui sera utilisée pour enregistrer les identifiants, et `$objets`, qui est un tableau qui définit les objets éditoriaux persistants.

On peut par exemple créer une rubrique hors-menu qui contient une rubrique agenda de la façon suivante :

```php
include_spip('inc/objets_persistants');

maj_objets_persistants('mon_site_spip', array(
    'rubrique_hors_menu' => array(
        'objet' => 'rubrique',
        'titre' => "99. Hors-menu",
        'enfants' => array(
            'rubrique_agenda' => array(
                'objet' => 'rubrique',
                'titre' => 'Agenda',
            ),
        ),
    ),
));
```

Au premier appel de la fonction, les objets seront créés, et leurs identifants seront enregistrés dans la méta qu'on à passé en paramètre.
Si les objets existent déjà, l'appel à cette fonction n'aura aucun effet, on peut donc mettre ce code dans `mes_options.php`, mais la meilleure solution est d'appeler `maj_objets_persistants` dans les fonctions d'administration d'un plugin.

On pourra alors retouver les identifiants de ces objets dans du code php

```php
include_spip('inc/config');

$id_rub_hors_menu = lire_config('mon_site_spip/rubrique_hors_menu');
```

ou dans des squelettes :

```
<BOUCLE_rub_agenda(RUBRIQUES){id_rubrique=#CONFIG{mon_site_spip/rubrique_agenda}}>
  #TITRE
</BOUCLE_rub_agenda>
```
