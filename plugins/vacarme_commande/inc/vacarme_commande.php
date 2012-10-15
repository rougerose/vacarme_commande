<?php
   if (!defined("_ECRIRE_INC_VERSION")) return;

   function tva_applicable ($type_client,$pays) {
      // tva applicable si c'est un particulier qui réside en UE. Particulier hors UE et Organisation (dans et hors UE), pas de tva.
      // la liste des pays de l'UE
      $UE = array('AT','BE','BG','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','ES','SE','UK');
      $tva = (($type_client == 'particulier') AND (in_array($pays,$UE)) ) ? true : false;
      return $tva;
   }
?>