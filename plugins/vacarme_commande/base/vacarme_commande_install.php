<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');
include_spip('base/create');
include_spip('base/abstract_sql');


function vacarme_commande_upgrade($nom_meta_base_version,$version_cible){
   $current_version = 0.0;
   if ((!isset($GLOBALS['meta'][$nom_meta_base_version])) || (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
      include_spip('base/vacarme_commande');
      if ($current_version==0.0){
         creer_base();
         maj_tables(array(
            "spip_contacts",
            "spip_abonnements",
            "spip_contacts_abonnements",
            "spip_paniers_liens",
            "spip_commandes_details"));
         ecrire_meta($nom_meta_base_version,$current_version=$version_cible,'non');
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

   sql_alter("TABLE spip_commandes_details DROP numero");

   effacer_meta($nom_meta_base_version);
}
?>
