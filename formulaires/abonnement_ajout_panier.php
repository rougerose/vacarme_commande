<?php
   if (!defined('_ECRIRE_INC_VERSION')) return;

   function formulaires_abonnement_ajout_panier_charger_dist($id_abonnement='',$retour=''){
      $valeurs = array(
         'id_abonnement' => $id_abonnement,
         'numero' => ''
      );
      return $valeurs;
   }

   function formulaires_abonnement_ajout_panier_verifier_dist($id_abonnement='',$retour=''){
      $erreurs = array();
      foreach(array('numero','id_abonnement') as $champ) {
         if (!_request($champ)) {
            $erreurs[$champ] = "Cette information est obligatoire !";
         }
      }
      if (count($erreurs)) {
         $erreurs['message_erreur'] = "Une erreur est présente dans votre saisie";
      }
      return $erreurs;
   }

   function formulaires_abonnement_ajout_panier_traiter_dist($id_abonnement='',$retour=''){
      $numero = intval(_request('numero'));
      // on ajoute les données attendues : objet-id_objet-quantité-numéro de départ d'abonnement
      $arg = 'abonnement-'.$id_abonnement.'-1-'.$numero;
      $remplir_panier = charger_fonction('remplir_panier', 'action/');
      $remplir_panier($arg);
      $res['editable'] = true;
      return $res;
   }

?>
