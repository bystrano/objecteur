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

/**
 * Ajouter un logo à un objet SPIP
 * On peu passer directement un file Path ou un $_FILE[input_name] à $fichier
 *
 * @param mixed $objet
 * @param mixed $id_objet
 * @param mixed $fichier
 * @access public
 * @return mixed
 */
if (!function_exists('ajouter_logo')) {
    function ajouter_logo($objet, $id_objet, $fichier) {
        include_spip('action/editer_logo');
        // Version SPIP 3.1 de cette fonction:
        if (function_exists('logo_modifier'))
            return logo_modifier($objet, $id_objet, 'on', $fichier);

        include_spip('action/iconifier');
        $chercher_logo = charger_fonction('chercher_logo','inc');
        $ajouter_image = charger_fonction('spip_image_ajouter','action');

        $type = type_du_logo(id_table_objet($objet));
        $logo = $chercher_logo($id_objet, id_table_objet($objet));

        if ($logo)
            spip_unlink($logo[0]);

        // Dans le cas d'un tableau, on présume que c'est un $_FILES et on passe directement
        if (is_array($fichier))
            $err = $ajouter_image($type."on".$id_objet," ", $fichier, true);
        else
            // Sinon, on caviarde la fonction ajouter_image
            $err = $ajouter_image($type."on".$id_objet," ", array('tmp_name' => $fichier), true);

        if ($err)
            return $err;
        else
            return true;
    }
}