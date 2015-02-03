<?php
/**
 * API du plugin Objecteur
 *
 * @plugin     objecteur
 * @copyright  2014
 * @author     Michel Bystranowski
 * @licence    GNU/GPL
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Créer ou retrouver des objets
 *
 * Pour chacun des objets de la liste passée en paramètre, on cherche
 * dans la base de données s'il existe déjà un objet correspondant à
 * la description. S'il n'y a pas encore d'objet correspondant, on le
 * crée. On retourne un tableaux des identifiants des objets, indexés
 * par leurs noms. Si quelque chose s'est mal passé, on retourne un
 * message d'erreur.
 *
 * @param array $objets : Un tableau de définitions d'objets.
 *
 * @return mixed : Un tableau d'identifiants indexés par nom d'objet,
 *                 ou un message d'erreur si quelque chose s'est mal
 *                 passé.
 *
 * @exemple :
 *
$objecteur = charger_fonction('objecteur', 'inc');
$objecteur(array(
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

-> array(
    'rubrique_hors_menu' => 1,
    'rubrique_agenda' => 2,
)
 *
 */
function inc_objecteur_dist ($objets) {

    include_spip('action/editer_objet');

    $ids_objets = array();

    foreach ($objets as $objet) {

        if ( ! definition_objet_valide($objet)) {

            spip_log("objet persistant mal défini : " .
                     var_export($objet, TRUE), _LOG_ERREUR);

            return "erreur : objet " . var_export($objet, TRUE) .
                " non valide";
        }

        $id_objet = objecteur_creer_objet($objet);
        $ids_objets[$objet['options']['nom']] = $id_objet;

        /* Gestion des objets enfants */
        if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {

            foreach ($enfants as $i => $enfant) {
                $enfants[$i]['options']['id_parent'] = $id_objet;
            }

            $objecteur = charger_fonction('objecteur', 'inc');

            $ids_enfants = $objecteur($enfants);

            /* Si on a reçu un string, c'est qu'il y a eu une erreur */
            if (is_string($ids_enfants)) {
                return $ids_enfants;
            } else {
                $ids_objets = array_merge($ids_objets, $ids_enfants);
            }
        }
    }

    return $ids_objets;
}

/**
 * Effacer des objets en masse
 *
 * On efface les objets qui correspondent aux définitions de la liste
 * passée en paramètre
 *
 * @param array $objets : Une liste de définitions d'objets
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon
 */
function inc_objecteur_effacer_dist ($objets) {

    include_spip('base/abstract_sql');
    include_spip('inc/autoriser');

    foreach ($objets as $objet) {

        if ( ! definition_objet_valide($objet)) {

            spip_log("objet persistant mal défini : "
                     . var_export($objet, TRUE), _LOG_ERREUR);

            return "erreur : objet " . var_export($objet, TRUE)
                                     . " non valide";
        }

        /* On commence par supprimer les enfants */
        if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {

            $objecteur_effacer = charger_fonction('objecteur_effacer', 'inc');
            if ($err = $objecteur_effacer($enfants)) {
                return $err;
            }
        }

        $id_objet = objecteur_trouver($objet);

        if ($id_objet) {
            if (autoriser('supprimer', $objet['objet'], $id_objet)) {
                sql_delete(table_objet_sql($objet['objet']),
                           id_table_objet($objet['objet']) . '=' . intval($id_objet));
            } else {
                return "supprimer " . $objet['objet'] . " $id_objet : action non autorisée";
            }
        }
    }
}

/**
 * Trouver un objet éditorial correspondant à une définition
 *
 * @param array $def_objet : Une définition d'objet éditorial
 *
 * @return int : L'identifiant de l'objet trouvé
 */
function objecteur_trouver ($def_objet) {

    include_spip('base/abstract_sql');

    if (isset($def_objet['options']['nom'])) {
        unset($def_objet['options']['nom']);
    }

    return intval(sql_getfetsel(
        id_table_objet($def_objet['objet']),
        table_objet_sql($def_objet['objet']),
        array_map(function ($index, $element) {
            return $index . '=' . sql_quote($element);
        }, array_keys($def_objet['options']), $def_objet['options'])));
}

/**
 * Teste la validité d'un tableau représentant un objet
 *
 * @param array $def_objet : le tableau de définition de l'objet
 *
 * @return bool : True si le tableau est valide, False sinon
 */
function definition_objet_valide ($def_objet) {

    // TODO vérifier que les champs définis correspondent bien à des
    //      champs de la table de la BD

    return (isset($def_objet['objet'])
            AND table_objet_sql($def_objet['objet'])
            AND isset($def_objet['options'])
            AND isset($def_objet['options']['nom'])
            AND $def_objet['options']['nom']);
}

/**
 * Créer un nouvel objet
 *
 * Si l'on trouve un objet qui correspond déjà à la description dans
 * la base on ne crée rien mais retourne son identifiant.
 *
 * @param array $def_objet : Le tableau de définition de l'objet
 *
 * @return int : l'identifiant de l'objet
 */
function objecteur_creer_objet ($def_objet) {

    include_spip('base/abstract_sql');
    include_spip('action/editer_objet');
    include_spip('objecteur_fonctions');

    $type_objet = objet_type($def_objet['objet']);
    $options = $def_objet['options'];
    $nom = $options['nom'];
    unset($options['nom']);

    /* On remplace une éventuelle clé 'id_parent' par une clé du nom
       du champ id_parent du type d'objet en question. */
    if (isset($options['id_parent'])) {
        $id_parent = $options['id_parent'];
        unset($options['id_parent']);
        $options[id_parent_objet($type_objet)] = $id_parent;
    }

    /* S'il y a déjà un objet correspondant à la description
       on le prend plutôt que d'en créer un nouveau */
    $id_objet = objecteur_trouver(array('objet' => $type_objet,
                                        'options' => $options));

    if (array_key_exists(id_parent_objet($type_objet), $options)) {

        $id_parent = $options[id_parent_objet($type_objet)];
        unset($options[id_parent_objet($type_objet)]);
    }

    /* Création d'un nouvel objet */
    if ( ! $id_objet) {

        /* On fait une exception pour que ça fonctionne avec le plugin
           gma, mais à terme il faudrait plutôt implémenter l'api
           objet_inserer pour les groupes de mot-clés avec parents */
        if (($type_objet == 'groupe_mots') AND $id_parent) {
            $options['id_parent'] = $id_parent;
            unset($id_parent);
            $id_objet = objet_inserer('groupe_mots');
            sql_updateq('spip_groupes_mots', $options, "id_groupe=$id_objet");
            return $id_objet;
        }

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
 * DÉPRÉCIÉ - Crée des objets éditoriaux et enregistre leurs id dans de métas
 *
 * @param String $nom_meta : Le nom de la meta dans laquelle seront
 *                           stocké les objets persistants
 * @param array $objets : Un tableau de définitions d'objets.
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon.
 */
function maj_objets_persistants ($nom_meta, $objets) {

    include_spip('inc/meta');
    include_spip('objecteur_fonctions');

    if ( ! $ids_objets = lire_config($nom_meta)) {
        $objecteur = charger_fonction('objecteur', 'inc');
        $ids_objets = $objecteur($objets);
    }

    ecrire_meta($nom_meta, serialize($ids_objets));
    maj_meta('objets_persistants', $nom_meta, $ids_objets);
}