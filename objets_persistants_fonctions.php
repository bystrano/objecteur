<?php
/**
 * Fonctions utiles au plugin Objets Persistants
 *
 * @plugin     Objets Persistants
 * @copyright  2015
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 * @package    SPIP\Objets_persistants\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


function plugin_est_actif ($prefixe_plugin) {

    include_spip('inc/plugin');
    return array_key_exists($prefixe_plugin, liste_chemin_plugin_actifs());
}