<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_vacarme_commande_vendeur_destinataires_dist($id_commande, $options) {
   // les notifications vendeurs sont expédiées à auteur 1 (webmaster) + auteur 16 (abonnement)
   //return array(1,16);
   return array(1);
   /*
      TODO rétablir envoi à l'adresse abonnement
   */

}

function notifications_vacarme_commande_vendeur_contenu_dist($id, $options, $destinataire, $mode) {
   $message = array();

   $id_commande = intval($id);
   $row = sql_fetsel("statut,reference,paiement,id_auteur","spip_commandes","id_commande=$id_commande");
   $row['identite'] = sql_fetsel("nom,prenom,type_client","spip_contacts_liens LEFT JOIN spip_contacts USING(id_contact)",array("objet =".sql_quote('auteur'),"id_objet =".$row['id_auteur']));
   $row['pays'] = sql_fetsel("pays","spip_adresses_liens LEFT JOIN spip_adresses USING(id_adresse)",array("objet =".sql_quote('auteur'),"id_objet =".$row['id_auteur']));

   // le client paie la TVA ?
   $tva_applicable = charger_fonction('tva_applicable','inc',true);
   $tva_applicable($row['identite']['type_client'],$row['pays']['pays']);

   // les totaux de la commande
   $total = sql_fetsel("montant_ht,montant","spip_commandes_transactions","id_commande=".$id_commande);
   #spip_log("commande ".$id_commande.' montants : '.$total['montant'],'vacarme_debug');
   $total = ($tva_applicable)?$total['montant']:$total['montant_ht'];

   $url_commande = generer_url_ecrire('commande_voir',"id_commande=$id_commande");
   $msg = _T('vacarme_commande:mail_paiement_vacarme', array(
      'numero_commande' => $row['reference'],
      'prenom' => $row['identite']['prenom'],
      'nom' => $row['identite']['nom'],
      'total' => $total.' euros',
      'statut' => $row['statut'],
      'paiement' => $row['paiement'],
      'url_commande' => $url_commande
      ));

   $message['texte'] = $msg;
   $message['court'] = _T('vacarme_commande:mail_sujet_paiement_vacarme',array('numero_commande'=>$row['reference'],'statut_commande'=>$row['statut']));

   return $message;
}


?>
