<?php
/**
 * Options du plugin Objets Persistants au chargement
 *
 * @plugin     Objets Persistants
 * @copyright  2015
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 * @package    SPIP\Objets_persistants\Options
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