<?php
/**
 * API du plugin Objets Persistants
 *
 * @plugin     objets_persistants
 * @copyright  2014
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Créer ou modifier des objets persistants
 *
 * On prend les objets l'un après l'autre, et s'il y a déjà une méta
 * on ne fait rien. Sinon on cherche si un objet existant correspond à
 * la description, et si on ne trouve vraiment rien on crée un nouvel
 * objet persistant.
 *
 * @param String $nom_meta : Le nom de la meta dans laquelle seront
 *                           stocké les objets persistants
 * @param array $objets : Un tableau de définitions d'objets. Chaque
 *                        clé du tableau donne le nom d'un objet, et
 *                        la valeur associée doit être un tableau avec
 *                        au moins une clé 'objet'. Le reste des
 *                        valeurs de ce tableau définissent les
 *                        valeurs des champs de l'objet.
 * @param bool $forcer_maj : permet de forcer une mise à jour de
 *                           l'objet même s'il existe déjà dans les
 *                           métas.
 *
 * @exemple :
 *
maj_objets_persistants('meta_test', array(
    'rubrique_hors_menu' => array(
        'objet' => 'rubrique',
        'titre' => "99. Hors-menu",
    ),
    'rubrique_agenda' => array(
        'objet' => 'rubrique',
        'titre' => '98. Agenda',
    ),
));
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon.
 */
function maj_objets_persistants ($nom_meta, $objets, $forcer_maj=FALSE) {

    include_spip('inc/config');
    include_spip('base/abstract_sql');
    include_spip('action/editer_objet');

    foreach ($objets as $nom_objet => $objet) {

        if ( ! isset($objet['objet'])) {
            spip_log("objet persistant mal défini : $nom_objet", _LOG_ERREUR);
            return "erreur : $nom_objet n'a pas de clé 'objet'";
        }

        $type_objet = $objet['objet'];
        unset($objet['objet']);

        if ( ! $id_objet = lire_config($nom_meta . '/' . $nom_objet)) {

            /* S'il y a déjà un objet correspondant à la description
               on le prend plutôt que d'en créer un nouveau */
            $id_objet = sql_getfetsel(
                id_table_objet($type_objet),
                table_objet_sql($type_objet),
                array_map(function ($index, $element) {
                    return $index . '=' . sql_quote($element);
                }, array_keys($objet), $objet));

            /* Création d'un nouvel objet persistant */
            if ( ! $id_objet) {

                if (array_key_exists(id_parent_objet($type_objet), $objet)) {

                    $id_parent = $objet[id_parent_objet($type_objet)];
                    unset($objet[id_parent_objet($type_objet)]);
                }

                if (isset($objet['id_parent'])) {

                    /* On remplace une éventuelle clé 'id_parent' par
                       la clé le nom du champ id_parent du type
                       d'objet en question */
                    $id_parent = $objet['id_parent'];
                    unset($objet['id_parent']);
                }

                if ($id_parent) {
                    $id_objet = objet_inserer($type_objet, $id_parent);
                } else {
                    $id_objet = objet_inserer($type_objet);
                }

                if ($err = objet_modifier($type_objet, $id_objet, $objet)) {
                    return $err;
                }
            }

            maj_meta($nom_meta, $nom_objet, $id_objet);

        } else if ($forcer_maj) {
            /* Mise à jour forcée de l'objet persistant */
            if ($err = objet_modifier($type_objet, $id_objet, $objet)) {
                return $err;
            }
            maj_meta($nom_meta, $nom_objet, $id_objet);
        }

        /* Gestion des objets enfants */
        if (isset($objet['enfants'])) {

            $enfants = array_map(function ($el) {
                $el['id_parent'] = $id_objet;
                return $el;
            }, $objet['enfants']);

            if ($err = maj_objets_persistants($nom_meta, $enfants, $forcer_maj)) {
                return $err;
            }
        }
    }
}

/**
 * Mettre à jour une meta en tant que tableau
 *
 * On s'en sert pour mettre plein de choses dans une même meta, de
 * façon à ne pas trop polluer la DB.
 *
 * @param string
 *     Le nom de la meta
 * @param mixed
 *     La clé à mettre à jour
 * @param mixed
 *     La valeur de la meta
 */
function maj_meta ($nom_meta, $cle, $valeur) {

    include_spip('inc/meta');

    $config = lire_config($nom_meta);
    $config = $config ? $config : array();
    $config[$cle] = $valeur;

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