<?php
$menus = array('adh_informations'=>'AdhInfo', 'adh_finance'=>'AdhFinance','adh_inscriptions'=>'AdhInscription','adh_visites'=>'AdhVisite',
	'adh_utilisateur'=>'Utilisateur',
	'ens_informations'=>'EnsInfo','ens_absences'=>'EnsAbsence','ens_cours'=>'EnsCours','ens_visites'=>'EnsVisite',
	'ens_utilisateur'=>'Utilisateur');
$vars['fiche'] = $menus[Sys::$CurrentMenu->Url];