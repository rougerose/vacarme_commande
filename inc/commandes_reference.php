<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) return;

// Surcharge de la fonction _dist de commandes
// pour un numéro de commande (référence) plus explicite et plus lisible
function inc_commandes_reference($id_auteur=0){
      $reference = date(Ymd)."-".intval($id_auteur);
   return $reference;
}

?>
