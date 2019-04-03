<?php

class Adherent extends genericClass {
	function Save() {
		$id = $this->Id;
		$annee = Cadref::$Annee;

		if(!$id) {
			$a = Sys::getOneData('Cadref', 'Adherent', 0, 1, 'DESC', 'Numero');
			$this->Numero = sprintf('%06d', intval($a->Numero) + 1);
			$this->Inscription = $annee;
		}
		$this->NomPrenom = $this->Nom.' '.$this->Prenom;
		$this->Utilisateur = Sys::$User->Initiales;
		$this->DateModification = time();
		return parent::Save();
	}

	function GetFormInfo($annee) {
		$c = !$this->CheckCertificat();
		$s = $this->getOneChild('AdherentAnnee/Annee='.$annee);
		return array('Cotisation'=>$s->Cotisation, 'Cours'=>$s->Cours, 'Visites'=>$s->Visites, 'Reglement'=>$s->Reglement, 'Differe'=>$s->Differe,
			'Regularisation'=>$s->Regularisation, 'Solde'=>$s->Solde, 'NotesAnnuelles'=>$s->NotesAnnuelles, 'Adherent'=>$s->Adherent,
			'ClasseId'=>$s->ClasseId, 'AntenneId'=>$s->AntenneId, 'CotisationAnnuelle'=>Cadref::$Cotisation, 'certifInvalide'=>$c,
			'Soutien'=>$s->Soutien);
	}

	// $mode :
	// 0 => save adherent (appel par Adherent/Save.twig.php)
	// 1 => save inscription ou reservation	
	// 2 => save reglement (appel direct)
	function SaveAnnee($data, $mode) {
		$annee = Cadref::$Annee;
		$cours = 0;
		$visit = 0;
		$regle = 0;
		$diffe = 0;
		$ins = $this->getChildren('Inscription/Annee='.$annee);
		foreach($ins as $in) {
			if(!$in->Attente && !$in->Supprime) $cours += $in->Prix - $in->Reduction - $in->Soutien;
		}
		$vis = $this->getChildren('Reservation/Annee='.$annee);
		foreach($vis as $vi) {
			if(!$vi->Attente && !$vi->Supprime) $visit += $vi->Prix - $vi->Reduction + $vi->Assurance;
		}
		$rgs = $this->getChildren('Reglement/Annee='.$annee);
		foreach($rgs as $rg) {
			if($rg->Supprime) continue;
			if($rg->Differe) {
				if($rg->Encaisse) $regle += $rg->Montant;
				else $diffe += $rg->Montant;
			} else $regle += $rg->Montant;
		}

		$a = $this->getOneChild('AdherentAnnee/Annee='.$annee);
		if(!$a) {
			$a = genericClass::createInstance('Cadref', 'AdherentAnnee');
			$a->addParent($this);
			$a->Annee = $annee;
			$a->Numero = $this->Numero;
		}

		if($mode == 0) {
			$a->Adhrent = $data->Adherent;
			$a->ClasseId = $data->ClasseId;
			$a->AntenneId = $data->AntenneId;
			$a->NotesAnnuelles = $data->NotesAnnuelles;
		}
		else if($mode == 1) {
			if(!$a->Cotisation && $data->Cotisation) $a->DateCotisation = time();
			$a->Cotisation = $data->Cotisation ? $data->Cotisation : 0;
			$this->Cotisation = $data->Cotisation;
			$this->Save();
		}
		$a->Regularisation = $data->Regularisation ? $data->Regularisation : 0;
		$a->Cours = $cours;
		$a->Visites = $visit;
		$a->Reglement = $regle;
		$a->Differe = $diffe;
		$a->Solde = $a->Cotisation + $cours /*+ $visit*/ - $regle - $diffe + $a->Regularisation;
		$a->Save();

		return true;
	}

	private function saveAnneeInscr($params) {
		$data = new stdClass();
		$inscr = $params['Inscr'];
		$data->Cotisation = $inscr['cotis'];
		$data->Regularisation = $inscr['regul'];
		$this->SaveAnnee($data, 1);
	}

	function Delete() {
		$rec = $this->getChildren('Reglement');
		if(count($rec)) {
			$this->addError(array("Message"=>"Cette fiche ne peut être supprimée", "Prop"=>""));
			return false;
		}
		$rec = $this->getChildren('AdherentAnnee');
		foreach($rec as $r)
			$r->Delete();
		$rec = $this->getChildren('Inscription');
		foreach($rec as $r)
			$r->Delete();
		$rec = $this->getChildren('Reservation');
		foreach($rec as $r)
			$r->Delete();

		return parent::Delete();
	}

	function EditInscriptions($params) {
		$annee = Cadref::$Annee;
		if(!isset($params['Numero'])) if(!isset($params['step'])) $params['step'] = 0;
		switch($params['step']) {
			case 0:
				return array(
					'step'=>1,
					'template'=>'editInscriptions',
					'callNext'=>array(
						'nom'=>'EditInscriptions',
						'title'=>'Réglement',
						'needConfirm'=>false
					)
				);
				break;
			case 1:
				unset($params['step']);
				if($params['Inscr']['solde'])
						return array(
						'step'=>2,
						'template'=>'editDiffere',
						'args'=>$params,
						'callNext'=>array(
							'nom'=>'EditInscriptions',
							'title'=>'Différé',
							'needConfirm'=>false
						)
					);
				$ret = $this->saveInscriptions($params, true);
				$this->saveAnneeInscr($params);
				if($ret) return array(
						'data'=>'Inscription enregistrée',
						'callBack'=>array(
							'nom'=>'refreshAdherent',
							'args'=>array(true)
						)
					);
				return false;
				break;
			case 2:
				$this->saveInscriptions($params, true);
				$this->saveDiffere($params);
				$this->saveAnneeInscr($params);
				return array(
					'data'=>'Inscription enregistrée',
					'callBack'=>array(
						'nom'=>'refreshAdherent',
						'args'=>array(true)
					)
				);
				break;
		}
	}

	private function saveInscriptions($params, $saveAdh) {
		$annee = Cadref::$Annee;
		$inscr = $params['Inscr'];
		if(!$inscr['updated']) {
			// reglement differe
			if(!$inscr['paye'] && $inscr['solde']) return false;
			return true;
		}

		// inscriptions
		foreach($params['newInscr'] as $ins) {
			if(!$ins['updated']) continue;
			$id = $ins['id'];
			$attente = 0;
			$supprime = 0;

			$o = genericClass::createInstance('Cadref', 'Inscription');
			$cls = genericClass::createInstance('Cadref', 'Classe');
			$cls->initFromId($ins['ClasseClasseId']);

			if(!$id) {
				$o->addParent($this);
				$o->addParent($cls);
				$o->Annee = $annee;
				$o->Numero = $this->Numero;
				$o->CodeClasse = $ins['CodeClasse'];
				$o->Antenne = substr($ins['CodeClasse'], 0, 1);
			} else {
				$o->initFromId($id);
				$attente = $o->Attente;
				$supprime = $o->Supprime;
			}

			$o->Attente = $ins['Attente'];
			$o->Supprime = $ins['Supprime'];
			$o->DateInscription = $ins['DateInscription'];
			$o->DateAttente = $ins['DateAttente'];
			$o->DateSupprime = $ins['DateSupprime'];
			$o->Prix = $ins['Prix'];
			$o->Reduction = $ins['Reduction'];
			$o->Soutien = $ins['Soutien'];
			$o->Utilisateur = Sys::$User->Initiales;
			$o->Save();

			// classe : inscrits/attentes/suppmime
			if(!$id || $supprime != $o->Supprime || $attente != $o->Attente) {
				$cls->Save();
			}
		}

		// reglement
		if($inscr['paye']) {
			$r = genericClass::createInstance('Cadref', 'Reglement');
			$r->addParent($this);
			$r->Numero = $this->Numero;
			$r->Annee = $annee;
			$r->DateReglement = $inscr['date'];
			$r->Montant = $inscr['paye'];
			$r->ModeReglement = $inscr['mode'];
//			$r->Cotisation = $inscr['cotis'];
			$r->Notes = $inscr['note'];
			$r->Differe = 0;
			$r->Encaisse = 1;
			$r->Utilisateur = Sys::$User->Initiales;
			$r->Save(true);
		}

		// adherent
		$this->Annee = $annee;
		if($saveAdh) $this->Save();

		return true;
	}

	private function saveDiffere($params) {
		$annee = Cadref::$Annee;
		$reg = $params['Diff']['regl'];
		foreach($reg as $r) {
			if(!$r['updated'] || !$r[paye]) continue;

			$o = genericClass::createInstance('Cadref', 'Reglement');
			if($r['id']) $o->initFromId($r['id']);
			else $o->addParent($this);

			$m = $r['mois'];
			$d = '15/'.(strlen($m) == 1 ? '0' : '').$m.'/'.($m >= 9 ? $annee : ($annee + 1));
			$o->Numero = $this->Numero;
			$o->Annee = $annee;
			$o->DateReglement = $d;
			$o->Montant = $r['paye'];
			$o->ModeReglement = $r['mode'];
//			$o->Cotisation = 0;
			$o->Notes = $r['note'];
			$o->Differe = 1;
			$o->Encaisse = 0;
			$o->Utilisateur = Sys::$User->Initiales;
			$o->Save(true);
		}
		$this->Save();
		return true;
	}

	function CheckCertificat() {
		$annee = Cadref::$Annee;
		$min = ($annee).'0630';
		$dat = $this->DateCertificat;
		if(!empty($dat) && date('Ymd', $dat) < $min) return true;

		$cert = false;
		$ins = $this->getChildren('Inscription/Annee='.$annee);
		foreach($ins as $i) {
			$c = $i->getOneParent('Classe');
			$d = $c->getOneParent('Discipline');
			if($d->Certificat) {
				$cert = true;
				break;
			}
		}
		if($cert && (empty($dat) || date('Ymd', $dat) < $min)) return false;
		return true;
	}

	function PrintCarte($recto = false) {
		require_once ('PrintCarte.class.php');

		$annee = Cadref::$Annee;
		$aan = $this->getOneChild('AdherentAnnee/Annee='.$annee);
		$ins = $this->getChildren('Inscription/Annee='.$annee);

		$pdf = new PrintCarte($this, $aan, $recto);
		$pdf->SetAuthor("Cadref");
		$pdf->SetTitle('Carte'.$this->Numero);

		$pdf->AddPage();
		$pdf->PrintLines($ins);

		$file = 'Home/tmp/Carte'.$this->Numero.'_'.date('YmdHis').'.pdf';
		$pdf->Output(getcwd().'/'.$file);
		$pdf->Close();
		
		$p = Cadref::GetParametre('IMPRIMANTE', 'CARTE', Sys::$User->Login);
		if($p && $p->Valeur) {
			$s = "lp -d ".$p->Valeur." $file";
			shell_exec($s);
			return array('pdf'=>false);
		}

		return array('pdf'=>$file);
	}

	function PrintAdherentSession($obj) {
		if(isset($_SESSION['PrintAdherent'])) $obj = $_SESSION['PrintAdherent'];
		else $obj = false;
		return $obj;
	}
	
	function PrintAdherent($obj) {
		$_SESSION['PrintAdherent'] = $obj;
		
		$menus = ['impressionslisteadherents', 'impressionscertificatesmedicaux', 'impressionsfichesincompletes'];
		$mode = array_search($obj['CurrentUrl'], $menus);

		$annee = Cadref::$Annee;
		$sql = '';
		$whr = '';

		switch($mode) {
			case 0: // liste edherents
				$file = 'ListeAdherent';
				$typAdh = isset($obj['TypeAdherent']) ? $obj['TypeAdherent'] : '';
				$contenu = isset($obj['Contenu']) ? $obj['Contenu'] : '';
				$rupture = isset($obj['Rupture']) ? $obj['Rupture'] : '';
				$enseignant = isset($obj['Enseignant']) ? $obj['Enseignant'] : '';
				$visite = isset($obj['Visite']) ? $obj['Visite'] : '';
				$adherent = false;


				if($typAdh != '' || $contenu == 'Q' || $rupture == 'S' || $visite != '') {
					$sql = "select distinct ";
					$adherent = true;
					$rupture = 'S';
				}
				else $sql = "select i.CodeClasse, i.ClasseId, n.AntenneId, i.Attente, i.DateAttente, d.Libelle as LibelleD, n.Libelle as LibelleN, c0.CodeClasse as Delegue, ";

				$sql .= "e.Sexe, e.Numero, e.Nom, e.Prenom, e.Adresse1, e.Adresse2, e.CP, e.Ville, e.Telephone1, e.Telephone2, e.Mail";

				if($typAdh == 'S') {
					// adhérents sans inscription
					$sql .= "from `##_Cadref-Adherent` e ";
					$whr = "and e.Cotisation>0 and e.Reglement=e.Cotisation and e.Differe=0 and e.Montant=0 ";
				}
				elseif($visite != '') {
					$sql .= "
from `##_Cadref-Adherent` e
inner join `##_Cadref-Reservation` r on r.AdherentId=e.Id and r.VisiteId=$visite ";
					$rupture = 'S';
					$contenu = 'A';
				}
				else {
					// adhérents inscrits
					$sql .= "
from `##_Cadref-Inscription` i
inner join `##_Cadref-Classe` c on c.Id=i.ClasseId
inner join `##_Cadref-Niveau` n on n.Id=c.NiveauId
inner join `##_Cadref-Discipline` d on d.Id=n.DisciplineId
inner join `##_Cadref-Adherent` e on e.Id=i.AdherentId
left join `##_Cadref-AdherentAnnee` aa on aa.AdherentId=e.Id and aa.Annee='$annee'
left join `##_Cadref-Classe` c0 on c0.Id=aa.ClasseId ";
					if($enseignant) {
						$sql .= "inner join `##_Cadref-ClasseEnseignants` ce on ce.Classe=i.ClasseId ";
						$whr .= "and ce.EnseignantId=$enseignant ";
					}
				}

				$mail = (isset($obj['Mail']) && $obj['Mail'] != '') ? $obj['Mail'] : '';
				if($mail == 'A') $whr .= "and e.Mail<>'' ";
				elseif($mail == 'S') $whr .= "and e.Mail='' ";

				if($typAdh != 'S' && $visite == '') {
					$whr .= "and i.Annee='$annee' and i.Supprime=0 ";

					// type adherent
					if($typAdh != '') {
						$whr .= "and e.Adherent in (";
						switch($typAdh) {
							case 'B': $whr .= "'B') ";
								break;
							case 'A': $whr .= "'B','A') ";
								break;
							case 'D': $whr .= "'B','A','D') ";
								break;
						}
					}

					if(isset($obj['Nouveaux']) && $obj['Nouveaux']) $whr .= "and e.Inscription='$annee' ";

					$antenne = (isset($obj['Antenne']) && $obj['Antenne'] != '') ? $obj['Antenne'] : '';
					if($antenne != '') $whr .= "and n.AntenneId='$antenne' ";

					$attente = (isset($obj['Attente']) && $obj['Attente'] != '') ? $obj['Attente'] : '';
					if($attente == 'I') $whr .= "and i.Attente=0 ";
					elseif($attente == 'A') $whr .= "and i.Attente<>0 ";

					$lieu = (isset($obj['Lieu']) && $obj['Lieu'] != '') ? $obj['Lieu'] : '';
					if($lieu != '') $whr .= "and c.Lieu='$lieu' ";

					//disciplines exclues
					if(isset($obj['Excl']) && isset($obj['Disc'])) {
						$disc = $obj['Disc'];
						$excl = $obj['Excl'];
						for($i = 0; $i < 5; $i++) {
							if(isset($excl[$i]) && $excl[$i] && isset($disc[$i]) && strlen($disc[$i]) == 11) {
								$whr .= "and i.CodeClasse<>'".$disc[$i]."' ";
							}
						}
					}
					//disciplines incluses
					if(isset($obj['Disc'])) {
						$w = '';
						$disc = $obj['Disc'];
						$excl = isset($obj['Excl']) ? $obj['Excl'] : array();
						for($i = 0; $i < 5; $i++) {
							if(isset($disc[$i]) && $disc[$i] != '' && (!isset($excl[$i]) || !$excl[$i])) {
								$w .= $w == '' ? "and (" : "or ";
								$w .= "i.CodeClasse like '".$disc[$i]."%' ";
							}
						}
						if($w != '') $whr .= $w.") ";
					}
				}

				//requete sql
				if($whr != '') $sql .= "where ".substr($whr, 4);
				if($adherent) {
					if($contenu == 'Q') $sql .= "order by e.CP, e.Ville, e.Nom, e.Prenom ";
					else $sql .= "order by e.Nom, e.Prenom ";
				}
				else {
					if(isset($obj['OrdreAtt']) && $obj['OrdreAtt']) $sql .= "order by i.DateAttente ";
					else $sql .= "order by i.CodeClasse, e.Nom, e.Prenom ";
				}
				break;
			case 1: // certificats medicaux
				$file = 'ListeCertificat';
				$contenu = 'N';
				$rupture = 'E'; // enseignant
				$antenne = 0;
				$sql = "
select distinct a.Sexe, a.Numero, a.Nom, a.Prenom, a.Telephone1, a.Telephone2, a.Mail,
a.DateCertificat, i.CodeClasse, i.ClasseId, d.Libelle as LibelleD, n.Libelle as LibelleN
from `##_Cadref-Adherent` a
inner join `##_Cadref-Inscription` i on i.AdherentId=a.Id and i.Annee='$annee'
inner join `##_Cadref-Classe` c on c.Id=i.ClasseId
inner join `##_Cadref-Niveau` n on n.Id=c.NiveauId
inner join `##_Cadref-Discipline` d on d.Id=n.DisciplineId
left join `##_Cadref-ClasseEnseignants` ce on ce.Classe=c.Id
left join `##_Cadref-Enseignant` e on e.Id=ce.EnseignantId
where a.Annee='$annee' and i.Supprime=0 and i.Attente=0 and d.Certificat<>0 
and (a.DateCertificat is null or a.DateCertificat<unix_timestamp('$annee-07-01')) ";
				if($obj['mode'] == 'print' && isset($obj['NoMail']) && $obj['NoMail'])
					$sql .= " and a.Mail not like '%@%' ";
				if($obj['mode'] == 'mail')
					$sql .= " and a.Mail like '%@%' ";

				$sql .= "order by e.Nom,i.CodeClasse,a.Nom,a.Prenom";
				break;
			case 2: // fiches incomplètes
				$file = 'ListeIncomplet';
				$contenu = 'N';
				$rupture = 'S';
				$antenne = 0;
				$sql = "
select a.Sexe, a.Numero, a.Nom, a.Prenom, a.Telephone1, a.Telephone2, a.Mail
from `##_Cadref-Adherent` a
where a.Annee='$annee' and (a.Origine='' or a.SituationId='' or a.ProfessionId='' or a.Sexe='' or a.Naissance='')
order by a.Nom, a.Prenom";
				break;
		}


		$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
		$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
		if(!$pdo) return array('success'=>false, 'sql'=>$sql);;

		if($mode == 1) {
			$body = "À ce jour nous ne sommes toujours pas en possession de votre certificat médical.<br /><br />";
			$body .= "Merci de bien vouloir nous renvoyer l’attestation sur l’hooneur ci-jointe.";
			$body .= Cadref::MailSignature();
		}
		
		if($obj['mode'] == 'mail') {
			foreach($pdo as $a) {
				if(strpos($a['Mail'], '@') > 0) {
					if($mode == 1) {
						$file = $this->imprimeCertificat(array($a));
						$b = Cadref::MailCivility($a).$body;
						$args = array('Subject'=>'CADREF : Certificat médical', 'To'=>array($a['Mail']), 'Body'=>$body, 'Attachments'=>array($file));
					}
					else $args = array('Subject'=>$obj['Sujet'], 'To'=>array($a['Mail']), 'Body'=>$obj['Corps'], 'Attachments'=>$obj['Pieces']['data']);
					if(MAIL_ADH) Cadref::SendMessage($args);				
				}
			}
			return true;
		}
		if($obj['mode'] == 'sms') {
			foreach($pdo as $a) {
				$params = array('Telephone1'=>$a['Telephone1'],'Telephone2'=>$a['Telephone2'],'Message'=>$obj['SMS']);
				Cadref::SendSms($params);
			}
			return true;
		}
		if($obj['mode'] == 'export') {
			$file = 'Home/tmp/ListeAdherent_'.date('YmdHis').'.csv';
			$f = fopen($file, 'w');
			$s = '"Numéro";"Nom";"Prénom";"Adresse1";"Adresse2";"CP";"Ville";"Téléphone1";"Téléphone2";"Mail";"Délégué"';
			if($obj['Rupture'] != 'S') $s .= ';"Classe";"Discipline";"Niveau";"Attente";"Date attente"';
			$s .= "\n";
			fwrite($f, $s);
			foreach($pdo as $a) {
				$s = '"'.$a['Numero'].'";';
				$s .= '"'.$a['Nom'].'";';
				$s .= '"'.$a['Prenom'].'";';
				$s .= '"'.$a['Adresse1'].'";';
				$s .= '"'.$a['Adresse2'].'";';
				$s .= '"'.$a['CP'].'";';
				$s .= '"'.$a['Ville'].'";';
				$s .= '"'.$a['Telephone1'].'";';
				$s .= '"'.$a['Telephone2'].'";';
				$s .= '"'.$a['Mail'].'";';
				$s .= '"'.$a['Delegue'].'"';
				if($obj['Rupture'] != 'S') {
					$s .= ';';
					$s .= '"'.$a['CodeClasse'].'";';
					$s .= '"'.$a['LibelleD'].'";';
					$s .= '"'.$a['LibelleN'].'";';
					$s .= '"'.($a['Attente'] ? 'O' : 'N').'";';
					$s .= '"'.($a['Attente'] ? date('d/m/Y H:i',$a['DateAttente']) : '').'"';
				}
				$s .= "\n";
				fwrite($f, $s);
			}
			fclose($f);
			return array('csv'=>$file, 'sql'=>$sql);
		}
		
		if($contenu != 'Q') {
			require_once ('PrintAdherent.class.php');

			$pdf = new PrintAdherent($mode, $contenu, $rupture, $antenne, $attente, $typAdh);
			$pdf->SetAuthor("Cadref");
			$pdf->SetTitle('Liste adherents');

			$pdf->AddPage();
			$pdf->PrintLines($pdo);

			$file = 'Home/tmp/'.$file.'_'.date('YmdHis').'.pdf';
			$pdf->Output(getcwd().'/'.$file);
			$pdf->Close();
		} else {
			require_once('Class/Lib/pdfb/fpdf_fpdi/PDF_label.php');

			$f = array('paper-size'=>'A4',
				'metric'=>'mm',
				'marginLeft'=>0,
				'marginTop'=>8.5,
				'NX'=>2,
				'NY'=>8,
				'SpaceX'=>0,
				'SpaceY'=>0,
				'width'=>105,
				'height'=>37.125,
				'font-size'=>9);
			$pdf = new PDF_label($f);
			$pdf->SetAuthor("Cadref");
			$pdf->SetTitle('Etiquettes adherents');

			$pdf->AddPage();
			foreach($pdo as $l) {
				$s = $l['Nom'].'  '.$l['Prenom']."\n".$l['Adresse1']."\n".$l['Adresse2']."\n".$l['CP']."  ".$l['Ville'];
				$pdf->Add_Label(iconv('UTF-8', 'ISO-8859-15//TRANSLIT', $s));
			}

			$file = 'Home/tmp/EtiquetteAdherent_'.date('YmdHis').'.pdf';
			$pdf->Output(getcwd().'/'.$file);
			$pdf->Close();
		}

		return array('pdf'=>$file, 'sql'=>$sql);
	}

	function PrintCertificat($params) {
		$mode = isset($params['mode']) ? $params['mode'] : 'print';
		$annee = Cadref::$Annee;
		$sql = "
select distinct a.Id, a.Sexe, a.Numero, a.Nom, a.Prenom, a.Mail
from `##_Cadref-Adherent` a
inner join `##_Cadref-Inscription` i on i.AdherentId=a.Id and i.Annee='$annee'
inner join `##_Cadref-Classe` c on c.Id=i.ClasseId
inner join `##_Cadref-Niveau` n on n.Id=c.NiveauId
inner join `##_Cadref-Discipline` d on d.Id=n.DisciplineId
left join `##_Cadref-ClasseEnseignants` ce on ce.Classe=c.Id
left join `##_Cadref-Enseignant` e on e.Id=ce.EnseignantId";
		$where = "a.Annee='$annee' and i.Supprime=0 and i.Attente=0 and d.Certificat<>0 
and (a.DateCertificat is null or a.DateCertificat<unix_timestamp('$annee-07-01')) ";
		if($params['mode'] == 'print' && isset($params['NoMail']) && $params['NoMail'])
			$sql .= " and a.Mail not like '%@%' ";
		if($params['mode'] == 'mail')
			$sql .= " and a.Mail like '%@%' ";
		$antenne = isset($params['Antenne']) ? $params['Antenne'] : '';
		if($antenne) $sql .= " and n.AntenneId=$antenne";

		$id = $this->Id;
		if(!$id) {
			if($mode == 'mail' && (!isset($params['ExecTask']) || !$params['ExecTask'])) {
				$t = genericClass::createInstance('Systeme', 'Tache');
				$t->Nom = 'PrintCertificat';
				$t->Type = 'Fonction';
				$t->TaskType = '';
				$t->TaskModule = 'Cadref';
				$t->TaskObject = 'Adherent';
				$t->TaskFunction = 'TacheAdherent';
				$t->TaskArgs = serialize($params);
				$t->Save();
				return array('message'=>'Tache lancée en arrière plan.');
			}
			
			$sql .= " where $where order by a.Nom,a.Prenom";
			$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
			$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
			if(!$pdo) return false;

			if($mode == 'mail') {
				$sub = "CADREF : Certificat médical";
				$bod = "À ce jour nous ne sommes toujours pas en possession de votre certificat médical.<br /><br />";
				$bod .= "Merci de bien vouloir nous renvoyer l’attestation sur l’hooneur ci-jointe.";
				$bod .= Cadref::MailSignature();
				foreach($pdo as $p) {
					$file = $this->imprimeCertificat(array($p), $p['Numero']);
					$b = Cadref::MailCivility($p).$bod;
					$args = array('To'=>array($p['Mail']), 'Subject'=>$sub, 'Body'=>$b, 'Attachments'=>array($file));
					if(MAIL_ADH) Cadref::SendMessage($args);
				}
				return array('message'=>$pdo->rowCount().' mails envoyés.');
			}
			else {
				$file = $this->imprimeCertificat($pdo, '');
				return array('pdf'=>$file);
			}
		}
		else {
			$sql .= " where a.Id=$id and $where";
			$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
			$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
			if(!$pdo) return array('sql'=>$sql);

			if(!$pdo->rowCount()) return array(
				'step'=>2,
				'data'=>'Pas de certificat demandé.'
			);

			$file = $this->imprimeCertificat($pdo, $mode);
			return array('pdf'=>$file);
		}
	}
	
	private function imprimeCertificat($list, $num) {
		require_once ('PrintCertificat.class.php');

		$pdf = new PrintCertificat();
		$pdf->SetAuthor("Cadref");
		$pdf->SetTitle('Certificat medical');

		foreach($list as $l)
			$pdf->PrintPage($l);

		$file = 'Home/tmp/Certificat'.$num.'_'.date('YmdHis').'.pdf';
		$pdf->Output(getcwd().'/'.$file);
		$pdf->Close();

		return $file;
	}

	
	public static function TacheAdherent($params) {
		$args = unserialize($params->TaskArgs);
		$args['ExecTask'] = 1;
		$adh = genericClass::createInstance('Cadref', 'Adherent');
		switch($args['Nom']) {
			case 'PrintAttestation': return $adh->PrintAttestation($args);
			case 'PrintCertificat': return $adh->PrintCertificat($args);
		}
	}

	function PrintAttestation($params) {
		$mode = isset($params['mode']) ? $params['mode'] : 'print';
		$sql = "
select distinct h.Sexe,h.Mail,h.Numero,h.Nom,h.Prenom,h.Adresse1,h.Adresse2,h.CP,h.Ville,a.Cotisation
from `##_Cadref-AdherentAnnee` a
inner join `##_Cadref-Adherent` h on h.Id=a.AdherentId";

		$id = $this->Id;
		if(!$id) {
			if($mode == 'mail' && (!isset($params['ExecTask']) || !$params['ExecTask'])) {
				$t = genericClass::createInstance('Systeme', 'Tache');
				$t->Nom = 'PrintAttestation';
				$t->Type = 'Fonction';
				$t->TaskType = '';
				$t->TaskModule = 'Cadref';
				$t->TaskObject = 'Adherent';
				$t->TaskFunction = 'TacheAdherent';
				$t->TaskArgs = serialize($params);
				$t->Save();
				return array('message'=>'Tache lancée en arrière plan.');
			}
			
			$annee = $params['AttestAnnee'];
			$fisc = $params['AttestFiscale'];
			$where = " where a.Annee='$annee' and a.Cotisation>0 and substr(from_unixtime(a.DateCotisation),1,4)='$fisc'";
			$antenne = isset($params['Antenne']) ? $params['Antenne'] : '';
			if($antenne) {
				$sql .= "
left join `kob-Cadref-Inscription` i on i.AdherentId=h.Id and i.Annee='$annee'
left join `kob-Cadref-Classe` c on c.Id=i.ClasseId
left join `kob-Cadref-Niveau` n on n.Id=c.NiveauId
";
				$where .= " and n.AntenneId=$antenne";
			}
			if($mode == 'print' && isset($params['NoMail']) && $params['NoMail'])
				$where .= " and h.Mail not like '%@%'";
			if($mode == 'mail')
				$where .= " and h.Mail like '%@%'";
			$sql .= $where.' order by h.Nom, h.Prenom';

			$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
			$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
			if(!$pdo) return false;

			if($mode == 'mail') {
				$an = $annee.'-'.($annee+1);
				$sub = "CADREF : Attestation fiscale";
				$bod = "Veuillez trouver en pièce jointe l’attestation fiscale correspondant à votre cotisation $an pour l’année fiscale $fisc.<br/><br />";
				$bod .= "Cette somme est à noter à la ligne 7UF de la déclaration 2042 RICI, case intitulée : \"Dons versés à d’autres organismes d’intérêt général\".";
				$bod .= Cadref::MailSignature();
				foreach($pdo as $p) {
					$file = $this->imprimeAttestation(array($p), $annee, $fisc, $p['Numero']);
					$b = Cadref::MailCivility($p).$bod;
					$args = array('To'=>array($p['Mail']), 'Subject'=>$sub, 'Body'=>$b, 'Attachments'=>array($file));
					if(MAIL_ADH) Cadref::SendMessage($args);
				}
				return array('message'=>$pdo->rowCount().' mails envoyés.');
			}
			else {
				$file = $this->imprimeAttestation($pdo, $annee, $fisc, '');
				return array('pdf'=>$file);
			}
		}
		else {
			if(!isset($params['step'])) $params['step'] = 0;
			switch($params['step']) {
				case 0:
					return array(
						'step'=>1,
						'template'=>'printAttestation',
						'callNext'=>array(
							'nom'=>'PrintAttestation',
							'title'=>'Attestation 2',
							'needConfirm'=>false
						)
					);
					break;
				case 1:
					$annee = $params['Attest']['AttestAnnee'];
					$fisc = $params['Attest']['AttestFiscale'];
					$where = " where a.AdherentId=$id and a.Annee='$annee' and a.Cotisation>0 and substr(from_unixtime(a.DateCotisation),1,4)='$fisc'";
					$sql = str_replace('##_', MAIN_DB_PREFIX, $sql.$where);
					$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
					if(!$pdo) return false;
					
					if(!$pdo->rowCount()) return array(
						'step'=>2,
						'data'=>'Pas de cotisation pour cette année'
					);

					$file = $this->imprimeAttestation($pdo, $annee, $fisc, $mode);
					return array(
						'step'=>2,
						'data'=>'<a id="displayAttestation" href="'.$file.'" target="_blank" ng-click="attestationAdherent(\''.$file.'\')">Attestation imprimée</a>',
						'callBack'=>array(
							'nom'=>'displayAttestation',
							'title'=>'Attestation 3',
							'args'=>array()
						)
					);
					break;
			}
		}
		return array(
			'params'=>$params,
			'template'=>'printAttestation',
		);
	}
	
	function CotisationList($id) {
		$sql = "select Cotisation,Annee,substr(from_unixtime(DateCotisation),1,4) as Fisc from `##_Cadref-AdherentAnnee` where AdherentId=$id and Cotisation>0";
		$sql = str_replace('##_', MAIN_DB_PREFIX, $sql.$where);
		$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
		$cot = array();
		foreach($pdo as $p) $cot[] = array('Cotisation'=>$p['Cotisation'],'Annee'=>$p['Annee'].'-'.($p['Annee']+1),'Fisc'=>$p['Fisc']);
		return array('cotisations'=>$cot);
	}

	private function imprimeAttestation($list, $annee, $fisc, $num) {
		require_once ('PrintAttestation.class.php');

		$pdf = new PrintAttestation($annee, $fisc);
		$pdf->SetAuthor("Cadref");
		$pdf->SetTitle('Attestations fiscales');

		foreach($list as $l)
			$pdf->PrintPage($l);

		$file = 'Home/tmp/Attestation'.$num.'_'.date('YmdHis').'.pdf';
		$pdf->Output(getcwd().'/'.$file);
		$pdf->Close();

		return $file;
	}

	function PrintCheque($params) {
		$id = $this->Id;
		if(!$id) {
			$p = $params;
			$annee = Cadref::$Annee;
			$classe = $params['CodeClasse'];
			$sql = "
select h.Nom,h.Prenom,h.Adresse1,h.Adresse2,h.CP,h.Ville
from `##_Cadref-Inscription` i
inner join `##_Cadref-Adherent` h on h.Id=i.AdherentId
where i.CodeClasse='$classe' and i.Annee='$annee'";
			$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
			$pdo = $GLOBALS['Systeme']->Db[0]->query($sql);
			if(!$pdo) return false;

			$file = $this->imprimeCheque($pdo, $params);
			return array('pdf'=>$file, 'sql'=>$sql);
		}
		else {
			if(!isset($params['step'])) $params['step'] = 0;
			switch($params['step']) {
				case 0:
					return array(
						'step'=>1,
						'template'=>'printCheque',
						'callNext'=>array(
							'nom'=>'PrintCheque',
							'title'=>'Cheque 2',
							'needConfirm'=>false
						)
					);
					break;
				case 1:
					$file = $this->imprimeCheque(null, $params['Cheque']);
					return array(
						'data'=>'<a id="displayCheque" href="'.$file.'" target="_blank" ng-click="chequeAdherent(\''.$file.'\')">Chèque imprimé</a>',
						'callBack'=>array(
							'nom'=>'displayCheque',
							'title'=>'Cheque 3',
							'args'=>array()
						)
					);
					break;
			}
		}
		return array(
			'params'=>$params,
			'template'=>'printCheque',
		);
	}

	private function imprimeCheque($list, $params) {
		require_once ('PrintCheque.class.php');

		$pdf = new PrintCheque($annee, $fisc);
		$pdf->SetAuthor("Cadref");
		$pdf->SetTitle('Cheque');

		if($list) {
			foreach($list as $l)
				$pdf->PrintPage($l, $params);
		} else $pdf->PrintPage(null, $params);

		$file = 'Home/tmp/Cheque_'.date('YmdHis').'.pdf';
		$pdf->Output(getcwd().'/'.$file);
		$pdf->Close();

		return $file;
	}

	function SendMessage($params) {
		if(!isset($params['step'])) $params['step'] = 0;
		switch($params['step']) {
			case 0:
				return array(
					'step'=>1,
					'template'=>'sendMessage',
					'args'=>array(),
					'callNext'=>array(
						'nom'=>'SendMessage',
						'title'=>'Message suite',
						'args'=>array('civilite'=>$s),
						'needConfirm'=>false
					)
				);
				break;
			case 1:
				if($params['Msg']['sendMode'] == 'mail') {
					$params['Msg']['To'] = array($params['Msg']['Mail']);
					$params['Msg']['Body'] .= Cadref::MailSignature();
					$params['Msg']['Attachments'] = $params['Msg']['Pieces']['data'];
					$ret = Cadref::SendMessage($params['Msg']);
				}
				else {
					$ret = Cadref::SendSms(array('Telephone1'=>$this->Telephone1,'Telephone2'=>$this->Telephone2,'Message'=>$params['Msg']['SMS']));
				}
				return array(
					'data'=>'Message envoyé',
					'params'=>$params['Msg'],
					'success'=>true,
					'callNext'=>false
				);
				break;
		}
	}

	function GetCours($mode, $obj) {
		$annee = Cadref::$Annee;
		$filter = str_replace('&', '', $obj['Filter']);
		$adhId = $this->Id;
		$antId = $obj['AntenneId'];
		$secId = $obj['SectionId'];
		$disId = $obj['DisciplineId'];
		switch($mode) {
			case 'antenne':
				$sql = "
select Id, Libelle
from `##_Cadref-Antenne`
where Libelle like '%$filter%'
";
				break;
			case 'section':
				$sql = "
select distinct s.Id, s.Libelle
from `##_Cadref-Discipline` d0
inner join `##_Cadref-Niveau` n on n.DisciplineId=d0.Id and n.AntenneId=$antId
inner join `##_Cadref-Classe` c on c.NiveauId=n.Id and c.Annee='$annee'
inner join `##_Cadref-WebDiscipline` d on d.Id=d0.WebDisciplineId
inner join `##_Cadref-WebSection` s on s.Id=d.WebSectionId
where d0.WebDisciplineId>0 and s.Libelle like '%$filter%'
order by s.Libelle";
				break;
			case 'discipline':
				$sql = "
select distinct d.Id, d.Libelle
from `##_Cadref-Discipline` d0
inner join `##_Cadref-Niveau` n on n.DisciplineId=d0.Id and n.AntenneId=$antId
inner join `##_Cadref-Classe` c on c.NiveauId=n.Id and c.Annee='$annee'
inner join `##_Cadref-WebDiscipline` d on d.WebSectionId=$secId and d.Id=d0.WebDisciplineId
where d0.WebDisciplineId>0 and d.Libelle like '%$filter%'
order by d.Libelle";				
				break;
			case 'classe':
				$sql = "
select distinct c.Id as clsId, d.Libelle as LibelleD, n.Libelle as LibelleN, 
j.Jour, c.HeureDebut, c.HeureFin, c.CycleDebut, c.CycleFin,
c.Places,if(c.Places<c.Inscrits,0,c.Places-c.Inscrits) as Disponible,
a.LibelleCourt as LibelleA,c.Prix,c.Attachements,
if(c.DateReduction1 is not null and c.DateReduction1<=unix_timestamp(Now()),c.Reduction1,0) as Reduction1,
if(c.DateReduction2 is not null and c.DateReduction2<=unix_timestamp(Now()),c.Reduction2,0) as Reduction2
from `##_Cadref-Discipline` d0
inner join `##_Cadref-Niveau` n on n.DisciplineId=d0.Id and n.AntenneId=$antId
inner join `##_Cadref-Classe` c on c.NiveauId=n.Id and c.Annee='$annee'
inner join `##_Cadref-WebDiscipline` d on d.Id=d0.WebDisciplineId
inner join `##_Cadref-Antenne` a on a.Id=n.AntenneId
left join `##_Cadref-Jour` j on j.Id=c.JourId
where d0.WebDisciplineId=$disId and (d0.Libelle like '%$filter%' or n.Libelle like '%$filter%')
order by d.Libelle, n.Libelle, c.JourId, c.HeureDebut";
				break;
			case 'inscription':
				$sql = "
select i.Id as insId, c.Id as clsId, d.Libelle as LibelleD, n.Libelle as LibelleN, 
j.Jour, c.HeureDebut, c.HeureFin, c.CycleDebut, c.CycleFin,
a.LibelleCourt as LibelleA,i.Prix,i.Reduction,i.Soutien,c.Attachements,
i.Attente,i.Supprime,
from_unixtime(i.DateAttente,'%d/%m/%Y') as DateAttente,
from_unixtime(i.DateSupprime,'%d/%m/%Y') as DateSupprime,
from_unixtime(i.DateInscription,'%d/%m/%Y') as DateInscription
from `##_Cadref-Inscription` i
inner join `##_Cadref-Classe` c on c.Id=i.ClasseId
inner join `##_Cadref-Niveau` n on n.Id=c.NiveauId
inner join `##_Cadref-Discipline` d0 on d0.Id=n.DisciplineId
inner join `##_Cadref-WebDiscipline` d on d.Id=d0.WebDisciplineId
inner join `##_Cadref-Antenne` a on a.Id=n.AntenneId
left join `##_Cadref-Jour` j on j.Id=c.JourId
where i.AdherentId=$adhId and i.Annee='$annee'
order by d.Libelle, n.Libelle, c.JourId, c.HeureDebut";
				break;
		}
		$sql = str_replace('##_', MAIN_DB_PREFIX, $sql);
		$pdo = $GLOBALS['Systeme']->Db[0]->query($sql, PDO::FETCH_ASSOC);
		$data = $pdo->fetchAll();
		if($mode == 'inscription' || $mode == 'classe') {
			$sql1 = "
select e.Nom, e.Prenom 
from `##_Cadref-ClasseEnseignants` ce
inner join `##_Cadref-Enseignant` e on e.Id=ce.EnseignantId
where ce.Classe=:cid";
			$sql1 = str_replace('##_', MAIN_DB_PREFIX, $sql1);
			$pdo = $GLOBALS['Systeme']->Db[0]->prepare($sql1);
			foreach($data as &$d) {
				$pdo->execute(array(':cid'=>$d['clsId']));
				$e = '';
				foreach($pdo as $p) {
					if($e) $e .= ', ';
					$e .= trim($p['Prenom'].' '.$p['Nom']);
				}
				$d['Enseignants'] = $e;
			}
		}
		return array('data'=>$data, 'sql'=>$sql);
	}
	
	
	function ChangePassword($params) {
		$data = array();
		$data['success'] = 0;
		$data['error'] = 0;
		$pwd = '[md5]'.md5($params['PwdOld']);
		if($pwd != Sys::$User->Pass) {
			$data['message'] = 'Mot de passe actuel incorrect';
			$data['error'] = 1;
			return $data;
		}
		$new = $params['PwdNew'];
		$cnf = $params['PwdConf'];
		$p = "/^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).*$/";
		if(strlen($new) < 8 || ! preg_match($p, $new)) {
			$data['message'] = 'Nouveau mot de passe non conforme';
			$data['error'] = 2;
			return $data;
		}
		if($new != $cnf) {
			$data['message'] = 'Confirmation incorrecte';
			$data['error'] = 3;
			return $data;
		}
		Sys::$User->Pass = '[md5]'.md5($new);
		Sys::$User->Save();
		
		if(strpos($this->Mail, '@') > 0) {
			$s = Cadref::MailCivility($this);
			$s .= "Votre nouveau mot de passe a été enregistré.<br /><br />";
			$s .= Cadref::MailSignature();
			$params = array('Subject'=>('CADREF : Changement de mot de passe.'),
				'To'=>array($this->Mail),
				'Body'=>$s);
			Cadref::SendMessage($params);
		}
		$msg = "CADREF : Changement de mot de passe.\nCode utilisateur: $this->Numero\nMote de passe: $new\n";
		$params = array('Telephone1'=>$this->Telephone1,'Telephone2'=>$this->Telephone2,'Message'=>$msg);
		Cadref::SendSms($params);

		$data['success'] = 1;
		$data['message'] = 'Mot de passe enregistré';
		return $data;
	}


	function EditReservations($params) {
		$annee = Cadref::$Annee;
		if(!isset($params['Numero'])) if(!isset($params['step'])) $params['step'] = 0;
		switch($params['step']) {
			case 0:
				return array(
					'step'=>1,
					'template'=>'editReservation',
					'callNext'=>array(
						'nom'=>'EditReservations',
						'title'=>'Réglement',
						'needConfirm'=>false
					)
				);
				break;
			case 1:
				unset($params['step']);
				$ret = $this->saveReservations($params, true);
				$this->saveAnneeReserv($params);
				if($ret) return array(
						'data'=>'Reservation enregistrée',
						'callBack'=>array(
							'nom'=>'refreshAdherent',
							'args'=>array(true)
						)
					);
				return false;
				break;
		}
	}

	private function saveReservations($params, $saveAdh) {
		$annee = Cadref::$Annee;
		$inscr = $params['Visit'];
		if(!$inscr['updated']) return true;

		// reservations
		foreach($params['newReserv'] as $ins) {
			if(!$ins['updated']) continue;
			$id = $ins['id'];
			$attente = 0;
			$supprime = 0;

			$o = genericClass::createInstance('Cadref', 'Reservation');

			if(!$id) {
				$o->addParent($this);
				$vis = genericClass::createInstance('Cadref', 'Visite');
				$vis->initFromId($ins['VisiteVisiteId']);
				$o->addParent($vis);
				$o->Annee = $annee;
				$o->Numero = $this->Numero;
				$o->Visite = $ins['Visite'];
			} else {
				$o->initFromId($id);
				$attente = $o->Attente;
				$supprime = $o->Supprime;
			}
			if($ins['DepartDepartId']) {
				$dep = genericClass::createInstance('Cadref', 'Depart');
				$dep->initFromId($ins['DepartDepartId']);
				$o->addParent($dep);
			}

			$o->Attente = $ins['Attente'];
			$o->Supprime = $ins['Supprime'];
			$o->DateInscription = $ins['DateInscription'];
			$o->DateAttente = $ins['DateAttente'];
			$o->DateSupprime = $ins['DateSupprime'];
			$o->Prix = $ins['Prix'];
			//$o->Assurance = $ins['Assurance'];
			$o->ModeReglement = $ins['ModeReglement'];
			$o->Utilisateur = Sys::$User->Initiales;
			$o->Save();

			// visite : inscrits/attentes/suppmime
			if(!$id || $supprime != $o->Supprime || $attente != $o->Attente) {
				$vis->Save();
			}
		}

//		// reglement
//		if($inscr['paye']) {
//			$r = genericClass::createInstance('Cadref', 'Reglement');
//			$r->addParent($this);
//			$r->Numero = $this->Numero;
//			$r->Annee = $annee;
//			$r->DateReglement = $inscr['date'];
//			$r->Montant = $inscr['paye'];
//			$r->ModeReglement = $inscr['mode'];
////			$r->Cotisation = $inscr['cotis'];
//			$r->Notes = $inscr['note'];
//			$r->Differe = 1;
//			$r->Encaisse = 0;
//			$r->Utilisateur = Sys::$User->Initiales;
//			$r->Save(true);
//		}

		// adherent
		$this->Annee = $annee;
		if($saveAdh) $this->Save();

		return true;
	}
	
	private function saveAnneeReserv($params) {
		$data = new stdClass();
		$inscr = $params['Visit'];
		$data->Cotisation = $inscr['cotis'];
		$data->Regularisation = $inscr['regul'];
		$this->SaveAnnee($data, 1);
	}
	

}
