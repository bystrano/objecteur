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
 * @param array $objets : Un tableau de définitions d'objets.
 * @param bool $forcer_maj : permet de forcer une mise à jour de
 *                           l'objet même s'il existe déjà dans les
 *                           métas.
 *
 * @exemple :
 *
maj_objets_persistants('mon_site_spip', array(
    array(
        'objet' => 'rubrique',
        'options' => array(
            'nom' => 'rubrique_hors_menu',
            'titre' => "99. Hors-menu",
        ),
        'enfants' =>  array(
            array(
                'objet' => 'rubrique',
                'options' => array(
                    'nom' => 'rubrique_agenda',
                    'titre' => 'Agenda',
                ),
            ),
        ),
    ),
));
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon.
 */
function maj_objets_persistants ($nom_meta, $objets, $forcer_maj=FALSE) {

    include_spip('inc/config');
    include_spip('action/editer_objet');

    foreach ($objets as $objet) {

        if ( ! objet_valide($objet)) {

            spip_log("objet persistant mal défini : " .
                     var_export($objet, TRUE), _LOG_ERREUR);

            return "erreur : objet " . var_export($objet, TRUE) .
                " non valide";
        }

        $type_objet = objet_type($objet['objet']);
        $options = $objet['options'];
        $nom = $options['nom'];
        unset($options['nom']);

        if ( ! $id_objet = lire_config("$nom_meta/$nom")) {

            $id_objet = objet_persistant_creer($objet);
            /* On enregistre les identifiants de l'objet… */
            maj_meta($nom_meta, $nom, $id_objet);

        } else if ($forcer_maj) {
            /* Mise à jour forcée de l'objet persistant */
            if ($err = objet_modifier($type_objet, $id_objet, $options)) {
                return $err;
            }
        }

        /* Gestion des objets enfants */
        if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {

            foreach ($enfants as $i => $enfant) {
                $enfants[$i]['options']['id_parent'] = $id_objet;
            }

            if ($err = maj_objets_persistants($nom_meta, $enfants, $forcer_maj)) {
                return $err;
            }
        }
        /* on enregistre la liste des définitions des objets. Il faut
           le faire à la fin pour éviter que lors d'appels récursifs,
           les enfants ne prennent le pas sur les parents */
        maj_meta('objets_persistants', $nom_meta, $objets);
    }
}

/**
 * Effacer les objets persistant corrspondants à une méta donnée.
 *
 * @param String $nom_meta : Le nom de la méta.
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon.
 */
function effacer_objets_persistants ($nom_meta) {

    include_spip('inc/config');

    $objets = lire_config("objets_persistants/$nom_meta");

    if ( ! $objets) { return; }

    foreach ($objets as $objet) {

        if ( ! objet_valide($objet)) {

            spip_log("objet persistant mal défini : "
                     . var_export($objet, TRUE), _LOG_ERREUR);

            return "erreur : objet " . var_export($objet, TRUE)
                                     . " non valide";
        }

        if ($err = objet_persistant_supprimer($nom_meta, $objet)) {
            return $err;
        }
    }

    /* supprimer la clé dans la méta 'objets_persistants' */
    maj_meta('objets_persistants', $nom_meta);
    effacer_meta($nom_meta);
}

/**
 * Teste la validité d'un tableau représentant un objet
 *
 * @param array $objet : le tableau représentant l'objet
 *
 * @return bool : True si le tableau est valide, False sinon
 */
function objet_valide ($objet) {

    return (isset($objet['objet'])
            AND table_objet_sql($objet['objet'])
            AND isset($objet['options'])
            AND isset($objet['options']['nom'])
            AND $objet['options']['nom']);
}

/**
 * Créer un nouvel objet persistant
 *
 * Si l'on trouve un objet qui correspond déjà à la description dans
 * la base on ne crée rien mais retourne son identifiant.
 *
 * @param array $objet : Le tableau de définition de l'objet
 *
 * @return int : l'identifiant de l'objet créé
 */
function objet_persistant_creer ($objet) {

    include_spip('base/abstract_sql');
    include_spip('action/editer_objet');

    $type_objet = objet_type($objet['objet']);
    $options = $objet['options'];
    $nom = $options['nom'];
    unset($options['nom']);

    /* On remplace une éventuelle clé 'id_parent' par la clé le nom du
       champ id_parent du type d'objet en question */
    if (isset($options['id_parent'])) {
        $id_parent = $options['id_parent'];
        unset($options['id_parent']);
        $options[id_parent_objet($type_objet)] = $id_parent;
    }

    /* S'il y a déjà un objet correspondant à la description
       on le prend plutôt que d'en créer un nouveau */
    $id_objet = sql_getfetsel(
        id_table_objet($type_objet),
        table_objet_sql($type_objet),
        array_map(function ($index, $element) {
            return $index . '=' . sql_quote($element);
        }, array_keys($options), $options));


    if (array_key_exists(id_parent_objet($type_objet), $options)) {

        $id_parent = $options[id_parent_objet($type_objet)];
        unset($options[id_parent_objet($type_objet)]);
    }

    /* Création d'un nouvel objet persistant */
    if ( ! $id_objet) {

        if ($id_parent) {
            $id_objet = objet_inserer($type_objet, $id_parent);
        } else {
            $id_objet = objet_inserer($type_objet);
        }

        objet_modifier($type_objet, $id_objet, $options);
    }

    return $id_objet;
}

/**
 * Supprimer un objet persistant
 *
 * On supprime récursivement toute la descendance, en commencant par
 * les plus jeunes
 *
 * @param String $nom_meta : Le nom de la méta correspondant à l'objet
 * @param array $objet : Le tableau de définition de l'objet
 *
 * @return int : l'identifiant de l'objet créé
 */
function objet_persistant_supprimer ($nom_meta, $objet) {

    include_spip('inc/config');
    include_spip('base/abstract_sql');
    include_spip('inc/autoriser');

    $type_objet = $objet['objet'];
    $options = $objet['options'];
    $nom = $options['nom'];
    unset($options['nom']);

    /* Gestion des objets enfants */
    if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {

        foreach ($enfants as $enfant) {
            if ($err = objet_persistant_supprimer($nom_meta, $enfant)) {
                return $err;
            }
        }
    }

    $id_objet = lire_config("$nom_meta/$nom");

    if (autoriser('supprimer', $type_objet, $id_objet)) {
        sql_delete(table_objet_sql($type_objet),
                   id_table_objet($type_objet) . '=' . intval($id_objet));
    } else {
        return "supprimer $type_objet $id_objet : action non autorisée";
    }
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