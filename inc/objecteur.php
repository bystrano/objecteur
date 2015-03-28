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
 * @param array $objets : Une définition d'objet, ou une liste de
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

    foreach ($objets as $objet) {

        if ($err = objecteur_valider_definition($objet)) {

            return _T('objecteur:erreur_definition_invalide',
                      array('err' => $err));
        }
    }

    $liste_objets = objecteur_calculer_liste($objets);

    if ($err = objecteur_valider_liste($liste_objets)) {
        return $err;
    }

    /* On vérifie qu'il n'y ait pas d'orphelins */
    foreach ($liste_objets as $objet) {

        if (array_key_exists(objet_type($objet['objet']), $GLOBALS['id_parents_objets']) AND
            ( ! in_array(objet_type($objet['objet']), $GLOBALS['objets_orphelins'])) AND
            ( ! array_key_exists($GLOBALS['id_parents_objets'][objet_type($objet['objet'])], $objet['options']))) {

            return _T('objecteur:erreur_objet_orphelin',
                      array('objets' => table_objet($objet['objet'])));
        }
    }

    $liste_objets = objecteur_ordonner_liste($liste_objets);

    if (is_string($liste_objets)) {
        return _T('objecteur:erreur_creation_objets_impossible',
                  array('liste_objets' => $liste_objets));
    }

    $ids_objets = array();

    foreach ($liste_objets as $objet) {

        $objet = objecteur_remplacer_references($objet, $ids_objets);

        $id_objet = objecteur_creer_objet($objet, $forcer_creation);

        if ($objet['options']['nom']) {
            $ids_objets[$objet['options']['nom']] = $id_objet;
        } else {
            $ids_objets[] = $id_objet;
        }
    }

    return $ids_objets;
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
       directement un définition d'objet plutôt qu'une liste de
       définition. On en fait alors une liste… */
    if (isset($objets['objet'])) {
        $objets = array($objets);
    }

    foreach ($objets as $objet) {

        if ($err = objecteur_valider_definition($objet)) {

            return _T('objecteur:erreur_definition_invalide',
                      array('err' => $err));
        }
    }

    $liste_objets = objecteur_effacer_calculer_liste($objets);

    if ($err = objecteur_valider_liste($liste_objets)) {
        return $err;
    }

    $liste_objets = objecteur_effacer_resoudre_references($liste_objets);

    if (is_string($liste_objets)) {
        return _T('objecteur:erreur_suppression_impossible',
                  array('liste_objets' => $liste_objets));
    }

    foreach ($liste_objets as $objet) {

        $id_objet = objecteur_trouver($objet);

        if ($id_objet) {
            if (autoriser('supprimer', $objet['objet'], $id_objet)) {
                sql_delete(table_objet_sql($objet['objet']),
                           id_table_objet($objet['objet']) . '=' . intval($id_objet));
            } else {
                return _T('objecteur:erreur_suppression_non_autorisee',
                          array(
                              'objet' => $objet['objet'],
                              'id_objet' => $id_objet,
                          ));
            }

        } else {
            return _T('objecteur:erreur_suppression_objet_introuvable',
                      array('objet' => var_export($objet, TRUE)));
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

    // On ne construit aucune requête SQL avec un élément de la white liste
    foreach($GLOBALS['objecteur_white_list'] as $element) {
        unset($def_objet['options'][$element]);
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
        return _T('objecteur:erreur_definition_pas_tableau',
                  array('objet' => $string_objet));
    }

    if ( ! isset($def_objet['objet'])) {
        return _T('objecteur:erreur_definition_pas_cle_objet',
                  array('objet' => $string_objet));
    }

    if ( ! isset($def_objet['options'])) {
        return _T('objecteur:erreur_definition_pas_cle_options',
                  array('objet' => $string_objet));
        return ;
    }

    $type_objet = $def_objet['objet'];
    $options = $def_objet['options'];

    if ( ! table_objet_sql($type_objet)) {
        return _T('objecteur:erreur_definition_type_objet_introuvable',
                  array('objet' => $string_objet));
    }

    include_spip('base/abstract_sql');

    $desc_table = description_table(table_objet_sql($type_objet));
    $champs_table = array_keys($desc_table['field']);

    foreach ($options as $cle => $valeur) {
        /* Les options peuvent être dans la white_list, id_parent
           ou un champ de la table du type d'objet en question */
        if ( (!in_array($cle, $GLOBALS['objecteur_white_list']))
            AND ($cle !== 'id_parent') // L'id_parent n'est pas dans la white liste
            AND ( ! in_array($cle, $champs_table))) {

            return _T('objecteur:erreur_definition_cle_invalide',
                      array(
                          'cle' => $cle,
                          'objet' => $string_objet,
                      ));
        }
    }

    /* les documents nécessitent une option fichier */
    if (($type_objet === 'document')
        AND ( ! isset($options['fichier']))) {

        return _T('objecteur:erreur_option_manquante',
                  array(
                      'option' => 'fichier',
                      'objet' => $string_objet,
                  ));
    }

    /* Si l'objet est valide, on teste récursivement les enfants */
    $enfants = isset($def_objet['enfants']) ? $def_objet['enfants'] : array();

    foreach ($enfants as $enfant) {
        if ($err = objecteur_valider_definition($enfant)) {
            return $err;
        }
    }
}

/* Pour nommer automatiquement les objets non-nommés, on les numérote
   avec un compteur global. Ça serait plus simple de faire ça avec des
   id aléatoires, mais dans ce cas on ne pourrait plus tester, donc on
   préfère bricoler avec la globale… */
$GLOBALS['objecteur_compteur'] = 0;

/**
 * Applatit une arborescence d'objets
 *
 * Retourne une liste de définitions d'objets à créer pour créer
 * l'arborescence.
 *
 * @param array $objets : une liste de définitions d'objets
 * @return array : une liste d'objets sans enfants, prêts à être créés
 */
function objecteur_calculer_liste ($objets) {

    $liste_objets = array();

    foreach ($objets as $objet) {

        /* On remplace une éventuelle clé 'id_parent' par une clé du nom
           du champ id_parent du type d'objet en question. */
        if (isset($objet['options']['id_parent'])) {
            $id_parent = $objet['options']['id_parent'];
            unset($objet['options']['id_parent']);
            $objet['options'][id_parent_objet($objet['objet'])] = $id_parent;
        }

        if (isset($objet['enfants']) AND $enfants = $objet['enfants']) {
            unset($objet['enfants']);
        }

        if (isset($objet['documents']) AND $documents = $objet['documents']) {
            unset($objet['documents']);
        }

        $liste_objets[] = $objet;

        /* Gestion des objets enfants */
        if ($enfants) {

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
                /* On remplace l'objet qu'on vient de mettre dans la
                   liste par une version nommée. */
                array_pop($liste_objets);
                $liste_objets[] = $objet;
            }

            foreach ($enfants as $i => $enfant) {
                $enfants[$i]['options']['id_parent'] = "@" . $objet['options']['nom'] . "@";
            }

            $liste_enfants = objecteur_calculer_liste($enfants);
            $liste_objets = array_merge($liste_objets, $liste_enfants);

        }

        /* Gestion des documents */
        if ($documents) {

            /* Si le tableau de documents est une liste de strings,
               c'est qu'on utilise le format simplifé. On commence
               alors par tout compliquer :-) */
            $format_simple = TRUE;
            foreach ($documents as $doc) {
                if ( ! is_string($doc)) {
                    $format_simple = FALSE;
                    break;
                }
            }
            if ($format_simple) {
                $documents = array_map(function ($fichier) {
                    return array(
                        'objet' => 'document',
                        'options' => array(
                            'fichier' => $fichier,
                        ),
                    );
                }, $documents);
            }

            /* si l'objet parent n'a pas de nom, on lui en donne un */
            if ( ! isset($objet['options']['nom'])) {

                $objet['options']['nom'] = '__' . $objet['objet'] . '-'
                    . $GLOBALS['objecteur_compteur'];

                $GLOBALS['objecteur_compteur'] += 1;
                /* On remplace l'objet qu'on vient de mettre dans la
                   liste par une version nommée. */
                array_pop($liste_objets);
                $liste_objets[] = $objet;
            }

            foreach ($documents as $i => $doc) {
                $documents[$i]['parent_doc']['objet'] = $objet['objet'];
                $documents[$i]['parent_doc']['id_objet'] = "@" . $objet['options']['nom'] . "@";
            }

            $liste_documents = objecteur_calculer_liste($documents);
            $liste_objets = array_merge($liste_objets, $liste_documents);
        }
    }

    return $liste_objets;
}

/**
 * Applatit une arborescence d'objets
 *
 * Presque la même chose que objecteur_calculer_liste, mais la
 * récursion se fait dans l'autre sens, les enfants sont traités en
 * premier
 *
 * @param array $objets : une liste de définitions d'objets
 * @return array : une liste d'objets sans enfants pour suppression
 */
function objecteur_effacer_calculer_liste ($objets) {

    $liste_objets = array();

    foreach ($objets as $objet) {

        /* On commence par supprimer les enfants */
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

            $liste_enfants = objecteur_effacer_calculer_liste($enfants);

            $liste_objets = array_merge($liste_objets, $liste_enfants);
            unset($objet['enfants']);
        }

        /* On remplace une éventuelle clé 'id_parent' par une clé du nom
           du champ id_parent du type d'objet en question. */
        if (isset($objet['options']['id_parent'])) {
            $id_parent = $objet['options']['id_parent'];
            unset($objet['options']['id_parent']);
            $objet['options'][id_parent_objet($objet['objet'])] = $id_parent;
        }

        $liste_objets[] = $objet;
    }

    return $liste_objets;
}

/**
 * Teste la validité d'une liste d'objets
 *
 * @param array $liste_objets : la liste de définitions d'objets
 *
 * @return mixed : Un message d'erreur si la liste est invalide, rien
 *                 sinon.
 */
function objecteur_valider_liste ($liste_objets) {

    /* On teste l'unicité des noms */
    $noms_objets = array();
    foreach ($liste_objets as $objet) {

        if ( ! $objet['options']['nom']) continue;

        if ( ! in_array($objet['options']['nom'], $noms_objets)) {
            $noms_objets[] = $objet['options']['nom'];
        } else {
            return _T('objecteur:erreur_liste_invalide',
                      array('err' => _T('objecteur:erreur_doublon_nom',
                                        array('nom' => $objet['options']['nom']))));
        }
    }
}

/**
 * Élimine les doublons dans une liste d'objets
 *
 * @param array $liste_objets : La liste d'objets
 *
 * @return array : La liste sans les doublons
 */
function objecteur_dedoublonner_liste ($liste_objets) {

    $liste_filtree = array();

    foreach ($liste_objets as $objet) {
        if ( ! in_array($objet, $liste_filtree)) {
            $liste_filtree[] = $objet;
        }
    }

    return $liste_filtree;
}

/**
 * S'assurer que les références d'une liste d'objets sont calculables.
 *
 * On vérifie que chaque référence existera déjà au moment où on en
 * aura besoin. Si ça n'est pas le cas, on essaie de réordonner la
 * liste pour que ça marche, et si ça n'est pas possible, on renvoie
 * un message d'erreur.
 *
 * @param array $liste_objets : Un liste d'objets sans enfants telle
 *                              que produite par la fonction
 *                              objecteur_calculer_liste.
 *
 * @return mixed : Un message d'erreur si une référence ne peut être
 *                 satisfaite, la liste dans un ordre qui ne posera
 *                 pas de problème de références.
 */
function objecteur_ordonner_liste ($liste_objets) {

    $ids_objets = array();
    $liste_ordonnee = array();

    /* on parcourt la liste des objets, et quand les références d'un
       objet sont connues, on le retire de la liste pour le mettre
       dans la liste ordonnée. Quand on les a tous essayés on
       recommence avec ceux qui restent, en espérant que les
       références qui manquaient ont été trouvées entre temps. Si on
       parcourt toute la liste sans trouver aucun objet à ajouter à la
       liste ordonnée, c'est que la liste n'est pas calculable. */
    while ($liste_objets) {

        $nb_objets = count($liste_objets);

        foreach ($liste_objets as $index => $objet) {
            $err = objecteur_remplacer_references($objet, $ids_objets);
            /* Si on a pu remplacer les références, on met l'objet
               dans la liste ordonnée, et on le retire de la liste. */
            if ( ! is_string($err)) {
                $ids_objets[$objet['options']['nom']] = 'ok';
                $liste_ordonnee[] = $objet;
                unset($liste_objets[$index]);
            }
        }

        /* Si le nombre d'objets dans la liste n'a pas baissé, c'est
           qu'on tourne en rond */
        if ((count($liste_objets) > 0) AND
            (count($liste_objets) === $nb_objets)) {

            $references_manquantes = '';
            foreach ($liste_objets as $objet) {
                $references_manquantes .= "\n - " . objecteur_remplacer_references($objet, $ids_objets);
            }

            return _T('objecteur:erreur_liste_invalide',
                      array('err' => $references_manquantes));
        }
    }

    return $liste_ordonnee;
}

/**
 * Remplacer les références à des objets existants par leurs
 * identifiants dans une liste d'objets à effacer
 *
 * On parcourt les objets de la liste, en résolvant les références,
 * jusqu'à ce qu'on ait tout résolu ou qu'on soit bloqué
 *
 * @param array $objet : Une liste de définitions d'objets. Les
 *                       éventuels enfants sont ignorés, comme les
 *                       objets ne sont plus sensés en avoir à cette
 *                       étape.
 *
 * @return array : La liste de définitions d'objets dans laquelle on a
 *                 remplacé les références par leurs valeurs, ou un
 *                 message d'erreur si l'une des références ne peut
 *                 être déterminée.
 */
function objecteur_effacer_resoudre_references ($liste_objets) {

    $liste_resolue = $liste_objets;
    $ids_objets = array();

    while ($liste_objets) {

        $compteur_objets_trouves = 0;

        foreach ($liste_objets as $index => $objet) {

            $objet = objecteur_remplacer_references($objet, $ids_objets);

            if ( ! is_string($objet)) {
                if ($id_objet = objecteur_trouver($objet)) {
                    $ids_objets[$objet['options']['nom']] = $id_objet;
                } else {
                    return _T('objecteur:erreur_objet_introuvable',
                              array('objet' => var_export($objet, TRUE)));
                }
                $liste_resolue[$index] = $objet;
                unset($liste_objets[$index]);
                $compteur_objets_trouves++;
            }

        }

        if ($compteur_objets_trouves === 0) {

            $references_manquantes = '';
            foreach ($liste_objets as $objet) {
                $references_manquantes .= "\n - " . objecteur_remplacer_references($objet, $ids_objets);
            }

            return _T('objecteur:erreur_liste_invalide',
                      array('err' => $references_manquantes));
        }
    }

    return $liste_resolue;
}

/**
 * Remplacer les références à des objets existants par leurs
 * identifiants dans la définition d'un objet
 *
 * On parcourt la définition de l'objet, et on remplace les références
 * entre des @ par l'identifiant correspondant de la liste de objets.
 *
 * @param array $objet : Une définition d'un objet. Les éventuels
 *                       enfants sont ignorés, comme les objets ne
 *                       sont plus sensés en avoir à cette étape.
 *
 * @param array $ids_objets : Un tableau clé/valeur dont les clés sont
 *                            des nom d'objets et les valeurs leurs
 *                            identifiants
 *
 * @return array : La définition de l'objet dans laquelle on a
 *                 remplacé les références par leurs valeurs, ou un
 *                 message d'erreur si l'une des références ne peut
 *                 être déterminée.
 */
function objecteur_remplacer_references ($objet, $ids_objets) {

    foreach ($objet as $cle_principale => $valeur_principale) {

        if (is_array($valeur_principale)) {

            foreach ($valeur_principale as $cle => $valeur) {
                if (!in_array($cle, $GLOBALS['objecteur_white_list'])
                    AND (preg_match('/^@.*@$/', $valeur) === 1)) {
                    $reference = trim($valeur, '@');
                    if (array_key_exists($reference, $ids_objets)) {
                        $objet[$cle_principale][$cle] = $ids_objets[$reference];
                    } else {
                        return _T('objecteur:erreur_reference_manquante',
                                  array('reference' => $reference));
                    }
                }
            }
        }
    }

    return $objet;
}

/**
 * Ajouter un logo à un objet
 *
 * @param String $objet     : Le type d'objet auquel on souhaite ajouter
 *                            un logo
 * @param Integer $id_objet : L'identifiant de l'objet
 * @param String $logo      : L'image à utiliser comme logo. Peut être un
 *                            chemin ou une adresse http://
 *
 * @return String : Un message d'erreur si quelque chose s'est mal
 *                  passé, rien sinon.
 */
function objecteur_ajouter_logo($objet, $id_objet, $logo) {

    // On va commencer par faire une copie local du logo
    include_spip('inc/distant');
    $logo_chemin = copie_locale($logo, 'force');

    // Dans le cas de SPIP 3.1 on utilise la fonction qui va bien
    if (include_spip('action/editer_logo'))
        return logo_modifier($objet, $id_objet, 'on', _DIR_RACINE.$logo_chemin);

    // Si on est en SPIP 3.0 on délègue à la fonction ajouter_image
    include_spip('action/iconifier');
    $chercher_logo = charger_fonction('chercher_logo','inc');
    $ajouter_image = charger_fonction('spip_image_ajouter','action');

    $type = type_du_logo(id_table_objet($objet));
    $logo = $chercher_logo($id_objet, id_table_objet($objet));

    if ($logo)
        spip_unlink($logo[0]);

    return $ajouter_image($type."on".$id_objet," ", array('tmp_name' => _DIR_RACINE.$logo_chemin), true);
}

/**
 * Ajouter un ou plusieurs documents à un objet
 *
 * @param String $objet     : Le type d'objet auquel on souhaite ajouter
 *                            le ou les documents.
 * @param Integer $id_objet : L'identifiant de l'objet
 * @param array $files      : Un fichier ou une liste de fichiers pour
 *                            les ou les documents. Chemin ou une
 *                            adresse http://
 *
 * @return Les identifiants des documents créés
 */
function objecteur_ajouter_documents($objet, $id_objet, $files) {
    include_spip('inc/distant');

    /* Si on n'a pas passé un tableau, c'est qu'on a mis directement
       un chemin. On en fait un tableau à un seul élément. */
    if ( ! is_array($files)) {
        $files = array($files);
    }

    $documents = array();
    foreach($files as $file) {
        // Les fichiers donnés par une url sont considérés comme distants
        if (preg_match(',^https?://,', $file) === 1) {

            $documents[] = array(
                'name' => $file,
                'distant' => true,
                'tmp_name' => $file,
            );
        } else {
            $documents[] = array(
                'name' => $file,
                'tmp_name' => $file,
            );
        }
    }

    // On ajoute les documents à un objet SPIP.
    $ajouter_documents = charger_fonction('ajouter_documents','action');
    return $ajouter_documents(
        'new',
        $documents,
        $objet, // Article, rubrique, autre objet spip
        $id_objet,
        'document'
    );
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

    /* On vérifie qu'on est autorisé */
    include_spip('inc/autoriser');
    if ( ! autoriser('creer', $type_objet)) {
        return _T('objecteur:erreur_creation_objet_interdite',
                  array('objet' => $type_objet));
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

        /* s'il y a un id_trad, on le met de côté pour plus tard */
        if (isset($options['id_trad'])) {
            $id_trad = $options['id_trad'];
            unset($options['id_trad']);
        }

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

        /* Les documents sont spéciaux, on s'en occupe à part */
        if ($type_objet == 'document') {

            /* S'il y a une clé parent_doc dans la définition, c'est
               pour définir un objet parent pour le document */
            if (isset($def_objet['parent_doc'])) {

                $parent_doc = $def_objet['parent_doc'];
                $id_objets = objecteur_ajouter_documents(
                    $parent_doc['objet'], $parent_doc['id_objet'],
                    $options['fichier']);

            } else {
                $id_objets = objecteur_ajouter_documents('', 0, $options['fichier']);
            }

            $id_objet = array_shift($id_objets);
            // On évite de passer le fichier à objet_modifier
            unset($options['fichier']);

        } else {

            if ($id_parent) {
                $id_objet = objet_inserer($type_objet, $id_parent);
            } else {
                $id_objet = objet_inserer($type_objet);
            }
        }

        objet_modifier($type_objet, $id_objet, $options);

        /* une fois l'objet créé, on s'occupe d'un éventuel lien de
           traduction */
        if ($id_trad) {
            $referencer_traduction = charger_fonction('referencer_traduction','action');
            $referencer_traduction($type_objet, $id_objet,$id_trad);
        }

        // On s'occupe du logo
        if (isset($options['logo']) AND $options['logo'])
            objecteur_ajouter_logo($type_objet, $id_objet, $options['logo']);

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