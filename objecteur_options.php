<?php
/**
 * Options du plugin Objecteur au chargement
 *
 * @plugin     Objecteur
 * @copyright  2015
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 * @package    SPIP\Objecteur\Options
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/* Un tableau donnant les clés d'objets parents pour chaque objet
   éditorial. Ce tableau est utilisé par la fonction id_parent_objet,
   et permet de définir comment seront créés les hiérarchies d'objets
   persistants. On peut redéfinir cette valeur pour y ajouter des
   objets éditoriaux. */
$GLOBALS['id_parents_objets'] = array(
    'article'   => 'id_rubrique',
    'breve'     => 'id_rubrique',
    'forum'     => 'id_parent',
    'mot'       => 'id_groupe',
    'petition'  => 'id_article',
    'rubrique'  => 'id_parent',
    'signature' => 'id_petition',
    'syndic'    => 'id_rubrique',
    'site'      => 'id_rubrique',
);

include_spip('objecteur_fonctions');

if (plugin_est_actif('gma')) {
    $GLOBALS['id_parents_objets']['groupe_mots'] = 'id_parent';
}