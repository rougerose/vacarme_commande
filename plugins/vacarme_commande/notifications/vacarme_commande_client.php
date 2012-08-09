<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function notifications_vacarme_commande_client_destinataires_dist($id_commande, $options) {
	$id_auteur=sql_getfetsel("id_auteur","spip_commandes","id_commande=".$id_commande);
	return array($id_auteur);
}

function notifications_vacarme_commande_client_contenu_dist($id, $options, $destinataire, $mode) {
   $message = array();

   // en l'état de l'appel de la notification depuis commandes/action/editer_commande
   // la fonction récupère uniquement $id : id_commande et options : id_auteur expediteur


   /* récupération des infos nécessaires sur la commande et le client :
         - statut
         - reference
         - type de paiement
         - id_auteur (ce serait utile qu'il soit dans $destinataire)
         - civilité, nom, prénom, type_client
         - son pays de résidence
   */
   $id_commande = intval($id);
   $row = sql_fetsel("statut,reference,paiement,id_auteur","spip_commandes","id_commande=$id_commande");

   $row['identite'] = sql_fetsel("civilite,nom,prenom,type_client",
   "spip_contacts_liens LEFT JOIN spip_contacts USING (id_contact)",
   array("objet = ".sql_quote('auteur'),"id_objet = ".$row['id_auteur']));
   $row['pays'] = sql_fetsel("pays","spip_adresses_liens LEFT JOIN spip_adresses USING (id_adresse)",array("objet =".sql_quote('auteur'),"id_objet =".$row['id_auteur']));

   // le client paie la TVA ?
   include_spip('inc/vacarme_commande');
   if (function_exists('tva_applicable')) {
      $tva = tva_applicable($row['identite']['type_client'],$row['pays']['pays']);
   }

   // le détail de la commande
   $details = sql_allfetsel("prix_unitaire_ht,taxe,quantite","spip_commandes_details","id_commande=$id_commande");
   foreach($details as $d) {
      $total += ($tva) ? ($d['prix_unitaire_ht']*$d['quantite'])*($d['taxe']+1) : ($d['prix_unitaire_ht']*$d['quantite']);
   }
   $total = round($total,2);

   // Elements nécessaires au message

   // 1-les civilités
   $genre = ($row['identite']['civilite'] == 'monsieur') ? _T('vacarme_commande:cher') : _T('vacarme_commande:chere');
   $msg = "$genre ".$row['identite']['prenom']." ".$row['identite']['nom'].",\n\n";

   // 2-l'url de la commande du client sur son compte
   $url_commande = generer_url_public('compte','section=commandes&id_commande='.$id_commande, true);

   // 3-quel est le statut de la commande : attente ou payé ?
   if ($row['statut'] == 'attente') {
      // cheque ou virement ?
      if ($row['paiement'] == 'cheque') {
         $type_paiement = _T('vacarme_commande:paiement_cheque');
         $intro_mail = _T('vacarme_commande:mail_intro_paiement_alter',array('type_paiement' => $type_paiement));
         $corps_mail = _T('vacarme_commande:mail_corps_paiement_cheque',array('total' =>$total,'numero_commande'=>$row['reference']));
      }
      if ($row['paiement'] == 'virement') {
         $type_paiement = _T('vacarme_commande:paiement_virement');
         $intro_mail = _T('vacarme_commande:mail_intro_paiement_alter',array('type_paiement' => $type_paiement));
         $corps_mail = _T('vacarme_commande:mail_corps_paiement_virement',array('total' =>$total,'numero_commande'=>$row['reference']));
      }
   }
   if ($row['statut'] == 'paye') {
      if ($row['paiement'] == 'paypal') {
         $intro_mail = _T('vacarme_commande:mail_intro_paiement_paypal',array('numero_commande' => $row['reference'],'total' => $total));
         $corps_mail = _T('vacarme_commande:mail_corps_paiement_confirmation');
      }
      if (preg_match('/cheque|virement/i',$row['paiement'])) {
         $type_paiement = ($row['paiement'] == 'cheque') ? _T('vacarme_commande:paiement_cheque') : _T('vacarme_commande:paiement_virement');
         $intro_mail = _T('vacarme_commande:mail_intro_paiement_alter_confirmation',array('numero_commande' => $row['reference'],'type_paiement' => $type_paiement));
         $corps_mail = _T('vacarme_commande:mail_corps_paiement_confirmation');
      }
   }

   // Le contenu du message
   $msg .= $intro_mail
      . "\n\n"
      . $corps_mail."\n\n"
      . _T('vacarme_commande:mail_lien_commande',array('url_compte_commande'=> $url_commande))."\n\n"
      . _T('vacarme_commande:mail_fin_paiement')."\n\n";

   // include_spip('inc/filtres');
   //    $message_texte = supprimer_tags($msg);
   //    $message_texte = filtrer_entites($message_texte);

   // uniquement un message texte brut
   // la version html, plus tard
   $message['texte'] = $msg;
   $message['court'] = _T('vacarme_commande:mail_sujet_paiement',array('numero_commande'=>$row['reference']));

   return $message;
}

?>
