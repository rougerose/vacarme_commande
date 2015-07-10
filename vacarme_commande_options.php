<?php
if (!defined("_ECRIRE_INC_VERSION")) return;
// entetes silencieux
$spip_header_silencieux = 1;


// debug du plugin
define('_DEBUG_VACARME',true);

define('_RENOUVELLE_ALEA',3600); // utile de modifier l'aléa pour les sessions ?

// TVA des abonnements
define('_TVA_ABONNEMENT',0.055);

// remplacer la fonction prix_formater du plugin prix qui ne marche pas comme attendu
function prix_format($prix){
   // sur joyent : le point est converti en virgule,
   // mais le symbole euro n'est pas affiché (EUR uniquement)
   setlocale(LC_ALL,'fr_FR.UTF-8');
   return $prix;
}

$debut_intertitre = "\n<h2 class=\"spip\">\n";
$fin_intertitre = "</h2>\n";

// surcharge fonction plugin prix/inc/inc_prix_ht_dist
// la surcharge consiste simplement à ne pas faire arrondi = 2
// pour éviter des écarts de quelques centimes entre le total d'un panier et le total d'une commande
function inc_prix_ht($type_objet, $id_objet, $arrondi=''){
	$prix_ht = 0;
	// Cherchons d'abord si l'objet existe bien
	if ($type_objet
		and $id_objet = intval($id_objet)
		and include_spip('base/connect_sql')
		and $type_objet = objet_type($type_objet)
		and $table_sql = table_objet_sql($type_objet)
		and $cle_objet = id_table_objet($type_objet)
		and $ligne = sql_fetsel('*', $table_sql, "$cle_objet = $id_objet")
	){
		// Existe-t-il une fonction précise pour le prix HT de ce type d'objet : prix_ht_<objet>() dans prix/<objet>.php
		if ($fonction_ht = charger_fonction('ht', "prix/$type_objet", true)){
			// On passe la ligne SQL en paramètre pour ne pas refaire la requête
			$prix_ht = $fonction_ht($id_objet, $ligne);
		}
		// S'il n'y a pas de fonction, regardons s'il existe des champs normalisés, ce qui évite d'écrire une fonction pour rien
		elseif ($ligne['prix_ht'])
			$prix_ht = $ligne['prix_ht'];
		elseif ($ligne['prix'])
			$prix_ht = $ligne['prix'];

		// Enfin on passe dans un pipeline pour modifier le prix HT
		$prix_ht = pipeline(
			'prix_ht',
			array(
				'args' => array(
					'id_objet' => $id_objet,
					'type_objet' => $type_objet,
					'prix_ht' => $prix_ht
				),
				'data' => $prix_ht
			)
		);
	}

	// Si on demande un arrondi, on le fait
	if ($arrondi)
		$prix_ht = round($prix_ht, $arrondi);

	return $prix_ht;
}


// surcharge pour modification du mail d'envoi lors de l'inscription
// http://doc.spip.org/@envoyer_inscription_dist
function envoyer_inscription($desc, $nom, $mode, $id) {
   $nom_site_spip = nettoyer_titre_email($GLOBALS['meta']["nom_site"]);
   $adresse_site = $GLOBALS['meta']["adresse_site"];
   if ($mode == '6forum') {
      // $adresse_login = generer_url_public('login');
      // http://localhost:8888/vacarme_commande/spip.php?page=compte&section=identification
      $adresse_login = generer_url_public('compte','section=identification');
      $msg = 'vacarme_commande:inscription_message_voici1';
   } else {
      $adresse_login = $adresse_site .'/'. _DIR_RESTREINT_ABS;
      $msg = 'form_forum_voici2';
   }

   $msg  = _T('vacarme_commande:inscription_message_auto')."\n\n"
         . _T('form_forum_bonjour', array('nom'=>$nom))."\n\n"
         . _T($msg, array('nom_site_spip' => $nom_site_spip, 'adresse_site' => $adresse_site . '/')) . "\n\n- "
         . _T('form_forum_login')." " . $desc['login'] . "\n- "
         . _T('form_forum_pass'). " " . $desc['pass'] . "\n-"
         . _T('vacarme_commande:inscription_message_adresse_login')." ".$adresse_login."\n\n"
         . _T('vacarme_commande:inscription_message_fin')."\n";

   return array("[$nom_site_spip] "._T('form_forum_identifiants'), $msg);
}


?>