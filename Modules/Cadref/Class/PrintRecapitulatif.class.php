<?php

require_once('Class/Lib/pdfb/fpdf_fpdi/fpdf.php');

class PrintRecapitulatif extends FPDF {
	
	private $left = 5;
	private $head;
	private $align;
	private $width;
	private $posy;
	private $largeur = 0;
	private $total = [0,0,0,0,0,0,0,0,0,0,0,0];
	private $ttotal = [0,0,0,0,0,0,0,0,0,0,0,0];
	private $titre;
	private $adherent = 0;
	private $sansCotis = 0;
	private $sansCours = 0;
	private $cours = false;
	private $nbDons = 0;
	private $nbSoutien = 0;
	private $dons;
	private $tdons = array();
	
	
	function PrintRecapitulatif($nsold) {
		parent::__construct('L', 'mm', 'A4');
		$this->AcceptPageBreak(true, 12);

		$this->nbcot = $nbcot;
		$this->cotis = $cotis;
		$this->head = array('','','Cours','Réduc','Soutien','Total','Cotis','Dons','Règlé','Différé','Régul',
			'Av rep','Av dû','Av util','Solde');
		$this->width = array(17,65,17,15,15,17,15,17,17,17,15,15,15,15,17);
		$this->align = array('L','L','R','R','R','R','R','R','R','R','R','R','R','R','R');
		$n = count($this->head);
		for($i = 0; $i < $n; $i++) $this->largeur += $this->width[$i]; 

		$this->titre = Cadref::$UTL." : Récapitulatif Adhérents";
		if($nsold) $this->titre .= " (Non soldés)";
	}
	
	private function cv($txt) {
		return iconv('UTF-8','ISO-8859-15//TRANSLIT',$txt);
	}

	function Header() {
		$y = 5;
		$this->SetFont('Arial','B',10);
		$this->SetXY($this->left, $y);
		$this->Cell(30, 4.5, date('d/m/Y H:i'));
		$this->SetXY(-40, $y);
		$this->Cell(35, 4.5, 'Page '.$this->PageNo(), 0, 0, 'R');
		$y += 5;
		$this->SetFont('Arial','B',12);
		$this->SetXY($this->left, $y);
		$this->Cell(200, 4.5, $this->cv($this->titre), 0, 0, 'C');
		$y += 8;
		
		$this->SetXY($this->left, $y);
		$this->SetFont('Arial','BU',9);
		$n = count($this->head);
		for($i = 0; $i < $n; $i++) 
			$this->Cell($this->width[$i], 5, $this->cv($this->head[$i]), 0, 0, $this->align[$i]);
	
		$this->posy = $y+6;
		$this->SetXY($this->left, $this->posy);
	}

	function Footer() {
		$this->SetXY($this->left, $this->posy);
	}
	
	function PrintTotal($mode=0) {
		if($mode) $t = &$this->total; 
		else $t = &$this->ttotal;
		
		$this->SetFont('Arial','B',9);
		$this->SetXY($this->left, $this->posy);
		$this->Cell($this->width[0], 4.5, '');
		$this->Cell($this->width[1], 4.5, $mode ? '' : 'Totaux', 0, 0, 'R');
		for($i = 0; $i < 12; $i++) {
			if($i == 2 && $mode && $t[$i] && $t[$i] != Cadref::$Soutien) $this->SetTextColor(255,0,0);
			$this->Cell($this->width[$i+2], 4.5, $t[$i], 0, 0, 'R');
			if($i == 2 && $mode && $t[$i] && $t[$i] != Cadref::$Soutien) $this->SetTextColor(0,0,0);
		}
		$s = $t[3]+$t[4]+$t[5]-$t[6]-$t[7]+$t[8]-$t[11]+$t[10];
		$this->Cell($this->width[14], 4.5, $s, 0, 0, 'R');
		$this->posy += 4.5;
		$this->SetXY($this->left, $this->posy);
		$this->Cell($this->largeur, 0.01, '', 'T');
		if(!$mode) {
			$this->total = true;
			$this->SetFont('Arial','',9);
			$this->posy += 4.5;
			$this->SetXY($this->left, $this->posy);
			$this->Cell(58, 4.5, $this->cv("Nombre d'adhérents : ").$this->adherent);
			$this->Cell(58, 4.5, $this->cv('Sans cotisations : ').$this->sansCotis);
			$this->Cell(58, 4.5, $this->cv('Sans cours : ').$this->sansCours);
			//$this->posy += 4.5;
			//$this->SetXY($this->left, $this->posy);
			$this->Cell(58, 4.5, $this->cv("Nombre de dons : ").$this->nbDons);
			$this->Cell(58, 4.5, $this->cv('Nombre de soutiens : ').$this->nbSoutien);
			
			$this->SetFont('Arial','',9);
			$this->posy += 5;
			$this->SetXY($this->left, $this->posy);
			$this->Cell(30, 4.5, 'Dons :');
			foreach($this->tdons as $a=>$d) {
				$this->posy += 4.5;
				$this->SetXY($this->left, $this->posy);
				$this->Cell(4, 4.5, $a);
				$this->Cell(20, 4.5, $d);	
			}
		}
		else {
			if(!$this->cours) $this->sansCours++;
			for($i = 0; $i < 12; $i++) $this->ttotal[$i] += $this->total[$i];
			// dons / antennes
			if($this->total[5]) {
				$max = 0;
				$ant = '';
				foreach($this->dons as $a=>$n) {
					if($n > $max) {
						$max = $n;
						$ant = $a;
					}
				}
				$this->tdons[$ant] = (isset($this->tdons[$ant]) ? $this->tdons[$ant] : 0) + $this->total[5];
			}
		}
	}
	
	function PrintLines($regl) {
		$rid = -1;
		$this->SetFont('Arial','',9);
		foreach($regl as $r) {
			$id = $r['Id'];
			if($id != $rid) {
				if($rid > -1) $this->PrintTotal(1);
				$this->PrintAdherent($r);
				$rid = $id;
			}
			$this->printLine($r);
		}
		if($rid) $this->PrintTotal(1);
	}

	private function printAdherent($l) {
		$this->SetFont('Arial','B',9);
		$this->SetXY($this->left, $this->posy);
		$this->Cell($this->width[0], 4.5, $l['Numero']);
		$s = $this->cv($l['Nom'].' '.$l['Prenom']);
		$this->Cell($this->width[1], 4.5, $s);
		$this->posy += 4.5;
		
		$this->adherent++;
		if(!$l['Cotisation']) $this->sansCotis++;
		if($l['Dons']) $this->nbDons++;
		$this->cours = false;
		$this->total = [0,0,0,0,$l['Cotisation'],$l['Dons'],$l['Reglement'],$l['Differe'],$l['Regularisation'],
			$l['AvoirReporte'],$l['AvoirDu'],$l['AvoirUtilise']];
		$this->dons = array();
	}
	
	private function printLine($l) {
		$prx = $l['Prix'];
		$red = $l['Reduction'];
		$sou = $l['Soutien'];
		$sup = $l['Supprime'];
		
		// cours / antenne
		if($this->total[5]) {
			$ant = substr($l['CodeClasse'], 0, 1);
			$this->dons[$ant] = (isset($this->dons[$ant]) ? $this->dons[$ant] : 0) + 1;
		}
		
		$this->SetFont('Arial',($sup ? 'I' : ''),9);
		$this->SetXY($this->left, $this->posy);
		$this->Cell($this->width[0], 4.5, $l['CodeClasse']);
		$s = $this->cv(trim($l['libelleD'].' '.$l['LibelleN'].'   '.$l['Utilisateur']));
		$this->Cell($this->width[1], 4.5, $s);
		$this->Cell($this->width[2], 4.5, $prx ?: '', 0, 0, 'R');
		$this->Cell($this->width[3], 4.5, $red ?: '', 0, 0, 'R');
		if($sou && $sou != Cadref::$Soutien) $this->SetTextColor(255,0,0);
		$this->Cell($this->width[4], 4.5, $sou ?: '', 0, 0, 'R');
		$this->SetTextColor(0,0,0);
		switch($sup) {
			case 0:
				$t = &$this->total;
				$s = $prx-$red-$sou;
				$t[0] += $prx;
				$t[1] += $red;
				$t[2] += $sou;
				$t[3] += $s;
				break;
			case 1: $s = $this->cv('Supprimé'); break;
			case 2: $s = $this->cv('Echangé'); break;
		}
		$this->Cell($this->width[5], 4.5, $s, 0, 0, 'R');
		$this->posy += 4.5;
		
		if($l['CodeClasse']) $this->cours = true;
		if($l['Soutien'] && !$sup) $this->nbSoutien++;
	} 

}