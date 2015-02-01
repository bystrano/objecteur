<?php
/**
 * Fonctions utiles au plugin Objecteur
 *
 * @plugin     Objecteur
 * @copyright  2015
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 * @package    SPIP\Objecteur\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Vrai si le plugin correspondant au préfix donné est activé, faux sinon
 *
 * @param string $prefix_plugin : Le préfixe d'un plugin
 *
 * @return bool : True si le plugin est activé, False sinon
 */
function plugin_est_actif ($prefix_plugin) {

    include_spip('inc/plugin');

    return array_key_exists(strtoupper($prefix_plugin),
                            liste_chemin_plugin_actifs());
}

/**
 * Mettre à jour une meta en tant que tableau
 *
 * On s'en sert pour mettre plein de choses dans une même meta, de
 * façon à ne pas trop polluer la DB. Si on omet le paramètre $valeur,
 * la clé est retirée de la méta
 *
 * @param string
 *     Le nom de la meta
 * @param mixed
 *     La clé à mettre à jour
 * @param mixed
 *     La valeur de la meta
 */
function maj_meta ($nom_meta, $cle, $valeur=NULL) {

    include_spip('inc/meta');

    $config = lire_config($nom_meta);
    $config = $config ? $config : array();

    if (is_null($valeur)) {
        if (isset($config[$cle])) { unset($config[$cle]); }
    } else {
        $config[$cle] = $valeur;
    }

    ecrire_meta($nom_meta, serialize($config));
}

/**
 * Retrouve la clé d'objet parent à partir du nom d'objet
 *
 * - article -> id_rubrique
 * - mot     -> id_groupe
 *
 * @api
 * @param string $type    : Nom de l'objet
 * @param string $serveur : Nom du connecteur
 *
 * @return string : Nom de la clé primaire
**/
function id_parent_objet ($type) {

    if (isset($GLOBALS['id_parents_objets'][$type])) {
        return $GLOBALS['id_parents_objets'][$type];
    } else {
        return '';
    }
}