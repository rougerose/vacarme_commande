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

/* Paypal Simple ----------------------------------------------------------- */
// parametres pour paiement standard paypal
if (!defined('_PAYPAL_BUSINESS_USERNAME'))
	define('_PAYPAL_BUSINESS_USERNAME', '539GDBZ3N63XU');
if (!defined('_PAYPAL_URL_SERVICES'))
	define('_PAYPAL_URL_SERVICES', 'https://www.paypal.com/cgi-bin/webscr');

?>