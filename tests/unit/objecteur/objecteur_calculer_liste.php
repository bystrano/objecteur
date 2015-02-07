<?php
/**
 * Test unitaire de la fonction objecteur_calculer_liste
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 15:24
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_calculer_liste';
$remonte = "../";
while (!is_dir($remonte."ecrire"))
    $remonte = "../$remonte";
require $remonte.'tests/test.inc';
find_in_path("../plugins/objecteur/inc/objecteur.php",'',true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f='objecteur_calculer_liste')){
    find_in_path("inc/filtres.php",'',true);
    $f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_calculer_liste());

// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_calculer_liste(){

    $essais = array (
        "Création d'objet simple" => array (
            array (
                array('objet' => 'rubrique'),
            ),
            array (
                array('objet' => 'rubrique'),
            ),
        ),
    );

    return $essais;
}