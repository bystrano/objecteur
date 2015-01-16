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
 * On prend les objets l'un après l'autre, et si une méta existe déjà
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
objets_persistants_modifier('meta_test', array(
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
function objets_persistants_modifier ($nom_meta, $objets, $forcer_maj=FALSE) {

    include_spip('inc/config');
    include_spip('base/abstract_sql');
    include_spip('action/editer_objet');

    foreach ($objets as $nom_objet => $objet) {

        if ( ! isset($objet['objet'])) {
            spip_log("objet persistant mal défini : $nom_objet");
            return "erreur : $nom_objet n'a pas de clé 'objet'";
        }

        $type_objet = $objet['objet'];
        unset($objet['objet']);

        if ( ! $id_objet = lire_config($nom_meta . '/' . $nom_objet)) {

            /* S'il y a déjà un objet correspondant à la description
               on le prend */
            $id_objet = sql_getfetsel(
                id_table_objet($type_objet),
                table_objet_sql($type_objet),
                array_map(function ($index, $element) {
                    return $index . '=' . sql_quote($element);
                }, array_keys($objet), $objet));

            if ( ! $id_objet) {
                $id_objet = objet_inserer($type_objet);
                objet_modifier($type_objet, $id_objet, $objet);
            }

            maj_meta($nom_meta, $nom_objet, $id_objet);

        } else if ($forcer_maj) {
            if ($err = objet_modifier($type_objet, $id_objet, $objet)) {
                return $err;
            }
            maj_meta($nom_meta, $nom_objet, $id_objet);
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
