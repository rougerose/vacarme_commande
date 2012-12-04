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

include_spip('inc/date');

/**
 * il faut avoir un id_transaction et un transaction_hash coherents
 * pour se premunir d'une tentative d'appel exterieur
 *
 *
 * @return array
 */
function presta_cheque_call_response(){

	// recuperer la reponse en post et la decoder
	$id_transaction = _request('id_transaction');
	$transaction_hash = _request('hash');
   $mode = 'cheque';

	if (!$row = sql_fetsel('*','spip_commandes_transactions','id_transaction='.intval($id_transaction))){
		spip_log("id_transaction $id_transaction non trouve",'gratuit.'._LOG_ERREUR);
		return array($id_transaction,false);
	}
	if ($transaction_hash!=$row['transaction_hash']){
		spip_log("id_transaction $id_transaction, hash $transaction_hash non conforme",'gratuit.'._LOG_ERREUR);
		return array($id_transaction,false);
	}

	if ($row['statut']=='ok') {
		spip_log("Check:Transaction $id_transaction deja validee","cheque");
		return array($id_transaction,true);
	}
   // si pages publiques, c'est le client qui demande à payer par chèque
   if (!test_espace_prive()) {
      $bank_recoit_notification = charger_fonction('recoit_notification','bank');
		return bank_recoit_notification($id_transaction,$transaction_hash,$mode); //retourne $id_transaction,true
   }
	return array($id_transaction,false);
}
?>