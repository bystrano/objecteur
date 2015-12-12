<?php
/**
 * Test unitaire de la fonction objecteur_valider_liste
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 13:32
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_valider_liste';
$remonte = '../';
while (!is_dir($remonte.'ecrire')) {
	$remonte = "../$remonte";
}
require $remonte.'tests/test.inc';
find_in_path('../plugins/objecteur/inc/objecteur.php', '', true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f = 'objecteur_valider_liste')) {
	find_in_path('inc/filtres.php', '', true);
	$f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_valider_liste());

// si le tableau $err est pas vide ca va pas
if ($err) {
	die('<dl>' . join('', $err) . '</dl>');
}

echo 'OK';


function essais_objecteur_valider_liste() {
	$essais = array (

		'Les listes valides sont valides' =>
		array (
			null,
			array(
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
				array (
					'objet' => 'rubrique',
					'options' =>
					array (
						'titre' => 'une rubrique',
						'nom' => 'nouvelle_rubrique',
						'lang' => 'fr',
					),
				),
			),
		),

		'On ne peut pas donner plusieurs fois le même nom' =>
		array (
			_T(
				'objecteur:erreur_liste_invalide',
				array(
					'err' => _T(
						'objecteur:erreur_doublon_nom',
						array('nom' => 'nouvel_article')
					)
				)
			),

			array(
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
				array (
					'objet' => 'article',
					'options' =>
					array (
						'titre' => 'un autre article avec le même nom',
						'nom' => 'nouvel_article',
						'lang' => 'fr',
						'statut' => 'publie',
					),
				),
			),
		),

		'On peut avoir plusieurs objets sans nom' =>
		array (
			null,
			array(
				array (
					'objet' => 'article',
					'options' =>
					array (
						'titre' => 'un article sans nom',
					),
				),
				array (
					'objet' => 'article',
					'options' =>
					array (
						'titre' => 'un autre article sans nom',
					),
				),
			),
		),


	);
	return $essais;
}
