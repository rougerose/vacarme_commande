<?php

// Sécurité
if (!defined('_ECRIRE_INC_VERSION')) return;

// Le prix HT d'un panier : addition des prix HT des objets liés
function prix_panier_ht($id_panier, $ligne){
	$fonction_ht = charger_fonction('ht', 'inc/prix');
	$prix_ht = 0;
	// le taux de tva (celle des produits, appliquée indifférement pour les abonnements et produits)
   include_spip('inc/config');
	$tva = lire_config('produits/taxe', 0) + 1;

	// On va chercher tous les objets liés
	$objets = sql_allfetsel('objet, id_objet, quantite', 'spip_paniers_liens', 'id_panier = '.$id_panier);

	// Pour chaque objet on va chercher son prix HT x sa quantité
   // les prix HT sont en fait en TTC dans la base. On retire donc la TVA à chaque objet
	if (is_array($objets)){
		foreach($objets as $objet){
			$prix_ht += round(($fonction_ht($objet['objet'], $objet['id_objet'])/$tva),2) * $objet['quantite'];
		}
	}

	return $prix_ht;
}

// Le prix TTC d'un panier : addition des prix TTC des objets liés
function prix_panier($id_panier, $prix_ht){
	$fonction_ttc = charger_fonction('ht', 'inc/prix');
	$prix = 0;

	// On va chercher tous les objets liés
	$objets = sql_allfetsel('objet, id_objet, quantite', 'spip_paniers_liens', 'id_panier = '.$id_panier);

	// Pour chaque objet on va chercher son prix TTC x sa quantité
	if (is_array($objets)){
		foreach($objets as $objet){
			$prix += $fonction_ttc($objet['objet'], $objet['id_objet']) * $objet['quantite'];
		}
	}

	return $prix;
}

?>
