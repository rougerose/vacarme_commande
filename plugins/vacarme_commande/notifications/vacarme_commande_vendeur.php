<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_vacarme_commande_vendeur_destinataires_dist($id_commande, $options) {
	include_spip('inc/config');
	$config = lire_config('commandes');
	return $config['vendeur_'.$config['vendeur']];

}

function notifications_vacarme_commande_vendeur_contenu_dist($id, $options, $destinataire, $mode) {
   $message = array();

   $id_commande = intval($id);
   $row = sql_fetsel("statut,reference,paiement,id_auteur","spip_commandes","id_commande=$id_commande");
   $row['identite'] = sql_fetsel("nom,prenom,type_client","spip_contacts_liens LEFT JOIN spip_contacts USING(id_contact)",array("objet =".sql_quote('auteur'),"id_objet =".$row['id_auteur']));
   $row['pays'] = sql_fetsel("pays","spip_adresses_liens LEFT JOIN spip_adresses USING(id_adresse)",array("objet =".sql_quote('auteur'),"id_objet =".$row['id_auteur']));


   // le client paie la TVA ?
   include_spip('inc/vacarme_commande');
   if (function_exists('tva_applicable')) {
      $tva = tva_applicable($row['identite']['type_client'],$row['pays']['pays']);
   }
   // le détail de la commande
   $details = sql_allfetsel("prix_unitaire_ht,taxe,quantite","spip_commandes_details","id_commande=57");
   foreach($details as $d) {
      $total = $d['prix_unitaire_ht']*$d['quantite'];
      if ($tva) {
         $total = $total * ($d['taxe'] + 1);
      }
      $total +=$total;
      $total = round($total,2);
   }
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
   $message['court'] = _T('vacarme_commande:mail_sujet_paiement_vacarme',array('numero_commande'=>$row['reference']));

   return $message;
}


?>
