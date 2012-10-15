<?php
   if (!defined("_ECRIRE_INC_VERSION")) return;

   function inc_inserer_transaction_dist($id_commande=0,$reference,$total_ht,$total_ttc) {
      include_spip('base/abstract_sql');
      if ($id_commande == 0 OR $reference == 0) {
         spip_log("Insérer transaction, données manquantes : id_commande ".$id_commande." et reference ".$reference,'vacarme_debug');
         return 0;
      }
      $id_transaction = sql_insertq('spip_commandes_transactions',array('id_commande' => $id_commande));
      if (!$id_transaction) {
         spip_log ('Insérer transaction, donnée manquante : id_transaction '.$id_transaction,'vacarme_debug');
         return 0;
      }
      // un hash pour securiser l'acces aux transactions
      $transaction_hash = substr(md5("$id_transaction:$reference:$date"),0,8);
      $transaction_hash = hexdec("0x".$transaction_hash);

      if (!sql_updateq("spip_commandes_transactions",array('statut'=>'commande','transaction_hash'=>$transaction_hash,'montant_ht'=>$total_ht,'montant'=>$total_ttc),"id_transaction=".intval($id_transaction))) {
         spip_log('Insérer transaction, ajout hash impossible','vacarme_debug');
         return 0;
      }
      pipeline('post_insertion',array(
         'args' => array(
            'table' => 'spip_commandes_transactions',
            'id_objet' => $id_transaction
         ),
         'data' => ''
      ));
      return $id_transaction;
   }
?>