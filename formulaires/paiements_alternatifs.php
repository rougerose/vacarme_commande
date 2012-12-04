<?php
   if (!defined('_ECRIRE_INC_VERSION')) return;

   function formulaires_paiements_alternatifs_charger_dist($options=array(),$retour) {
      $valeurs = array();
      $valeurs = array_merge($valeurs,$options);
      return $valeurs;
   }

   function formulaires_paiements_alternatifs_verifier_dist($options=array(),$retour) {
      $erreurs = array();
      foreach(array('commande_numero','id_auteur','id_commande') as $champ) {
         if (!_request($champ)) {
                  $erreurs[$champ] = "";
              }
          }
          if (count($erreurs)) {
              $erreurs['message_erreur'] = "Une erreur s'est produite. <a href='#'>Merci de nous contacter</a>.";
          }
      return $erreurs;
   }

   function formulaires_paiements_alternatifs_traiter_dist($options=array(),$retour) {
      $retours = array();
      $id_auteur = $options['id_auteur'];
      $reference = $options['commande_numero'];
      $id_commande = intval($options['id_commande']);
      $paiement = $options['type_paiement'];
      $statut_nouveau = 'attente'; // la commande est mise en attente de réception du règlement

      // paiement cheque ou virement ?
      if ($paiement) sql_updateq('spip_commandes',array('paiement' => $paiement),'id_commande='.$id_commande);

      $action = charger_fonction('instituer_commande', 'action');
      if ($action) {
         $action($id_commande."-".$statut_nouveau);
         $retours['message_ok'] = _T('vacarme_commande:message_ok_formulaire_paiement');
         if ($retour) $retour = parametre_url($retour,'r','2','&'); $retours['redirect'] = $retour;
         // suppression du panier
         include_spip('inc/paniers');
         // Si on trouve un panier pour le visiteur actuel
         if ($id_panier = paniers_id_panier_encours()){
            // On le supprime
            $supprimer = charger_fonction('supprimer_panier', 'action/');
            $supprimer($id_panier);
         }
      } else {
         $retours['message_erreur'] = _T('vacarme_commande:message_erreur_formulaire_paiement',array('numero_commande' => $reference));
         if ($retour) $retour = parametre_url($retour,'r','0','&'); $retours['redirect'] = $retour;
      }
      return $retours;
   }

?>