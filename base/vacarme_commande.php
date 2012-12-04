<?php
   if (!defined("_ECRIRE_INC_VERSION")) return;

   function vacarme_commande_declarer_tables_interfaces($interface){
      // Déclaration des alias de table
      $interface['table_des_tables']['commandes_transactions'] = 'commandes_transactions';
      return $interface;
   }

   function vacarme_commande_declarer_tables_principales($tables_principales) {
      $tables_principales['spip_contacts']['field']['organisation'] = "tinytext DEFAULT '' NOT NULL";
      $tables_principales['spip_contacts']['field']['service'] = "TINYTEXT NOT NULL DEFAULT ''";
      $tables_principales['spip_contacts']['field']['type_client'] = "TINYTEXT NOT NULL DEFAULT ''";

      $tables_principales['spip_contacts_abonnements']['field']['numero_debut'] = "BIGINT(21) NOT NULL";
      $tables_principales['spip_contacts_abonnements']['field']['numero_fin'] = "BIGINT(21) NOT NULL";

      $tables_principales['spip_abonnements']['field']['reference'] = "VARCHAR(255) NOT NULL DEFAULT ''";

      $tables_principales['spip_abonnements']['field']['cadeau'] = "INT(4) NOT NULL";

      $tables_principales['spip_paniers_liens']['field']['numero'] = "BIGINT(21) NOT NULL";

      $tables_principales['spip_commandes_details']['field']['numero'] = "BIGINT(21) NOT NULL";

      $tables_principales['spip_commandes']['field']['paiement'] = "tinytext not null default ''";

      // base version 0.4 : table commandes_transactions
      $commandes_transactions = array(
         "id_transaction"     => "BIGINT(21) NOT NULL", // identifier la transaction pour le paiement. cf. Bank
         "id_commande"        => "BIGINT(21) NOT NULL",
         "transaction_hash"   => "BIGINT(21) NOT NULL DEFAULT 0", // sécuriser les skels. cf. Bank
         "autorisation_id"    => "VARCHAR(55) NOT NULL DEFAULT ''", // numero d'autorisation de debit envoye par le presta bancaire. cf. Bank
         "montant_ht"         => "varchar(25) NOT NULL DEFAULT ''", // montant ht en euros
         "montant"            => "varchar(25) NOT NULL DEFAULT ''", // montant ttc en euros
         "mode"               => "varchar(25) NOT NULL DEFAULT ''", // mode de paiement (prestataire)
         "montant_regle"      => "varchar(25) NOT NULL DEFAULT ''", // montant regle (renvoye par le presta) en euros
         "date_paiement"      => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
         "statut"             => "varchar(25) NOT NULL DEFAULT ''", // commande, ok (ok si est passee en reglement)
         "reglee"             => "varchar(3) NOT NULL DEFAULT 'non'", // oui/non (non si reglement incomplet/partiel)
         "finie"              => "tinyint(1) NOT NULL DEFAULT 0",

         "message" => "TEXT NOT NULL DEFAULT ''", // message de retour a afficher

         "maj"                => "timestamp"
      );

      $commandes_transactions_cles = array(
         'PRIMARY KEY'     => 'id_transaction',
         'KEY id_commande' => 'id_commande'
      );

      $tables_principales['spip_commandes_transactions'] = array(
         'field'          => &$commandes_transactions,
         'key'            => &$commandes_transactions_cles,
         'join' => array(
            'id_transaction' => 'id_transaction',
            'id_commande' => 'id_commande'
         )
      );
      return $tables_principales;
   }
?>
