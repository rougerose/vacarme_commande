<?php

   if (!defined('_ECRIRE_INC_VERSION')) return;

   function inc_ipnlistener_recuperer_post_https($datas='') {
      /**
      *  PHP-PayPal-IPN Example
      *
      *  This shows a basic example of how to use the IpnListener() PHP class to
      *  implement a PayPal Instant Payment Notification (IPN) listener script.
      *
      *  For a more in depth tutorial, see my blog post:
      *  http://www.micahcarrick.com/paypal-ipn-with-php.html
      *
      *  This code is available at github:
      *  https://github.com/Quixotix/PHP-PayPal-IPN
      *
      *  @package    PHP-PayPal-IPN
      *  @author     Micah Carrick
      *  @copyright  (c) 2011 - Micah Carrick
      *  @license    http://opensource.org/licenses/gpl-3.0.html
      */

      // instantiate the IpnListener class
      include_spip('lib/ipnlistener');

      $erreur = false;
      $listener = new IpnListener();

      /*
      When you are testing your IPN script you should be using a PayPal "Sandbox"
      account: https://developer.paypal.com
      When you are ready to go live change use_sandbox to false.
      */
      $listener->use_sandbox = true;

      try {
         $listener->requirePostMethod();
         $verified = $listener->processIpn($datas);
      } catch (Exception $e) {
         $erreur = true;
         $erreur_msg = $e->getMessage();
         spip_log("erreur exception ".$erreur_message,"paypal");
      }
      spip_log("valeur verifie ".$verified,"paypal");

      if ($verified) $response = 'VERIFIED';
      else $response = 'INVALID';

      return array($response,$erreur,$erreur?$erreur_msg:'');
   }
?>