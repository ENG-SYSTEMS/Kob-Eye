<?php

class Facture extends genericClass {
// il a été décidé de ne stocker dans la facture que le minimum d'informations
// car on peut avoir un taux de tva différent suivant le produit ou la livraison
//  au moment de l'impression de la facture on ira chercher toutes les informations
    var $Client;
    var $Commande;
    var $AdrFactu;
    var $AdrLivr;

	public function Save() {

		if (is_object($this->Client)) {
			$this->AddParent( $this->Client );
		}
		if (is_object($this->Commande)) {
			$this->AddParent( $this->Commande );
		}
        if (is_object($this->AdrFactu)){
            $this->FactuCivilite = $this->AdrFactu->Civilite;
            $this->FactuNom = $this->AdrFactu->Nom;
            $this->FactuPrenom = $this->AdrFactu->Prenom;
            $this->FactuAdresse = $this->AdrFactu->Adresse;
            $this->FactuCodePostal = $this->AdrFactu->CodePostal;
            $this->FactuVille = $this->AdrFactu->Ville;
            $this->FactuPays = $this->AdrFactu->Pays;
        }
        if (is_object($this->AdrLivr)){
            $this->LivrCivilite = $this->AdrLivr->Civilite;
            $this->LivrNom = $this->AdrLivr->Nom;
            $this->LivrPrenom = $this->AdrLivr->Prenom;
            $this->LivrAdresse = $this->AdrLivr->Adresse;
            $this->LivrCodePostal = $this->AdrLivr->CodePostal;
            $this->LivrVille = $this->AdrLivr->Ville;
            $this->LivrPays = $this->AdrLivr->Pays;
        }
		parent::Save();

		if ($this->NumFac=='') $this->NumFac = sprintf("FA".Date('Y').Date('m')."%05d",$this->Id);
		// Mise à jour des base ht de la facture
		$cdet= $this->getParents('Commande') ;
		$cde=$cdet[0];
        $cde->getLignesCommande();
        $cde->getTableTva($this);
		parent::Save();
	}

	/**
	 * Création d'une facture
	 * @Param  	objet  commande
	 * @return	void
	 */
	public function InitFromCde($lacde) {
		$this->MontantTTC = $lacde->MontantPaye;
		$this->Commande = $lacde;
		$this->Client = $lacde->getClient();
        $this->AdrFactu = $lacde->getAdresseFacturation();
        $this->AdrLivr = $lacde->getAdresseLivraison();
	}

	/* APPALOOSA *******************************************/

	/**
	 * PGF 
	 *
	 * impression factures
	 * @return	status
	 */
	function PrintFactures($ids) {
		$files = array();
		foreach($ids as $id) $files[] = "Boutique/Facture/$id/FacturePdf.pdf";
		$res = array('printFiles'=>$files);
		return WebService::WSStatus('method', 1, '', '', '', '', '', null, $res);
	}

	/**
	 * PGF 
	 *
	 * export factures
	 * @return	status
	 */
	function ExportCompta($deb,$fin) {
		$models = array('Particuliers','Professionnels');
		$otvaDef = new ObjetTva();
		$err = '';
		$files = array();
		$fps = array();

		$fin += 86400;
		$facs = Sys::getData('Boutique',"Facture/tmsCreate>=$deb&tmsCreate<$fin",0,99999,'ASC','tmsCreate');
		foreach($facs as $fac) {
			$numFac = $fac->NumFac;
			$cmd = $fac->getParents('Commande');
			$cmd = $cmd[0];
			$otva = $cmd->getObjetTva($otvaDef);
			$mag = $cmd->getParents('Magasin');
			$mag = $mag[0];
			$typCpt = $mag->TypeCompta;
			$journal = $mag->JournalVente;
			$regDebit = $mag->CompteRegulDebit;
			$regCredit = $mag->CompteRegulCredit;
			$bon = $cmd->getChildren('BonLivraison');
			// initialisation de la livraison à 0 si pas de livraison
			$liv=0;
			// TODO PLUS TARD
			// IL FAUDRA MODIFIER DANS TYPE DELIVRAISON ET AU LIEU DE STOCKER LE TAUX STOCKER LE TYPE DE TVA (REDUITE, NORMALE...)

			// on cherche le compte comptable du taux de tva de la livraison à partir d'un taux
			if(sizeof($bon)) {
				$bon = $bon[0];
				$liv = round($bon->MontantLivraisonTTC, 2);
				$tliv = $bon->getParents('TypeLivraison');
				$tliv = $tliv[0];
				$cptLiv = $tliv->CompteComptable;
				if(!$cptLiv) $err .= "$numFac : Type livraison $tliv->Nom : Compte non trouvé\n";
 			} 

			$cli = Sys::$Modules['Boutique']->callData("Client/Commande/".$cmd->Id,false,0,1,'','');
			$cli = Client::getCurrentClient($cli[0]['UserId']);
			$nom = trim($cli->Nom.' '.$cli->Prenom);
			if(isset($fps[$typCpt])) {
				$fp = $fps[$typCpt];
			}
			else {
				$file = 'Home/tmp/export-Compta'.$models[$typCpt].'-'.date('Yhm-His').'.csv';
				$files[] = $file;
				$fp = fopen($file, 'w');
				$fps[$typCpt] = $fp;
			}

			$etatP = -1;
			$pay = Sys::$Modules['Boutique']->callData("Commande/".$cmd->Id."/Paiement",false,0,0,'DESC','Id');
			$p = null;
			foreach($pay as $p) {
				if($p['Etat'] == 1) {
					$etatP = $p['Etat'];
					break;
				}
			}
			if($etatP == -1) {
				foreach($pay as $p) {
					$etatP = $p['Etat'];
					break;
				}
			}
			if($p) {
				$typ = Sys::getData('Boutique',"Instance/Paiement/".$p['Id'],0,1);
				$typ = $typ[0];
				$paiement = $typ->Nom;
				$compteClient = $typ->CompteComptable;
				if(!$compteClient) $err .= "$numFac : Paiement $paiement : Compte non trouvé\n";
			}
			else {
				$err .= "Paiement non trouvé\n";
				$paiement = '';
				$compteClient = '';
			}

			$hts = array();
			$tht = 0;
			$ttc = round($cmd->MontantTTC+$liv, 2);
			$ecr = date('d/m/Y', $fac->tmsCreate).';'.$journal.';'.$compteClient.';'.$numFac.' '.$paiement.';'.$nom.';';
			$ecr .= number_format($ttc, 2, ',', '').";;\r\n";
			fwrite($fp, $ecr);
			
			$cre = array();
			$lig = $cmd->getChildren('LigneCommande');
			foreach($lig as $l) {
				$ref = $l->getParents('Reference');
				$ref = $ref[0];
				$pro = $ref->getParents('Produit');
				$pro = $pro[0];

				if ($l->TypeProduit==4){
					$l->initConfig();
					$ht=0;
					$cps = $pro->getChildren('ConfigPack');
					if (is_array($cps)) {
						$htRef = 0;
						foreach ($cps as $cp){
							$ref2 = genericClass::createInstance('Boutique','Reference');
							$ref2->initFromId($l->Config[$cp->Id]);
							if ($cp->TarifPack) $htRef += $ref2->TarifPack;
							else $htRef += $cp->TarifHT;
						}
						foreach ($cps as $cp){
							$ref2 = genericClass::createInstance('Boutique','Reference');
							$ref2->initFromId($l->Config[$cp->Id]);
							if ($cp->TarifPack) $Montant = $ref2->TarifPack;
							else $Montant = $cp->TarifHT;
							if($Montant) {
								$Montant = $l->MontantHT * ($Montant / $htRef);
								$pro2 = $ref2->getParents('Produit');
								$pro2 = $pro2[0];
								$tva = $otva->getTaux($pro2->TypeTvaInterne);
								$hts[$tva] += $Montant;
								$acc = $this->findAccount($pro2, $typCpt);
								if(empty($acc)) {
									$acc = '';
									$err .= "$numFac : Produit $pro2->Reference : Compte non trouvé\n";
								}
								$cre[$acc] += $Montant;
								$ht += $Montant;
							}
						}
					}
					$tht += $ht;
				}
				elseif($l->MontantHT) {
					$tva = $otva->getTaux($pro->TypeTva);
					$hts[$tva] += $l->MontantHT;
					$acc = $this->findAccount($pro, $typCpt);
//klog::l("$numFac : ACC $acc : PROD $pro->Reference : HT $l->MontantHT : TVA $tva");
					if(empty($acc)) {
						$acc = '';
						$err .= "$numFac : Produit $pro->Reference : Compte non trouvé\n";
					}
					$cre[$acc] += $l->MontantHT;
					$tht += $l->MontantHT;
				}
			}
			if($liv) {
				$ht = round($bon->MontantLivraisonHT, 2);
				$cre[$cptLiv] = $ht;
				$tht += $bon->MontantLivraisonHT;
				$hts[$bon->TxTvaBonLivr] += $bon->MontantLivraisonHT;
			}
			foreach($hts as $tva => $ht) {
				$acc = $otva->getCompte($tva, false);
				if(!$acc) $err .= "$numFac : TVA ".$tva['Taux']." : Compte non trouvé\n";
				else $cre[$acc] += round($ht * $tva / 100, 2);
			}

			// ajuste les credits pour correspondre au tht
//			$tht = 0;
			$tcre = 0;
			$max = 0;
			foreach($cre as $acc => $val) {
//				$tht += $val;
				$val = round($val, 2);
				$tcre += $val;
				if($val>$max) {$max = $val; $amax = $acc;}
				$cre[$acc] = $val;
			}
			$tht = round($tht, 2);
			$diff = round($ttc - $tcre, 2);
//if(abs($diff)>.02) klog::l("$numFac : >>>>>>>>>> TTC:$ttc HT:$tht CRED:$tcre  DIFF:$diff",$cre);
			if(abs($diff) > 0.02) {
				//$err .= "Facture $fac->NumFac : Balance incorrecte\n";
			}
			else {
				$cre[$amax] += $diff;
				$tcre += $diff;
				$diff = 0;
			}
			$ctrl = 0;
			$n = count($cre);
			$i = 0;
			foreach($cre as $acc => $val) {
				$i++;
				$mnt = round($val, 2);
				$ctrl += $mnt;
				$ecr = date('d/m/Y', $fac->tmsCreate).';'.$journal.';'.$acc.';'.$numFac.' '.$paiement.';'.$nom.';';
				$ecr .= ';'.number_format($mnt, 2, ',', '').';'.($i == $n && $diff == 0 ? number_format($ctrl, 2, ',', '') : '')."\r\n";
				fwrite($fp, $ecr);
			}
			if(abs($diff) > 0.02) {
//				$ctrl += $diff;
				if($diff < 0) {
					$ecr = date('d/m/Y', $fac->tmsCreate).';'.$journal.';'.$regDebit.';'.$numFac.' '.$paiement.';'.$nom.';';
					$ecr .= number_format(abs($diff), 2, ',', '').";;".number_format($ctrl, 2, ',', '')."\r\n";
				}
				else {
					$ctrl += $diff;
					$ecr = date('d/m/Y', $fac->tmsCreate).';'.$journal.';'.$regCredit.';'.$numFac.' '.$paiement.';'.$nom.';';
					$ecr .= ";".number_format(abs($diff), 2, ',', '').";".number_format($ctrl, 2, ',', '')."\r\n";
				}
				fwrite($fp, $ecr);
			}
		}

		foreach($fps as $fp) fclose($fp);
		
		if(!count($files)) $err .= 'Rien à exporter';
		if($err != '') return WebService::WSStatus('method', 0, '', '', '', '', '', array(array('message'=>$err)), null);
		
		$res = array('printFiles'=> $files);
		return WebService::WSStatus('method', 1, '', '', '', '', '', null, $res);
	}
	private function findAccount($pro, $typ) {
		if($pro->CompteComptable) return $pro->CompteComptable;
		$acc = '';
		$cat = $pro->getParents('Categorie');
		foreach($cat as $c) {
			$acc = $typ ? $c->CompteComptablePro : $c->CompteComptable;
			if($acc) return $acc;
			$acc = $this->findAccount1($c, $typ);
			if($acc) break;
		}
		return $acc;
	}
	private function findAccount1($cat, $typ) {
		$acc = '';
		$cat = $cat->getParents('Categorie');
		foreach($cat as $c) {
			$acc = $typ ? $c->CompteComptablePro : $c->CompteComptable;
			if($acc) return $acc;
			$acc = $this->findAccount1($c, $typ);
			if($acc) break;
		}
		return $acc;
	}



}