<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_vacarme_editer_password_auteur_charger_dist($id_auteur='new'){
   $valeurs = array(
      'new_pass' => '',
      'new_pass2' => ''
   );
   return $valeurs;
}

function formulaires_vacarme_editer_password_auteur_verifier_dist($id_auteur='new'){
   $auteur = auteur_source_login(intval($id_auteur));
   $auth_methode = ($auteur['source'] ? $auteur['source'] : 'spip');
   include_spip('inc/auth');
   include_spip('inc/autoriser');

   if ($p = _request('new_pass')) {
      if ($p != _request('new_pass2')) {
         $erreurs['new_pass'] = _T('info_passes_identiques');
         $erreurs['new_pass2'] = _T('info_passes_identiques');
         $erreurs['message_erreur'] .= _T('info_passes_identiques');
      }
      elseif ($err = auth_verifier_pass($auth_methode, $auteur['login'], $p, $id_auteur)){
         $erreurs['new_pass'] = 'password trop court';
         $erreurs['new_pass2'] = _T('info_passes_identiques');
         $erreurs['message_erreur'] .= 'password trop court';
      }
   }
   return $erreurs;
}

function formulaires_vacarme_editer_password_auteur_traiter_dist($id_auteur='new'){
   $retours = array();
   $p = _request('new_pass');

   $auteur = auteur_source_login(intval($id_auteur));
   $auth_methode = ($auteur['source'] ? $auteur['source'] : 'spip');
   $np = auth_modifier_pass($auth_methode, $auteur['login'], $p, $id_auteur);
   $retours['message_ok'] = _T('vacarme_commande:message_modification_enregistree');
   $retours['editable'] = true;
   return $retours;
}

function auteur_source_login($id_auteur) {
   $auteur = array();
   $auteur = sql_fetsel('login,source','spip_auteurs','id_auteur='.intval($id_auteur));
   return $auteur;
}

?>
