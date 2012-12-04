<?php
/*
 * Paiement Bancaire
 * module de paiement bancaire multi prestataires
 * stockage des transactions
 *
 * Auteurs :
 * Cedric Morin, Nursit.com
 * (c) 2012 - Distribue sous licence GNU/GPL
 *
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Recevoir la notification d'un paiement par chèque ou virement. Pour les autres presta voir presta/$presta/inc/
 *
 * @return array
 */
function bank_recoit_notification($id_transaction,$transaction_hash,$mode,$notifier=true){
   $mode_autorises = array('cheque','virement');
   if (!$row_prec = sql_fetsel("*","spip_commandes_transactions","id_transaction=".intval($id_transaction)." AND transaction_hash=".sql_quote($transaction_hash))) {
      spip_log("id_transaction invalide : $id_transaction/hash$transaction_hash",'paiement_alter');
		return array(0,false);
   }
	if ($row_prec['reglee']=='oui')
		return array($id_transaction,true); // cette transaction a deja ete reglee. double entree, on ne fait rien

   if (!$mode or !in_array($mode,$mode_autorises)) {
		spip_log("Transaction $id_transaction:pas de mode paiement renseigné (chèque ou virement)",'paiement_alter');
		$message="Une erreur s'est produite pendant l'enregistrement de votre commande";
		return bank_echec_transaction($id_transaction,$message); // erreur sur la transaction
   }

   /*
      TODO traiter les autres possibilités d'échec ou de traitement de la commande à réception du chèque par un admin ? Pour le moment, on enregistre seulement le fait que le client indique vouloir payer par chèque.
   */

	$message = _T('vacarme_commande:message_ok_formulaire_paiements_alter');

   // commande en attente du reglement
	sql_update("spip_commandes_transactions",
		array(
		"mode"=>sql_quote($mode),
		"statut"=>sql_quote('attente'),
      "message" => sql_quote($message)
		),
		"id_transaction=".intval($id_transaction)
	);
	spip_log("cheque_response : id_transaction $id_transaction, attente",$mode);

   // mise à jour de la commande correspondante
   $id_commande = sql_getfetsel("id_commande","spip_commandes_transactions","id_transaction=".intval($id_transaction));
   sql_updateq("spip_commandes",array("paiement" => $mode, "statut" => "attente"),"id_commande=".intval($id_commande));

   // mise à jour de l'abonnement s'il existe
   $id_commandes_detail = sql_allfetsel('id_commandes_detail','spip_commandes_details','id_commande='.$id_commande.' and objet="abonnement"');
   if ($id_commandes_detail) {
      foreach ($id_commandes_detail as $k) {
         foreach($k as $id) {
            sql_updateq("spip_contacts_abonnements",array('statut_abonnement' => 'attente'),'id_commandes_detail='.$id);
         }
      }
   }

	// ensuite un pipeline de traitement, notification etc...
	$message = pipeline('bank_traiter_reglement',array(
		'args'=>array(
			'id_transaction'=>$id_transaction,
			'new'=>$row_prec['reglee']!=='oui',
			'confirm'=>$row_prec['reglee']=='oui',
			'notifier'=>$notifier,
			'avant'=>$row_prec),
		'data'=>$message)
	);

	return array($id_transaction,true);
}

/**
 * Renseigner une transaction echouee
 *
 * @param int $id_transaction
 * @param string $message
 * @return array
 */
function bank_echec_transaction($id_transaction,$message){
	sql_updateq("spip_commandes_transactions",
	  array('message'=>$message,'statut'=>'echec'),
	  "id_transaction=".intval($id_transaction)
	);
	return array($id_transaction,false); // erreur sur la transaction
}

?>