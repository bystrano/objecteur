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

On lui passe en paramètre une définition, ou une liste de définitions d'objets :

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
Les identifiants des objets qui n'ont pas défini d'option `nom` sont indexés par ordre de création.
Sur un SPIP fraîchement installé, l'exemple ci-dessus retournerait donc :

```php
array(
    'rubrique_hors_menu' => 1,
    'rubrique_agenda' => 2,
    0 => 1, // l'identifiant du mot-clé "humeur"
)
```

Au premier appel, la fonction crée les objets, puis elle ne fait que retourner les identifiants.
Si quelque chose s'est mal passé, ou que le tableau d'objets passé en paramètre est mal défini, la fonction retourne un message d'erreur.

Un deuxième paramètre permet de forcer la création de nouveaux objets, même s'il en existe déjà de similaires dans la base de données :

```php
$objecteur(
    array(
        'objet' => 'article',
        'options' => array(
            'titre' => 'nouvel article',
        ),
    ),
    TRUE);
```

Ainsi, chaque appel créera un nouvel article.

`objecteur_effacer`
-------------------

La fonction `objecteur_effacer` sert à supprimer des objets éditoriaux.
On l'utilise comme la fonction `objecteur` :

```php
$objecteur_effacer = charger_fonction('objecteur_effacer', 'inc');
$objecteur_effacer(
    array(
        'objet' => 'mot',
        'options' => array(
            'titre' => 'humeur',
        ),
    ),
);
```

La fonction se charge alors de supprimer les objets éditoriaux qui correspondent aux définitions passées en paramètre.
S'il y a plusieurs objets correspondants à la définition, on n'en efface qu'un seul.
