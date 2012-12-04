<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) return;

// calcul du prix TTC d'un abonnement
function prix_abonnement($id_objet, $prix_ht){
	$prix = $prix_ht;
   // la taxe ne peut être définie en config dans ecrire pour le moment
   /*if(_TVA_ABONNEMENT){
      $taxe = _TVA_ABONNEMENT;
   } else {
      // TVA 7% par défaut
      $taxe = 0.07;
   }
   $prix = $prix_ht*(1+$taxe);*/

	return $prix;
}

?>