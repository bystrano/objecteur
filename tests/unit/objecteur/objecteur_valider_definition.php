<?php
/**
 * Test unitaire de la fonction objecteur_valider_definition
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 13:32
 */

$test = 'objecteur_valider_definition';
$remonte = "../";
while (!is_dir($remonte."ecrire"))
    $remonte = "../$remonte";
require $remonte.'tests/test.inc';
find_in_path("../plugins/objecteur/inc/objecteur.php",'',true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f='objecteur_valider_definition')){
    find_in_path("inc/filtres.php",'',true);
    $f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_valider_definition());

// si le tableau $err est pas vide ca va pas
if ($err) {
    die ('<dl>' . join('', $err) . '</dl>');
}

echo "OK";


function essais_objecteur_valider_definition(){
		$essais = array (
  0 => 
  array (
    0 => 'La définition de l\'objet doit être un tableau !
\'hello\'',
    1 => 'hello',
  ),
  1 => 
  array (
    0 => 'La définition de l\'objet n\'as pas de clé \'options\'
array (
  \'objet\' => \'rubrique\',
)',
    1 => 
    array (
      'objet' => 'rubrique',
    ),
  ),
  2 => 
  array (
    0 => 'bloubliboulga n\'est pas une option valide
array (
  \'objet\' => \'article\',
  \'options\' => 
  array (
    \'bloubliboulga\' => \'au plus vite\',
  ),
)',
    1 => 
    array (
      'objet' => 'article',
      'options' => 
      array (
        'bloubliboulga' => 'au plus vite',
      ),
    ),
  ),
  3 => 
  array (
    0 => NULL,
    1 => 
    array (
      'objet' => 'article',
      'options' => 
      array (
        'titre' => 'un nouvel article',
        'nom' => 'nouvel_article',
        'lang' => 'fr',
        'id_trad' => 'hello',
      ),
    ),
  ),
);
		return $essais;
	}
















?>