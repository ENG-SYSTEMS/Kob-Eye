<?php

switch(Sys::$CurrentMenu->Url) {
	
	case 'adh_site_enseignant':
		$vars['fiche'] = 'Cadref/Print/PrintEnseignant/?adherent='.$this->Id;
		break;
	
	default:
		$menus = array(
			'adh_informations'=>'AdhInfo',
			'adh_finance'=>'AdhFinance',
			'adh_inscriptions'=>'AdhPanier',  //'AdhInscription',
			'adh_visites'=>'AdhVisite',
			'adh_visites'=>'AdhVisite',
			'adh_documents/adh_carte'=>'AdhCarte',
			'adh_documents/adh_attestation'=>'AdhAttestation',
			'adh_message'=>'sendMessage',
			'adh_panier'=>'AdhPanier',
			'adh_site_adherent'=>'AdhListeAdherent',
			'ens_informations'=>'EnsInfo',
			'ens_absences'=>'EnsAbsence',
			'ens_cours'=>'EnsCours',
			'ens_visites'=>'EnsVisite',
			'ens_message'=>'sendMessage'	
			);
		$vars['fiche'] = 'Cadref/AccesPublic/'.$menus[Sys::$CurrentMenu->Url];
}
