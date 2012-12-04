<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_vacarme_editer_email_auteur_charger_dist($id_auteur='new'){
   $valeurs = formulaires_editer_objet_charger('auteur', $id_auteur, '', '', '', '');
   return $valeurs;
}

function formulaires_vacarme_editer_email_auteur_verifier_dist($id_auteur='new'){
   $erreurs = formulaires_editer_objet_verifier('auteur', $id_auteur, array('email'));

   if ($email = _request('email')) {
      include_spip('inc/filtres');
      if (!email_valide($email)) {
         $erreurs['email'] = _T('form_email_non_valide');
      }
   }

   return $erreurs;
}

function formulaires_vacarme_editer_email_auteur_traiter_dist($id_auteur='new'){
   $retours = array();
   $retours = formulaires_editer_objet_traiter('auteur', $id_auteur, '', '', '', '');
   $retours['editable'] = true;
   $retours['message_ok'] = _T('vacarme_commande:message_modification_enregistree');
   return $retours;
}

?>
