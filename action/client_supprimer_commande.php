<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_client_supprimer_commande_dist() {
   // Le client demande à supprimer sa commande (depuis la page d'annulation)
   $securiser_action = charger_fonction('securiser_action', 'inc');
   $id_commande = $securiser_action();

   // suppression de la commande
   if ($id_commande = intval($id_commande)) {
      sql_delete('spip_commandes', 'id_commande=' . $id_commande);
      sql_delete('spip_commandes_details', 'id_commande=' . $id_commande);
   }
   
   // le paiement n'a pas été fait. Il faut supprimer le panier
   include_spip('inc/paniers');
   // Si on trouve un panier pour le visiteur actuel
   if ($id_panier = paniers_id_panier_encours()){
      // On le supprime
      $action = charger_fonction('supprimer_panier', 'action/');
      $action($id_panier);
   }

}

?>
