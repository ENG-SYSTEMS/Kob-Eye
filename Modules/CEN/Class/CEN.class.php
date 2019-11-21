<?php

class CEN extends Module {
	
	// description d'une application
	public static function GetPresentation($args) {
		$o = Sys::getOneData('CEN', 'Presentation/Code='.$args['code']);
		switch($args['lang']) {
			case 'es': $pres = $o->TexteEs; break;
			case 'fr': $pres = $o->TexteFr; break;
			case 'en': $pres = $o->TexteEn; break;
		}
		return array('presentation'=>$pres);
	}


	// liste d'entrées du GDN 
	public static function GetGDN($args) {	
		switch($args['mode']) {
			case 'genor':
				return Temoa::GetGenor($args);
				
			case 'temoa':
				return Temoa::GetList($args);
				
			case 'docs':
				return Temoa::GetDocs();
				
			case 'doc':
				return Temoa::GetDocument($args);
				
			case 'notes':
				return Temoa::GetNotes($args);

			case 'picts':
				return Temoa::GetPicts($args);

			case 'temoa-trad':
				return Temoa::GetTraduction($args);
				
			case 'dict':
				$dics = Sys::getData('CEN', 'Dictionnaire');
				$dicId= array();
				$dic = array();
				foreach($dics as $d) {
					$id = $d->Id;
					$dic[] = array('id'=>$d->Id, 'title'=>$d->Nom, 'selected'=>1);
					$dicId[$d->Id] = $d->Nom;
				}
				return array('dictionariesId'=>$dicId, 'dictionaries'=>$dic);
				
			case 'list':
				return GDN::GetList($args);
				
			case 'trad':
				return GDN::GetGDN($args);
				
			case 'pres':
				switch($args['pres']) {
					case 'dic':	$dic = Sys::getOneData('CEN', 'Dictionnaire/'.$args['id']);	break;
					case 'doc':	$dic = Sys::getOneData('CEN', 'Temoa/'.$args['id']);	break;
					case 'comm': return GDN::GetComments($args);
					case 'doc-read':
						$dic = Sys::getOneData('CEN', 'Temoa/'.$args['id']);	
						return array('text'=>$dic->DocHtml);
				}
				switch($args['lang']) {
					case 'es': $pres = $dic->PresentationEs; break;
					case 'fr': $pres = $dic->PresentationFr; break;
					case 'en': $pres = $dic->PresentationEn; break;
				}
				if(empty($pres)) $pres = $dic->PresentationEs;
				return array('text'=>$pres);
				
			case 'norm':
				break;
		}
	}

	
	
}