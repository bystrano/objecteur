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

$objets_temporaires = array(
    array (
        'objet' => 'rubrique',
        'options' => array(
            'nom' => 'rubrique_accueil_1',
            'titre' => 'Accueil',
        ),
    ),

    array (
        'objet' => 'rubrique',
        'options' => array(
            'nom' => 'rubrique_bxl_3',
            'titre' => 'Région BXL',
            'id_parent' => '@rubrique_agenda_3@',
        ),
    ),
    array(
        'objet' => 'rubrique',
        'options' => array(
            'nom' => 'rubrique_agenda_3',
            'titre' => "Calendrier",
        ),
    ),

);

$ids_objets_temporaires = $objecteur($objets_temporaires);

if (isset($ids) and is_string($ids)) {
    die("Impossible de créer les objets temporaires $ids");
}


//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_effacer_resoudre_references());

// On efface les objets qui ont été créés pour les tests
$objecteur_effacer = charger_fonction('objecteur_effacer', 'inc');

if ($erreur = $objecteur_effacer($objets_temporaires)) {
    echo "$erreur<br>";
}


// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_effacer_resoudre_references(){

    global $ids_objets_temporaires;

    $essais = array (
        "Les listes valides sont valides" =>
        array (
            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil_1',
                        'titre' => 'Accueil',
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil_1',
                        'titre' => 'Accueil',
                    ),
                ),
            ),
        ),

        "Les listes invalides sont invalides" =>
        array (
            _T('objecteur:erreur_liste_invalide',
               array(
                   'err' => '
 - référence manquante : rubrique_agenda',)),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil_2',
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
                        'nom' => 'rubrique_bxl_3',
                        'id_parent' => $ids_objets_temporaires['rubrique_agenda_3'],
                        'titre' => 'Région BXL',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda_3',
                        'titre' => "Calendrier",
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_bxl_3',
                        'titre' => 'Région BXL',
                        'id_parent' => '@rubrique_agenda_3@',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda_3',
                        'titre' => "Calendrier",
                    ),
                ),
            ),
        ),

        "On n'accepte pas les références circulaires" =>
        array (
            _T('objecteur:erreur_liste_invalide',
               array(
                   'err' => '
 - référence manquante : rubrique_agenda_4
 - référence manquante : rubrique_accueil_4',)),

            array(
                array (
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_accueil_4',
                        'titre' => 'Accueil',
                        'id_parent' => '@rubrique_agenda_4@',
                    ),
                ),
                array(
                    'objet' => 'rubrique',
                    'options' => array(
                        'nom' => 'rubrique_agenda_4',
                        'titre' => "Calendrier",
                        'id_parent' => '@rubrique_accueil_4@',
                    ),
                ),
            ),
        ),

    );

    return $essais;
}