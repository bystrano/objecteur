<?php
/**
 * Test unitaire de la fonction objecteur_dedoublonner_liste
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 13:32
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_dedoublonner_liste';
$remonte = "../";
while (!is_dir($remonte."ecrire"))
    $remonte = "../$remonte";
require $remonte.'tests/test.inc';
find_in_path("../plugins/objecteur/inc/objecteur.php",'',true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f='objecteur_dedoublonner_liste')){
    find_in_path("inc/filtres.php",'',true);
    $f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_dedoublonner_liste());

// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_dedoublonner_liste(){
    $essais = array (

        "Les listes sans doublons sont retournées telles quelles" =>
        array (
            array (
                0 =>
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'titre' => 'un nouvel article',
                        'nom' => 'nouvel_article',
                    ),
                ),
                1 =>
                array (
                    'objet' => 'rubrique',
                    'options' =>
                    array (
                        'titre' => 'une rubrique',
                        'nom' => 'nouvelle_rubrique',
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'titre' => 'un nouvel article',
                        'nom' => 'nouvel_article',
                    ),
                ),
                array (
                    'objet' => 'rubrique',
                    'options' =>
                    array (
                        'titre' => 'une rubrique',
                        'nom' => 'nouvelle_rubrique',
                    ),
                ),
            ),
        ),

        "Les listes avec doublons sont retournées sans doublons" =>
        array (
            array (
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'titre' => 'un nouvel article',
                    ),
                ),
            ),

            array(
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'titre' => 'un nouvel article',
                    ),
                ),
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'titre' => 'un nouvel article',
                    ),
                ),
            ),
        ),

    );
    return $essais;
}
