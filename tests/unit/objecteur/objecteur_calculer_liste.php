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

        "Création de plusieurs objets" => array (
            array (
                array('objet' => 'rubrique'),
                array('objet' => 'article'),
            ),
            array (
                array('objet' => 'rubrique'),
                array('objet' => 'article'),
            ),
        ),

        "Enfants" => array (
            array (
                0 =>
                array (
                    'objet' => 'rubrique',
                    'options' =>
                    array (
                        'nom' => '__rubrique-0',
                    ),
                ),
                1 =>
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'id_rubrique' => '@__rubrique-0@',
                    ),
                ),
            ),
            array (
                array(
                    'objet' => 'rubrique',
                    'enfants' => array(
                        array(
                            'objet' => 'article'
                        ),
                    ),
                ),
            ),
        ),

        "petits enfants" => array (
            array (
                0 =>
                array (
                    'objet' => 'rubrique',
                    'options' =>
                    array (
                        'nom' => '__rubrique-1',
                    ),
                ),
                1 =>
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'id_rubrique' => '@__rubrique-1@',
                    ),
                ),
                2 =>
                array (
                    'objet' => 'rubrique',
                    'options' =>
                    array (
                        'nom' => 'sousrub',
                        'id_parent' => '@__rubrique-1@',
                    ),
                ),
                3 =>
                array (
                    'objet' => 'article',
                    'options' =>
                    array (
                        'id_rubrique' => '@sousrub@',
                    ),
                ),
            ),

            array (
                array(
                    'objet' => 'rubrique',
                    'enfants' => array(
                        array(
                            'objet' => 'article'
                        ),
                        array(
                            'objet' => 'rubrique',
                            'options' => array(
                                'nom' => 'sousrub',
                            ),
                            'enfants' => array(
                                array('objet' => 'article'),
                            ),
                        ),
                    ),
                ),
            ),
        ),

        "On remplace les clés id_parent" => array (
            array (
                array(
                    'objet' => 'article',
                    'options' => array(
                        'id_rubrique' => 2,
                    ),
                ),
            ),
            array (
                array(
                    'objet' => 'article',
                    'options' => array(
                        'id_parent' => 2,
                    ),
                ),
            ),
        ),

        "Documents liés à un objet" => array (
            array(
                array(
                    'objet' => 'article',
                    'options' => array(
                        'nom' => 'parent',
                        'id_rubrique' => 2,
                    ),
                ),
                array(
                    'objet' => 'document',
                    'options' => array(
                        'titre' => 'lorem',
                        'fichier' => 'http://lorempixel.com/300/200/',
                    ),
                    'parent_doc' => array(
                        'objet' => 'article',
                        'id_objet' => '@parent@',
                    ),
                ),
            ),
            array (
                array(
                    'objet' => 'article',
                    'options' => array(
                        'nom' => 'parent',
                        'id_parent' => 2,
                    ),
                    'documents' => array(
                        array(
                            'objet' => 'document',
                            'options' => array(
                                'titre' => 'lorem',
                                'fichier' => 'http://lorempixel.com/300/200/',
                            ),
                        ),
                    ),
                ),
            ),
        ),

        "Documents liés à un objet - format simplifié" => array (
            array(
                array(
                    'objet' => 'article',
                    'options' => array(
                        'nom' => 'parent',
                        'id_rubrique' => 2,
                    ),
                ),
                array(
                    'objet' => 'document',
                    'options' => array(
                        'fichier' => 'http://lorempixel.com/300/200/',
                    ),
                    'parent_doc' => array(
                        'objet' => 'article',
                        'id_objet' => '@parent@',
                    ),
                ),
                array(
                    'objet' => 'document',
                    'options' => array(
                        'fichier' => '/tmp/image.png',
                    ),
                    'parent_doc' => array(
                        'objet' => 'article',
                        'id_objet' => '@parent@',
                    ),
                ),
            ),
            array (
                array(
                    'objet' => 'article',
                    'options' => array(
                        'nom' => 'parent',
                        'id_parent' => 2,
                    ),
                    'documents' => array(
                        'http://lorempixel.com/300/200/',
                        '/tmp/image.png',
                    ),
                ),
            ),
        )
    );

    return $essais;
}