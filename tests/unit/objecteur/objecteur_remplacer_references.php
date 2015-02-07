<?php
/**
 * Test unitaire de la fonction objecteur_remplacer_references
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 17:56
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_remplacer_references';
$remonte = "../";
while (!is_dir($remonte."ecrire"))
    $remonte = "../$remonte";
require $remonte.'tests/test.inc';
find_in_path("../plugins/objecteur/inc/objecteur.php",'',true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f='objecteur_remplacer_references')){
    find_in_path("inc/filtres.php",'',true);
    $f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_remplacer_references());

// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_remplacer_references(){
    $essais = array (
        "On ne touche à rien s'il n'y a pas de références" =>
        array (
            array (
                'objet' => 'rubrique',
                'options' => array(
                    'titre' => 'Agenda',
                ),
            ),
            array (
                'objet' => 'rubrique',
                'options' => array(
                    'titre' => 'Agenda',
                ),
            ),
            array (
                'id_rub' => 1,
            ),
        ),
        "On remplace les références" =>
        array (
            array (
                'objet' => 'rubrique',
                'options' => array(
                    'titre' => 'Agenda',
                    'id_parent' => 42,
                ),
            ),
            array (
                'objet' => 'rubrique',
                'options' => array(
                    'titre' => 'Agenda',
                    'id_parent' => '@rub_hors_menu@',
                ),
            ),
            array (
                'rub_hors_menu' => 42,
            ),
        ),
    );
    return $essais;
}