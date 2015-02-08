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
Sur un SPIP fraîchement installé, l'exemple ci-dessus retournerait :

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

La fonction `objecteur_effacer`
-------------------------------

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

Les définitions d'objets
------------------------

Une définition d'objet est un tableau qui définit un ou plusieurs objets éditoriaux à créer ou supprimer.

Un définition d'objet valide est un tableau avec une clé `objet`, dont la valeur est un nom d'objet éditorial valide.
Ce tableau doit aussi avoir une clé `options`, un tableau dont les clés sont soit `nom`, soit `id_parent`, soit des noms de champs de la table SQL correspondant à la clé `objet`.

### Les références par nom ###

L'option `nom` permet de définir un identifiant pour l'objet qui sera créé ou retourné.
Cet identifiant sera utilisé comme clé dans le tableau des `id_objets` retournés par la fonction `objecteur`.

On pourra aussi s'en servir pour utilier l'identifiant d'un objet à créer dans la définition d'un autre objet, en écrivant `@nom_objet@`.
On peut par exemple créer des liens de traduction entre des articles crées par l'objecteur comme ça :

```php
$objecteur(array(
    array(
        'objet' => 'article',
        'options' => array(
            'nom' => 'home_fr',
            'titre' => 'Accueil',
        ),
    ),
    array(
        'objet' => 'article',
        'options' => array(
            'id_trad' => '@home_fr@',
            'titre' => 'Home',
        ),
    ),
));
```

### Les hiérarchies d'objets ###

L'option `id_parent` permet de définir un objet parent.
On peux utiliser `id_parent` même si le champ SQL qui gère la parenté s'appelle autrement, il sera remplacé automatiquement par le bon nom de champ, comme `id_groupe` pour les mots-clés ou `id_rubrique` pour les articles.

Chaque définition d'objet peut aussi avoir une clé `enfants`, qui permet de définir une arborescence d'objets éditoriaux.
Sa valeur est soit une définition, soit une liste de définitions d'objet éditoriaux.
Si on a défini des options `id_parent` pour les objets éditoriaux enfants, ces options seront ignorées.
