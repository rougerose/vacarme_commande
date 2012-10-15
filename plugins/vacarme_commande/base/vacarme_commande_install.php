<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');
include_spip('base/create');
include_spip('base/abstract_sql');


function vacarme_commande_upgrade($nom_meta_base_version,$version_cible) {
   $current_version = 0.0;
   if ((!isset($GLOBALS['meta'][$nom_meta_base_version])) || (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)) {
      include_spip('base/vacarme_commande');
      if ($current_version==0.0){
         creer_base();
         maj_tables(array(
            "spip_contacts",
            "spip_abonnements",
            "spip_contacts_abonnements",
            "spip_paniers_liens",
            "spip_commandes",
            "spip_commandes_details"));
            ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
         }
         if (version_compare($current_version,"0.2","<")){
            maj_tables('spip_commandes');
            ecrire_meta($nom_meta_base_version, $current_version="0.2");
         }
         if (version_compare($current_version,"0.3","<")){
            creer_base(); // ajout de spip_commandes_transactions
            ecrire_meta($nom_meta_base_version, $current_version="0.3");
         }
      }
   }

function vacarme_commande_vider_tables($nom_meta_base_version) {
   sql_alter("TABLE spip_contacts DROP organisation");
   sql_alter("TABLE spip_contacts DROP service");
   sql_alter("TABLE spip_contacts DROP type_client");

   sql_alter("TABLE spip_contacts_abonnements DROP numero_debut");
   sql_alter("TABLE spip_contacts_abonnements DROP numero_fin");

   sql_alter("TABLE spip_abonnements DROP reference");
   sql_alter("TABLE spip_abonnements DROP cadeau");

   sql_alter("TABLE spip_paniers_liens DROP numero");

   sql_alter("TABLE spip_commandes DROP paiement");

   sql_alter("TABLE spip_commandes_details DROP numero");

   sql_drop_table('spip_commandes_transactions');

   effacer_meta($nom_meta_base_version);
}
?>
