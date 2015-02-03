Objecteur
=========

_Objecteur_ est un plugin pour le CMS SPIP, il fournit une API pour créer des objets éditoriaux en masse.
Pour l'instant il ne fait rien en soi, mais est conçu pour être utilisé par d'autres plugins.

La fonction `objecteur`
-----------------------

La fonction `objecteur` sert à créer des objets éditoriaux.
On charge la fonction via le mécanisme habituel de SPIP :

```php
$objecteur = charger_fonction('objecteur', 'inc');
```

Et on lui passe en paramètre une liste de définitions d'objets :

```php
$objecteur(array(
    array(
        'objet' => 'rubrique',
        'options' => array(
            'nom' => 'rubrique_hors_menu',
            'titre' => "99. Hors-menu",
        ),
        'enfants' =>  array(
            array(
                'objet' => 'rubrique',
                'options' => array(
                    'nom' => 'rubrique_agenda',
                    'titre' => 'Agenda',
                ),
            ),
        ),
    ),
    array(
        'objet' => 'mot',
        'options' => array(
            'titre' => 'humeur',
        ),
    ),
));
```

S'il y a déjà des objets éditoriaux qui correspondent aux définitions, on ne crée pas de nouveaux objets.
Dans tous les cas, la fonction retourne un tableau d'identifiants des objets en question, indexés par leurs noms.
On ne retourne pas les identifiants des objets qui n'ont pas définis d'option `nom`.
Sur un SPIP fraîchement installé, l'exemple ci-dessus retournerait donc :

```php
array(
    'rubrique_hors_menu' => 1,
    'rubrique_agenda' => 2,
)
```

Au premier appel, la fonction crée les objets, puis elle ne fait que retourner les identifiants.
Si quelque chose s'est mal passé, ou que le tableau d'objets passé en paramètre est mal définit, la fonction retourne un message d'erreur.

`objecteur_effacer`
-------------------

La fonction `objecteur_effacer` sert à supprimer des objets éditoriaux.
On l'utilise comme la fonction `objecteur` :

```php
$objecteur_effacer = charger_fonction('objecteur_effacer', 'inc');
$objecteur_effacer(array(
    array(
        'objet' => 'mot',
        'options' => array(
            'titre' => 'humeur',
        ),
    ),
));
```

La fonction se charge alors de supprimer les objets éditoriaux qui correspondent aux définitions passées en paramètre.
