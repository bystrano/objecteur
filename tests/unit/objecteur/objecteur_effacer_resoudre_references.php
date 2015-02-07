<?php
/**
 * Test unitaire de la fonction objecteur_effacer_resoudre_references
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 15:24
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_effacer_resoudre_references';
$remonte = "../";
while (!is_dir($remonte."ecrire"))
    $remonte = "../$remonte";
require $remonte.'tests/test.inc';
find_in_path("../plugins/objecteur/inc/objecteur.php",'',true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f='objecteur_effacer_resoudre_references')){
    find_in_path("inc/filtres.php",'',true);
    $f = chercher_filtre($f);
}

// On doit préparer des objets en base pour chaque test, sinon la
// fonction ne trouvera jamais rien
$objecteur = charger_fonction('objecteur', 'inc');
$ids_objets = array();
foreach (essais_objecteur_effacer_resoudre_references() as $essai) {

    $ids = $objecteur($essai[1]);

    if ( ! is_string($ids)) {
        $ids_objets = array_merge($ids_objets, $ids);
    }
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_effacer_resoudre_references());

// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_effacer_resoudre_references(){

    global $ids_objets;

    $essais = array (
        "Les listes valides sont valides" =>
        array (
            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'titre' => 'Accueil',
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'titre' => 'Accueil',
                    ),
                ),
            ),
        ),

        "Les listes invalides sont invalides" =>
        array (
            'La liste n\'est pas valide : 
 - référence manquante : rubrique_agenda',
            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'titre' => 'Accueil',
                        'id_parent' => '@rubrique_agenda@',
                    ),
                ),
            ),
        ),

        "On sait retrouver des références dans la base de données" =>
        array (
            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'id_parent' => $ids_objets['rubrique_agenda'],
                        'titre' => 'Accueil',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda',
                        'titre' => "Calendrier",
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'titre' => 'Accueil',
                        'id_parent' => '@rubrique_agenda@',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda',
                        'titre' => "Calendrier",
                    ),
                ),
            ),
        ),

        "On n'accepte pas les références circulaires" =>
        array (
            'La liste n\'est pas valide : 
 - référence manquante : rubrique_agenda
 - référence manquante : rubrique_accueil',

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil',
                        'titre' => 'Accueil',
                        'id_parent' => '@rubrique_agenda@',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda',
                        'titre' => "Calendrier",
                        'id_parent' => '@rubrique_accueil@',
                    ),
                ),
            ),
        ),

    );

    return $essais;
}