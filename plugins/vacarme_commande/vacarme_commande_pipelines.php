<?php
   if (!defined("_ECRIRE_INC_VERSION")) return;

   // =================
   // = Insertion css =
   // =================
   function vacarme_commande_insert_head_css($flux){
      $inuit        = find_in_path('css/inuit.css');
      $grille12col  = find_in_path('css/grid.inuit.css');
      $css          = find_in_path('css/styles.css');
      $ie8          = find_in_path('css/ie8.css');
      $flux        .= "\n<link rel='stylesheet' type='text/css' href='$inuit' />\n";
      $flux        .= "<link rel='stylesheet' type='text/css' href='$grille12col' />\n";
      $flux        .= "<link rel='stylesheet' type='text/css' href='$css' />\n";
      $flux        .= "<!--[if lte IE 8]><link rel='stylesheet' type='text/css' href='$ie8' /><![endif]-->\n";
      return $flux;
   }


   // ================
   // = Insertion js =
   // ================
   function vacarme_commande_insert_head($flux){
      $js_public = find_in_path('javascript/vacarme_commande-public.js');
      if ($js_public) {
         $flux     .= "\n<script src='$js_public' type='text/javascript'></script>\n";
      }
      return $flux;
   }

   // Header espace privé
   function vacarme_commande_header_prive($flux){
   	$js = find_in_path('prive/squelettes/javascript/vacarme_commande_prive.js');
   	$flux .= "\n<script type='text/javascript' src='$js'></script>\n";
   	$css = generer_url_public('prive/squelettes/css/vacarme_commande_prive.css');
   	$flux .= "\n<link rel='stylesheet' href='$css' type='text/css' media='all' />\n";
   	return $flux;
   }


   // ==============================
   // = pipeline traitement_paypal =
   // ==============================
   // 20121009 : le traitement paypal se fait uniquement ici (et non plus dans commandes_paypal_pipelines). L'identification ne se fait plus sur la référence de la commande (son numéro) mais sur son identifiant.
   function vacarme_commande_traitement_paypal($flux) {
      if (_DEBUG_VACARME) spip_log("entrée traitement_paypal",'vacarme_debug');
      if (
      $flux['args']['paypal']['custom'] == 'payer_commande'
         and $id_commande = $flux['args']['paypal']['invoice']
            and $commande = sql_fetsel('statut, id_auteur', 'spip_commandes', 'id_commande = '.sql_quote($id_commande))
      ){
         //$ref = $commande['reference'];
         //$id_commande = $commande['id_commande'];
         $statut_commande = $commande['statut'];
         $statut_paypal = $flux['args']['paypal']['payment_status'];
         $prix_paypal = $flux['args']['paypal']['mc_gross'];
         if (_DEBUG_VACARME) spip_log("id_commande : ".$id_commande." id_commande paypal ".$id_commande,'vacarme_debug');

         // Si le statut Paypal est "Pending" on passe juste la commande en attente et on verra plus tard pour le reste
         if ($statut_paypal == 'Pending'){
            $statut_nouveau = 'attente';
         }
         // Si Paypal est "Completed" on vérifie que le montant correspond au prix de cette commande
         elseif ($statut_paypal == 'Completed'){
            $fonction_prix = charger_fonction('prix', 'inc/');
            $prix_commande = $fonction_prix('commande', $id_commande);

            // Si on a pas assez payé
            if ($prix_paypal < $prix_commande){
               $statut_nouveau = 'partiel';
            }
            // Sinon c'est bon
            else{
               $statut_nouveau = 'paye';
            }
         }
         // Sinon on dit que c'est en erreur
         else{
            $statut_nouveau = 'erreur';
         }
         // le type de paiement est renseigné dans tous les cas.
         sql_updateq('spip_commandes',array('paiement' => 'paypal'),'id_commande='.$id_commande);

         if (_DEBUG_VACARME) spip_log("traitement_paypal envoi vers instituer $id_commande-$statut_nouveau",'vacarme_debug');
         //on institue la commande
         $action = charger_fonction('instituer_commande', 'action');
         $action($id_commande."-".$statut_nouveau);
      }
      return $flux;
   }


   // ===========================
   // = pipeline post_insertion =
   // ===========================

   // flux depuis creer_commande_encours : on ajoute l'id de commande dans sa référence
   function vacarme_commande_post_insertion ($flux) {
      if ($flux['args']['table'] == 'spip_commandes' and $flux['args']['id_objet']) {
         $id_commande = intval($flux['args']['id_objet']);
         $reference = $flux['data']['reference']; // de la forme aaaammjj-id_auteur
         $reference = $reference."-".$id_commande; // devient aaaammjj-id_auteur-id_commande
         sql_updateq("spip_commandes", array('reference' => $reference), "id_commande=$id_commande");
         // on renvoie dans le flux la nouvelle référence
         $flux['data']['reference'] = $reference;

         // le prix et la TVA enregistrée précédemment lors de la création de commande (par le pipeline panier2commande_post_insertion) sont faux (prix TTC de la base, tva à zéro). Donc correction.
         // On ne se préoccupe pas si le client paie ou pas la TVA. On enregistre le prix ht.
         include_spip('inc/config');
      	$tx_tva = lire_config('produits/taxe', 0);

         $cde = sql_allfetsel('id_commandes_detail,prix_unitaire_ht,taxe','spip_commandes_details','id_commande='.$id_commande.' AND prix_unitaire_ht != 0');
         if ($cde) {
            foreach($cde as $emplette) {
               $prix_ht = $emplette['prix_unitaire_ht']/($tx_tva + 1);
               sql_updateq('spip_commandes_details',array('prix_unitaire_ht' => $prix_ht, 'taxe' => $tx_tva),'id_commandes_detail='.$emplette['id_commandes_detail']);
            }
         }
         // le client doit-il payer la tva ? Cela dépend de son pays de résidence et s'il est particulier ou organisation
         $tva = true; // a priori oui...

         $id_auteur = $flux['data']['id_auteur'];
         $row = sql_fetsel('*','spip_commandes','id_commande='.intval($id_commande));
         if($id_auteur AND $row['id_auteur'] == $id_auteur) {
            // faire autant de selections pour connaitre le pays et le type du client : problème...
            $contact = sql_fetsel('type_client,id_contact','spip_contacts','id_auteur='.$id_auteur);
            $id_adresse = sql_fetsel('id_adresse','spip_adresses_liens','id_objet='.$id_auteur.' AND type='.sql_quote('principale').' AND objet='.sql_quote('auteur'));
            $pays = sql_fetsel('pays','spip_adresses','id_adresse='.$id_adresse['id_adresse']);
            $tva_applicable = charger_fonction('tva_applicable', 'inc');
            $tva = $tva_applicable($contact['type_client'],$pays['pays']);
         }
         // récupération des prix de chaque élément qui compose la commande
         if ($montants = sql_allfetsel('prix_unitaire_ht, taxe, quantite', 'spip_commandes_details', 'id_commande='.$id_commande)) {
             foreach ($montants as $m) {
                $total_ht += $m['prix_unitaire_ht']*$m['quantite'];
                if ($tva) $total_ttc += round(($m['prix_unitaire_ht']*($m['taxe']+1)),2)*$m['quantite'];
             }
             #var_dump($total_ht,$total_ttc)
             $total_ht = round($total_ht,2);
             if (!$tva) $total_ttc = $total_ht;
         }
         #var_dump($total_ht,$total_ttc); die();
         // ajout des infos de transactions dans la table commandes_transactions
         $inserer_transaction = charger_fonction('inserer_transaction','inc/');
         $id_transaction = $inserer_transaction($id_commande,$reference,$total_ht,$total_ttc);
         if ($id_transaction == 0)
            spip_log("Pas d'id_transaction","vacarme_debug");
      }
      return $flux;
   }


   // =========================
   // = pipeline post_edition =
   // =========================
   function vacarme_commande_post_edition($flux){
      // après instituer_commande, on peut récupérer le flux.
      if ($flux['args']['table'] == 'spip_commandes' AND $flux['args']['action'] == 'instituer') {
         $statut = $flux['data']['statut'];
         $statut_ancien = $flux['args']['statut_ancien'];
         $id_commande = $flux['args']['id_objet'];

         // on a une commande, on verifie si on un abonnement dedans
         // et si c'est le cas, on insère les numéros de début et de fin d'abonnement dans la table contacts_abonnement

         if ($statut != $statut_ancien) {
            // la commande en cours est payée ou en attente ?
            if ($statut_ancien == 'encours') {
               include_spip('inc/config');
               $config = lire_config('commandes');
               if (in_array($statut,$config['quand']) and $statut != 'encours') {
                  // on récupère le détail des commandes, si abonnement on récupère le numéro et id_ojbet (id abonnement)
                  if ($row = sql_allfetsel('id_objet,numero,id_commandes_detail', 'spip_commandes_details', 'id_commande = '.$id_commande.' and objet = "abonnement"')) {
                     foreach ($row as $r) {
                        // on récupère la durée de l'abonnement à partir de son id
                        $duree = sql_fetsel('duree,periode','spip_abonnements','id_abonnement = '.$r['id_objet']);
                        //spip_log($duree,'vacarme_pipeline');
                        $numero_debut = $r['numero'];
                        $id_commandes_detail = $r['id_commandes_detail'];
                        if ($duree['periode'] == 'mois') {
                           $numero_fin = (intval($duree['duree'])/3) + (intval($numero_debut)-1);
                           // dans contacts_abonnement, à partir de l'id commandes détails, on insère numéro début et fin
                           sql_updateq(
                              'spip_contacts_abonnements',
                              array('numero_debut'=>$numero_debut,'numero_fin'=>$numero_fin),
                              'id_commandes_detail = '.$id_commandes_detail
                           );
                        }
                     }
                  }
               }
            }
         }

         // on envoie les notifications
         $notifications = charger_fonction('notifications', 'inc', true);
         // on reprend sur le modèle de la notification dans instituer_commande, c'est-à-dire uniquement l'id du webmestre dans $options,
         // mais on pourrait, du coup, en ajouter d'autres puisqu'on fabrique notre propre notification. A voir plus tard.
         $options = array();
         $options['expediteur'] = 1; // webmestre
         // on envoie
         $notifications('vacarme_commande_vendeur', $id_commande, $options);
         $notifications('vacarme_commande_client', $id_commande, $options);
      }
      return $flux;
   }

   // ===========================
   // = pipeline affiche_milieu =
   // ===========================


?>
