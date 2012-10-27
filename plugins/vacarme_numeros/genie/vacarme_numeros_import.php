<?php

   if (!defined("_ECRIRE_INC_VERSION")) return;

   function genie_vacarme_numeros_import_dist($t){
      spip_log("debut de la fonction d'importation",'vacarme_numeros_cron');
      include_spip('inc/distant');
      include_spip('inc/yaml');

      $url = 'http://www.vacarme.org/?page=export.yaml';
      $page = recuperer_page($url);
      $f = yaml_decode($page);

      // le taux de TVA utilisé par le plugin Produits
      $tva = lire_config('produits/taxe');

      $data = array();

      $processus = false;
      // pour le dev on met en true
      // $processus = true;
      foreach($f as $numeros) {
         foreach($numeros as $numero) {
            if(is_array($numero['sommaire'])) {
               $s = array();
               foreach($numero['sommaire'] as $sommaire) {
                  $s[] = serialize($sommaire);
               }
               $numero['sommaire'] = serialize($s);
            }

            if (isset($numero['id'])) {
               // Cette rubrique n'a pas déjà été importée ?
               $id = $numero['id'];
               if (!sql_countsel("spip_vacarme_numeros", array("id_rubrique_distante=".sql_quote($id)))) {
                  // traitement des données
                  $titre = $numero['numero'];
                  $saison = $numero['saison'];
                  $annee = $numero['annee'];
                  $prix = $numero['prix']; // on garde le prix TTC
                  $url = $numero['url'];
                  $logo = $numero['logo'];
                  $sommaire = $numero['sommaire'];

                  // insertion
                  sql_insertq("spip_vacarme_numeros",array('id_rubrique_distante' => $id,'numero' => $titre,'saison' => $saison,'annee' => $annee,'prix' => $prix, 'url' => $url, 'logo' => $logo, 'sommaire' => $sommaire, 'statut' => 'publie'));

                  spip_log("La rubrique «".$titre."» a été importée","vacarme_numeros_cron");
                  $processus = true;
               }
            }
         }
      }

      // une importation a été faite, les numéros sont enregistrés comme produits.
      if ($processus) {
         // les numeros importés en produits sont placés dans la rubrique 2/secteur 2
         $id_rubrique = 2;
         $id_secteur = 2;
         $row = array();

         // 1- on met les numeros en prop (ie qui viennent d'être importés)
         $result = sql_select("*", "spip_vacarme_numeros", "statut='publie'");
         if ($result) {
            while ($row = sql_fetch($result)){
               $id_numero = $row['id_numero'];
               $titre = $row['numero'];
               $prix = $row['prix'];
               $saison = $row['saison'];
               $annee = $row['annee'];
               $url = $row['url'];
               $logo = $row['logo'];
               // calcul de la référence du produit à ajouter
               $ref = preg_replace('/(vacarme\s*)(\d*)(\/*)(\d*)/i', 'v$2', $titre);
               // le sommaire
               $sommaire = unserialize($row['sommaire']);

               // 2- les numéros sont importés dans la table produits
               // test nécessaire (?) car sinon, un numéro est importé plusieurs fois de manière identique. Pas compris pourquoi.
               if (!$test = sql_fetsel("id_produit","spip_produits","titre=".sql_quote($ref))) {
                  $ladate = date('Y-m-d H:i:s');
                  $id_produit = sql_insertq('spip_produits', array('titre' => $titre, 'id_rubrique' => $id_rubrique, 'id_secteur' => $id_secteur, 'prix_ht' => $prix, 'statut' => 'publie', 'lang' => 'fr', 'date' => $ladate, 'reference' => $ref));
                  if ($id_produit) {
                     // 3- on importe le logo pour l'appliquer au produit
                     if ($logo){
                        $infos = recuperer_infos_distantes($logo);
                        if ($infos['extension']) {
                            $ext    = $infos['extension'];
                            $taille = $infos['taille'];
                            $fichier = $infos['fichier'];
                            $largeur = $infos['largeur'];
                            $hauteur = $infos['hauteur'];
                            // extension autorisee ?
                            $ext_autorisee = sql_fetsel("inclus", "spip_types_documents", "extension=" . sql_quote($ext) . " AND upload='oui'");
                            if ($ext_autorisee) {
                               $id_document = sql_insertq('spip_documents', array('extension'=>$ext, 'date'=> $ladate, 'fichier'=> $fichier, 'taille'=> $taille, 'largeur' => $largeur, 'hauteur' => $hauteur, 'mode' => 'document', 'distant' => 'oui', 'statut' => 'publie', 'date_publication' => $ladate));
                               sql_insertq('spip_documents_liens', array('id_document' =>$id_document, 'id_objet'=> $id_produit, 'objet'=> 'produit','vu'=> 'non'));
                               // on applique éventuellement la fonction de copie locale
                               // si l'url du fichier est relative ../IMG/distant
                               $f = sql_fetsel("fichier","spip_documents","id_document=$id_document");
                               $url_relative = strstr($f['fichier'],'../IMG/');
                               if ($url_relative) {
                                  include_spip('vacarme_numeros_fonctions');
                                  $traitement = vacarme_numeros_copie_locale($id_document);
                               }
                            }
                        }
                     } // fin importation logo
                     // 4- le sommaire détaillé d'un numéro
                     sql_insertq('spip_vacarme_numeros_details', array('id_produit' => $id_produit, 'saison' => $saison, 'annee' => $annee, 'url' => $url));
                     if (is_array($sommaire)) {
                        foreach($sommaire as $data) {
                           $data = unserialize($data);
                           $data_titre = $data['titre'];
                           $data_url = $data['url'];
                           $resume = $data['resume'];
                           sql_insertq('spip_vacarme_numeros_sommaires', array('id_produit' => $id_produit, 'titre' => $data_titre, 'url' => $data_url, 'resume' => $resume));
                        }
                     } // fin sommaire
                  } // fin test produit
               } // fin importation produit
               // on dépublie les numéros qui viennent d'être transformés en produits
               sql_updateq('spip_vacarme_numeros', array('statut' => "refuse"), 'id_numero=' . intval($id_numero));
            }
         }
      }

      spip_log("fin de la fonction d'importation",'vacarme_numeros_cron');
      return 1;

   }


?>