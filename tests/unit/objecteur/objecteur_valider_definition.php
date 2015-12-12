<?php
/**
 * Test unitaire de la fonction objecteur_valider_definition
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 13:32
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_valider_definition';
$remonte = '../';
while (!is_dir($remonte.'ecrire')) {
	$remonte = "../$remonte";
}
require $remonte.'tests/test.inc';
find_in_path('../plugins/objecteur/inc/objecteur.php', '', true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f = 'objecteur_valider_definition')) {
	find_in_path('inc/filtres.php', '', true);
	$f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_valider_definition());

// si le tableau $err est pas vide ca va pas
if ($err) {
	die('<dl>' . join('', $err) . '</dl>');
}

echo 'OK';


function essais_objecteur_valider_definition() {
	$essais = array (
		'Les strings ne sont pas acceptés' =>
		array (
			_T(
				'objecteur:erreur_definition_pas_tableau',
				array('objet' => '\'hello\'')
			),
			'hello',
		),

		'La clé options est obligatoire' =>
		array (
			_T(
				'objecteur:erreur_definition_pas_cle_options',
				array('objet' => 'array (
  \'objet\' => \'rubrique\',
)')
			),
			array (
				'objet' => 'rubrique',
			),
		),

		"Les options ne peuvent pas être n'importe quoi" =>
		array (
			_T(
				'objecteur:erreur_definition_cle_invalide',
				array(
					'cle' => 'bloubliboulga',
					'objet' => 'array (
  \'objet\' => \'article\',
  \'options\' => 
  array (
    \'bloubliboulga\' => \'au plus vite\',
  ),
)')
			),
		array (
			'objet' => 'article',
			'options' =>
				array (
					'bloubliboulga' => 'au plus vite',
				),
			),
		),

		'les objets valides sont valides' =>
		array (
			null,
			array (
				'objet' => 'article',
				'options' =>
				array (
					'titre' => 'un nouvel article',
					'nom' => 'nouvel_article',
					'lang' => 'fr',
					'statut' => 'publie',
				),
			),
		),

		"la clé 'fichier' est obligatoire pour les documents" =>
		array (
			_T('objecteur:erreur_option_manquante', array(
				'option' => 'fichier',
				'objet' => "array (
  'objet' => 'document',
  'options' => 
  array (
    'titre' => 'un nouveau document',
  ),
)",
			)),
			array (
				'objet' => 'document',
				'options' =>
				array (
					'titre' => 'un nouveau document',
				),
			),
		),

		"la clé 'fichier' est obligatoire pour les documents 2" =>
		array (
			null,
			array (
				'objet' => 'document',
				'options' =>
				array (
					'titre' => 'un nouveau document',
					'fichier' => '/chemin/vers/fichier',
				),
			),
		),

	);
	return $essais;
}
