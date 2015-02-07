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
 * Pour la définition d'objet (ou pour chaque objet de la liste de
 * définitions) passée en paramètre, on cherche dans la base de
 * données s'il existe déjà un objet correspondant à la
 * description. S'il n'y a pas encore d'objet correspondant, on le
 * crée. On retourne un tableaux des identifiants des objets, indexés
 * par leurs noms. Les identifiants des objets qui n'ont pas définis
 * d'option `nom` sont indexés par ordre de création. Si quelque chose
 * s'est mal passé, on retourne un message d'erreur.
 *
 * @param array $objets : Une définition d'objet, ou un tableau de
 *                        définitions d'objets.
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
    array(
        'objet' => 'mot',
        'options' => array(
            'titre' => 'humeur',
        ),
    ),
));

-> array(
    0 => 1, // l'identifiant du mot-clé "humeur"
    'rubrique_hors_menu' => 1,
    'rubrique_agenda' => 2,
)
 *
 */
function inc_objecteur_dist ($objets, $forcer_creation=FALSE) {

    include_spip('action/editer_objet');

    /* Si le paramètre $objets à une clé 'objet', c'est qu'on a passé
       directemenet un définition d'objet plutôt qu'une liste de
       définition. On en fait alors une liste… */
    if (isset($objets['objet'])) {
        $objets = array($objets);
    }

    $ids_objets = array();

    foreach ($objets as $objet) {

        if ($err = objecteur_valider_definition($objet)) {

            spip_log("objecteur: définition de l'objet invalide : $err", _LOG_ERREUR);

            return "définition de l'objet invalide : $err";
        }
    }

    $liste_objets = objecteur_calculer_liste($objets);

    foreach ($liste_objets as $objet) {

        $id_objet = objecteur_creer_objet($objet, $forcer_creation);

        if (isset($objet['options']['nom'])) {
            $ids_objets[$objet['options']['nom']] = $id_objet;
        } else {
            $ids_objets[] = $id_objet;
        }

    }

    return $ids_objets;
}

/* Pour nommer automatiquement les objets non-nommés, on les numérote
   avec un compteur global. Ça serait plus simple de faire ça avec des
   id aléatoires, mais dans ce cas on ne pourrait plus tester, donc on
   préfère bricoler avec la globale… */
$GLOBALS['objecteur_compteur'] = 0;

/**
 * Calcule les choses à faire pour créer une arborescence d'objets
 *
 * Retourne une liste de définitions d'objets à créer, dans le bon
 * ordre. La fonction objecteur n'a alors plus qu'à créer ces objets.
 *
 * @param array $objets : une liste de définitions d'objets
 * @return array : une liste d'objets sans enfants, prêts à être créés
 */
function objecteur_calculer_liste ($objets) {

    $liste_objets = array();

    foreach ($objets as $objet) {

        /* Gestion des objets enfants */
        if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {

            /* à l'image du paramètre $objets de la fonction
               objecteur, la clé enfants peut être une définition
               d'objet plutôt qu'une liste de définitions. */
            if (isset($enfants['objet'])) {
                $enfants = array($enfants);
            }

            /* si l'objet parent n'a pas de nom, on lui en donne un */
            if ( ! isset($objet['options']['nom'])) {

                $objet['options']['nom'] = '__' . $objet['objet'] . '-'
                    . $GLOBALS['objecteur_compteur'];

                $GLOBALS['objecteur_compteur'] += 1;
            }

            foreach ($enfants as $i => $enfant) {
                $enfants[$i]['options']['id_parent'] = "@" . $objet['options']['nom'] . "@";
            }

            $ids_enfants = objecteur_calculer_liste($enfants);

            unset($objet['enfants']);
            $liste_objets[] = $objet;
            $liste_objets = array_merge($liste_objets, $ids_enfants);

        } else {
            $liste_objets[] = $objet;
        }
    }

    return $liste_objets;
}

/**
 * Effacer des objets en masse
 *
 * On efface le ou les objets qui correspondent à la ou aux
 * définitions de la liste passée en paramètre.
 *
 * @param array $objets : Une définition d'objet, ou un tableau de
 *                        définitions d'objets.
 *
 * @return mixed : Un message d'erreur si quelque chose s'est mal
 *                 passé, rien sinon
 */
function inc_objecteur_effacer_dist ($objets) {

    include_spip('base/abstract_sql');
    include_spip('inc/autoriser');

    /* Si le paramètre $objets à une clé 'objet', c'est qu'on a passé
       directemenet un définition d'objet plutôt qu'une liste de
       définition. On en fait alors une liste… */
    if (isset($objets['objet'])) {
        $objets = array($objets);
    }

    foreach ($objets as $objet) {

        if ($err = objecteur_valider_definition($objet)) {

            spip_log("objecteur_effacer: définition de l'objet invalide : $err", _LOG_ERREUR);

            return "définition de l'objet invalide : $err";
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
 * @return mixed : Un message d'erreur si la définition est invalide,
 *                 rien sinon.
 */
function objecteur_valider_definition ($def_objet) {

    $string_objet = var_export($def_objet, TRUE);

    if ( ! is_array($def_objet)) {
        return "La définition de l'objet doit être un tableau !" .
            "\n$string_objet";
    }

    if ( ! isset($def_objet['objet'])) {
        return "La définition de l'objet n'as pas de clé 'objet'" .
            "\n$string_objet";
    }
    if ( ! isset($def_objet['options'])) {
        return "La définition de l'objet n'as pas de clé 'options'" .
            "\n$string_objet";
    }

    $type_objet = $def_objet['objet'];
    $options = $def_objet['options'];

    if ( ! table_objet_sql($type_objet)) {
        return "Le type d'objet $type_objet n'existe pas !" .
            "\n$string_objet";
    }

    include_spip('base/abstract_sql');

    $desc_table = description_table(table_objet_sql($type_objet));
    $champs_table = array_keys($desc_table['field']);

    foreach ($options as $cle => $valeur) {
        /* Les options peuvent être 'nom', 'id_parent', ou un champ de
           la table du type d'objet en question */
        if (($cle !== 'nom')
            AND ($cle !== 'id_parent')
            AND ( ! in_array($cle, $champs_table))) {
            return "$cle n'est pas une option valide" .
                "\n$string_objet";
        }
    }

    /* Si l'objet est valide, on teste récursivement les enfants */
    $enfants = isset($def_objet['enfants']) ? $def_objet['enfants'] : array();

    foreach ($enfants as $enfant) {
        if ($err = objecteur_valider_definition($enfant)) {
            return $err;
        }
    }
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
function objecteur_creer_objet ($def_objet, $forcer_creation) {

    include_spip('base/abstract_sql');
    include_spip('action/editer_objet');
    include_spip('objecteur_fonctions');

    $type_objet = objet_type($def_objet['objet']);
    $options = $def_objet['options'];

    if (isset($options['nom'])) unset($options['nom']);

    /* On remplace une éventuelle clé 'id_parent' par une clé du nom
       du champ id_parent du type d'objet en question. */
    if (isset($options['id_parent'])) {
        $id_parent = $options['id_parent'];
        unset($options['id_parent']);
        $options[id_parent_objet($type_objet)] = $id_parent;
    }

    /* S'il y a déjà un objet correspondant à la description
       on le prend plutôt que d'en créer un nouveau */
    if ( ! $forcer_creation) {
        $id_objet = objecteur_trouver(array('objet' => $type_objet,
                                            'options' => $options));
    }

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