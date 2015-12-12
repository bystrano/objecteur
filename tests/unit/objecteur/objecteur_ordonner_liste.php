<?php
/**
 * Test unitaire de la fonction objecteur_ordonner_liste
 * du fichier ../plugins/objecteur/inc/objecteur.php
 *
 * genere automatiquement par TestBuilder
 * le 2015-02-07 18:52
 */

header('Content-Type: text/html; charset=UTF-8');

$test = 'objecteur_ordonner_liste';
$remonte = '../';
while (!is_dir($remonte.'ecrire')) {
	$remonte = "../$remonte";
}
require $remonte.'tests/test.inc';
find_in_path('../plugins/objecteur/inc/objecteur.php', '', true);

// chercher la fonction si elle n'existe pas
if (!function_exists($f = 'objecteur_ordonner_liste')) {
	find_in_path('inc/filtres.php', '', true);
	$f = chercher_filtre($f);
}

//
// hop ! on y va
//
$err = tester_fun($f, essais_objecteur_ordonner_liste());

// si le tableau $err est pas vide ca va pas
if ($err) {
	die('<dl>' . join('', $err) . '</dl>');
}

echo 'OK';


function essais_objecteur_ordonner_liste() {
	$essais = array (
		'Les listes valides sont valides' =>
		array (
			array(
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
					),
				),
			),

			array(
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
					),
				),
			),
		),

		'Les listes invalides sont invalides' =>
		array (
			_T(
				'objecteur:erreur_liste_invalide',
				array(
					'err' => '
 - référence manquante : rubrique_agenda',)
			),

			array(
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
						'id_parent' => '@rubrique_agenda@',
					),
				),
			),
		),

		'Les listes dans le désordre sont réordonnées' =>
		array (
			array(
				array(
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_agenda',
					),
				),
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
						'id_parent' => '@rubrique_agenda@',
					),
				),
			),

			array(
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
						'id_parent' => '@rubrique_agenda@',
					),
				),
				array(
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_agenda',
					),
				),
			),
		),

		"On n'accepte pas les références circulaires" =>
		array (
			_T(
				'objecteur:erreur_liste_invalide',
				array(
					'err' => '
 - référence manquante : rubrique_agenda
 - référence manquante : rubrique_accueil',)
			),

			array(
				array (
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_accueil',
						'id_parent' => '@rubrique_agenda@',
					),
				),
				array(
					'objet' => 'rubrique',
					'options' => array(
						'nom' => 'rubrique_agenda',
						'id_parent' => '@rubrique_accueil@',
					),
				),
			),
		),
	);
	return $essais;
}
