<?php
class Reservation extends genericClass {
    var $_partenaires = array();
    var $_produits = array();
    var $_date = null;
    var $_heuredebut = null;
    var $_nbinvites = 0;

    /**
     * UTILS
     */
    function deleteLigneFacture($type){
        $tmp = $this->_produits;
        $this->_produits = array();
        foreach ($tmp as $k=>$p){
            if ($p->Type!=$type) array_push($this->_produits,$p);
        }
    }
    function addLigneFacture($libelle,$tarif,$quantite=1,$prod=null,$type='Service') {
        $ser = genericClass::createInstance('Reservations', 'LigneFacture');
        $ser->Libelle = $libelle;
        $ser->Type = $type;
        $ser->Quantite = $quantite;
        $ser->MontantUnitaireTTC = $tarif;
        $ser->MontantTTC = $tarif*$quantite;
        $ser->addParent($prod);
        array_push($this->_produits, $ser);
        return $ser;

    }

    /**
     * GETTER
     */
    function getFacture() {
        $total = $this->getTotal();
        $fact = Sys::getOneData('Reservations','Reservation/'.$this->Id.'/Facture');
        if ($fact) return $fact;
        elseif ($total >0){
            //création de la facture
            $fact = genericClass::createInstance('Reservations','Facture');
            $fact->MontantTTC = $total;
            $fact->MontantHT = $total/1.20;
            $fact->addParent($this->getClient());
            $fact->addParent($this);
            $fact->Save();

            //on attache aussi toutes les lignes facture
            $lf = Sys::getData('Reservations','Reservation/'.$this->Id.'/LigneFacture');
            foreach ($lf as $l) {
                $l -> addParent($fact);
                $l->Save();
            }

            return $fact;
        }
        return false;
    }
    function getLigneFacture() {
        if ($this->Id){
            //on cherche dans les parents en base
            return Sys::getData('Reservations','Reservation/'.$this->Id.'/LigneFacture');
        }else return $this->_produits;
    }
    function getPartenaires() {
        if ($this->Id){
            $out = array();
            //on cherche dans les parents en base
            $statuses = Sys::getData('Reservations','Reservation/'.$this->Id.'/StatusReservation');
            foreach ($statuses as $s){
                array_push($out,$s->getOneChild('Partenaire'));
            }
            if (count($out)) return $out;
        }else return $this->_partenaires;
    }
    function getClient() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getOneData('Reservations','Client/Reservation/'.$this->Id);
            if ($out) return $out;
        }
        if (!$out){
            //on cherche dans le tableau parrent temporaire.
            foreach ($this->Parents as $p){
                if ($p["Titre"]=='Client') {
                    return Sys::getOneData('Reservations','Client/'.$p["Id"]);
                }
            }
        }
        return false;
    }
    function getService() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getOneData('Reservations','Service/Reservation/'.$this->Id);
            if ($out) return $out;
        }
        if (!$out){
            //on cherche dans le tableau parent temporaire.
            if (is_array($this->Parents))foreach ($this->Parents as $p){
                if ($p["Titre"]=='Service') {
                    return Sys::getOneData('Reservations','Service/'.$p["Id"]);
                }
            }
        }
        return false;
    }
    function getCourt() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getOneData('Reservations','Court/Reservation/'.$this->Id);
            if ($out) return $out;
        }
        if (!$out){
            //on cherche dans le tableau parrent temporaire.
            foreach ($this->Parents as $p){
                if ($p["Titre"]=='Court') {
                    return Sys::getOneData('Reservations','Court/'.$p["Id"]);
                }
            }
        }
        return false;
    }

    /**
     * FRONT SETTER
     */
    /**
     * Définit la réservation
     */
    function checkReservation() {
        //ajout de la ligne de service
        $client = $this->getClient();
        $service = $this->getService();
        $court = $this->getCourt();
        $typecourt = $court->getParents('TypeCourt');
        $typecourt = $typecourt[0];
        if ($service) {
            //suppression si existante
            $this->deleteLigneFacture('Réservation');
            //ajout de la réservation
            $this->addLigneFacture($service->Titre, $service->getTarif($client, $this->DateDebut, $this->DateFin), 1, $service, 'Réservation');
        }
        
        //partenaires
        $nbabonnes = sizeof($this->getPartenaires());
        $this->_nbinvites;
        $this->deleteLigneFacture('Invitation');
        if ($service->TarifInvite>0&&$this->_nbinvites>0&$client->isSubscriber()
            || $service->TarifInvite>0&&$this->_nbinvites>0&&$typecourt->GestionInvite=="Quantitatif"){
            $this->addLigneFacture($service->Titre.' - Invitation',$service->TarifInvite,$this->_nbinvites,$service,'Invitation');
        }

        $this->NbParticipant = $this->_nbinvites+ $nbabonnes + 1;

        //définition du Nom de la réservation
        $this->Nom = $client->Nom.' '.$client->Prenom.' - '.$service->Titre; //date('d/m/Y à H:i',$this->DateDebut);
    }


    function setPartenaires($parts){
        $this->_partenaires = array();
        if (is_array($parts))foreach ($parts as $p){
            $p = (array)$p;
            if ($p['Client']>0) {
                //recherche du client
                $cli = Sys::getOneData('Reservations','Client/'.$p['Client']);
                $part = $cli->getChildren('Partenaire');
                if (!sizeof($part)){
                    //il faut le créer
                    $pa = genericClass::createInstance('Reservations', 'Partenaire');
                    $pa->Nom = $cli->Nom;
                    $pa->Email = $cli->Email;
                    $pa->Prenom = $cli->Prenom;
                    $pa->addParent($cli);
                    $pa->Save();
                }else{
                    $pa = $part[0];
                }
                array_push($this->_partenaires, $pa);
            }else $this->_nbinvites++;
        }
        if (!sizeof($parts)){
            $this->_nbinvites=1;
        }
    }
    function setPartenairesBis($parts){
        $cli = $this->getClient();

        //klog::l('$cli',$cli);

        $this->_partenaires = array();
        if (is_array($parts))foreach ($parts as $k=>$p){
            $p = (array)$p;
            if ($p['Client']>0) {
                //recherche du client
                $pa = Sys::getOneData('Reservations','Partenaire/'.$p['Client']);
                if(!$pa) {
                    $this->addError(array("Message"=>"Une erreur a été rencontrée avec le partenaire n°".$k." !"));
                    continue;
                }

                array_push($this->_partenaires, $pa);
            }else {
                if(isset($p['Email']) && $p['Email'] != null && $p['Email'] != ''){
                    $pa = Sys::getOneData('Reservations','Partenaire/Email='.$p['Email']);
                } else {
                    if(!isset($p['Prenom']) || $p['Prenom'] == null || $p['Prenom'] == '' || !isset($p['Nom']) || $p['Nom'] == null || $p['Nom'] == '') {
                        $this->addError(array("Message" => "Une erreur a été rencontrée avec le partenaire n°" . $k . " ! Vous devez au moins définir un Nom et un Prenom."));
                        continue;
                    }

                    $pa = Sys::getOneData('Reservations','Partenaire/Nom='.$p['Nom'].'&Prenom='.$p['Prenom']);
                }


                if(!$pa) {
                    $pa = genericClass::createInstance('Reservations', 'Partenaire');
                    $pa->Nom = $p['Nom'];
                    $pa->Email = $p['Email'];
                    $pa->Prenom = $p['Prenom'];

                    $pa->addParent($cli);
                    $pa->Save();

                    if(isset($p['Email']) && $p['Email'] != null && $p['Email'] != ''){
                        $pa = Sys::getOneData('Reservations','Partenaire/Email='.$p['Email']);
                    } else {
                        $pa = Sys::getOneData('Reservations','Partenaire/Nom='.$p['Nom'].'&Prenom='.$p['Prenom']);
                    }
                }

                $cli->addChild('Partenaire',$pa->Id);

                array_push($this->_partenaires, $pa);
            }
        }
        if (!sizeof($parts)){
            return false;
        }
    }

    function setNombrePartenaires($nb){
        $this->_nbinvites=$nb;
    }

    function setProduits($produit){
        foreach ((array)$produit as $i=>$p){
            if ($p>0) {
                $client = $this->getClient();
                //récupération du produit
                $prod = Sys::getOneData('Reservations', 'Service/' . $i);
//                $this->addLigneFacture($prod->Titre,$client->isSubscriber() ? $prod->TarifInvite:$prod->Tarif,$p,$prod,'Produit');
                $this->addLigneFacture($prod->Titre,$prod->Tarif,$p,$prod,'Produit');
            }
        }
    }
    function setClient($cli) {
        $this->addParent($cli);
    }
    function setCourt($court) {
        $this->addParent($court);
    }
    function setService($service) {
        $this->addParent($service);
    }
    function setDate($date){
        $this->_date = mktime(0,0,0,date('n', $date),date('j', $date),date('Y', $date));
    }
    function setHeureDebut($heure){
        //transformation de l'heure
        $t = explode(':',$heure);
        $sec = $t[0]*3600;
        $sec+= $t[1]*60;
        $this->_heuredebut = $sec;
    }

    /**
     * CHECKER
     */
    function checkClient() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getCount('Reservations','Client/Reservation/'.$this->Id);
            if ($out) return true;
        }
        if (!$out){
            //on cherche dans le tableau parrent temporaire.
            foreach ($this->Parents as $p){
                if ($p["Titre"]=='Client') return true;
            }
        }
        return false;
    }
    function checkCourt() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getCount('Reservations','Court/Reservation/'.$this->Id);
            if ($out) return true;
        }
        if (!$out){
            //on cherche dans le tableau parrent temporaire.
            foreach ($this->Parents as $p){
                if ($p["Titre"]=='Court') return true;
            }
        }
        return false;
    }
    function checkService() {
        $out=false;
        if ($this->Id){
            //on cherche dans les parents en base
            $out = Sys::getCount('Reservations','Service/Reservation/'.$this->Id);
            if ($out) return true;
        }
        if (!$out){
            //on cherche dans le tableau parrent temporaire.
            foreach ($this->Parents as $p){
                if ($p["Titre"]=='Service') return true;
            }
        }
        return false;
    }
    function checkDate() {
        $service = $this->getService();
        if (!$service) return false;

        if ($this->_date&&$this->_heuredebut){
            //récupération du service pour la durée

            //definition des heuredebut et heurefin
            $this->DateDebut = $this->_date + $this->_heuredebut;
            $this->DateFin = $this->DateDebut + $service->Duree*60; //TODO  verif mais je pense que ce n'est plus valide
            $this->_date = null;
            $this->_heuredebut = null;
        }
        //il faut également le court
        $court = $this->getCourt();
        if (!$court) return false;


        if ($this->DateDebut && $this->DateFin && $this->DateDebut < $this->DateFin){

            $service_debut = $service->HeureOuverture;
            $service_fin = $service->HeureFermeture;

            $jour_debut = date('Y/m/d',$this->DateDebut);
            $jour_fin = date('Y/m/d',$this->DateFin);


            $time_debut = DateTime::createFromFormat('Y/m/d H:i',$jour_debut.' '.$service_debut); //Debut periode du jour
            $time_debut = $time_debut->getTimestamp();
            $time_fin = DateTime::createFromFormat('Y/m/d H:i',$jour_debut.' '.$service_fin); //Fin periode du jour
            $time_fin = $time_fin->getTimestamp();

            if($this->DateDebut >= $time_debut && $this->DateDebut <= $time_fin && $this->DateFin >= $time_debut && $this->DateFin <= $time_fin){
                return true;
            }else return false;
        }else return false;
    }
    function checkDateJour() {
        if ($this->_date){
            //definition des heuredebut et heurefin
            $this->DateDebut = $this->_date;
            $this->DateFin = $this->DateDebut + 86400;
        }
        //il faut également le court
        $court = $this->getCourt();
        if (!$court) return false;

        if ($this->DateDebut && $this->DateFin){
            return true;
        }else return false;
    }
    function checkDispo() {

        //il faut également le court
        if ($this->Id&&$this->Valide)return true;
        $court = $this->getCourt();
        $tc = $court->getParents('TypeCourt');
        $tc = $tc[0];
        if (!$court) return false;


        if ($this->DateDebut && $this->DateFin){
            //RESERVATION
            if ($tc->Reservation=='Horaire') {

                //test du debut à cheval
                $out = Sys::getCount('Reservations', 'Court/' . $court->Id . '/Reservation/Valide=1&DateDebut>=' . $this->DateDebut . '&DateDebut<' . $this->DateFin .'&Id!='. $this->Id);
                if ($out) return false;
                //test de la fin à cheval
                $out = Sys::getCount('Reservations', 'Court/' . $court->Id . '/Reservation/Valide=1&DateFin>' . $this->DateDebut . '&DateFin<=' . $this->DateFin.'&Id!='. $this->Id);
                if ($out) return false;
                //test des deux en encadrement intérieur
                $out = Sys::getCount('Reservations', 'Court/' . $court->Id . '/Reservation/Valide=1&DateFin<=' . $this->DateFin . '&DateDebut>=' . $this->DateDebut.'&Id!='. $this->Id);
                if ($out) return false;
                //test les deux en encadrement extérieur
                $out = Sys::getCount('Reservations', 'Court/' . $court->Id . '/Reservation/Valide=1&DateFin>=' . $this->DateFin . '&DateDebut<=' . $this->DateDebut.'&Id!='. $this->Id);
                if ($out) return false;
            }


//klog::l('this',$this);

            //DISPONIBILITE
//            //test du debut à cheval
//            $out = Sys::getCount('Reservations','Court/'.$court->Id.'/Disponibilite/Dispo=0&Debut>='.$this->DateDebut.'&Debut<'.$this->DateFin);
//            if ($out) return false;
//            //test de la fin à cheval
//            $out = Sys::getCount('Reservations','Court/'.$court->Id.'/Disponibilite/Dispo=0&Fin>'.$this->DateDebut.'&Fin<='.$this->DateFin);
//            if ($out) return false;
//            //test des deux en encadrement intérieur
//            $out = Sys::getCount('Reservations','Court/'.$court->Id.'/Disponibilite/Dispo=0&Fin<='.$this->DateFin.'&Debut>='.$this->DateDebut);
//            if ($out) return false;
//            //test les deux en encadrement extérieur
//            $out = Sys::getCount('Reservations','Court/'.$court->Id.'/Disponibilite/Dispo=0&Fin>='.$this->DateFin.'&Debut<='.$this->DateDebut);
//            if ($out) return false;


            $dispos = Disponibilite::getDispo($this->DateDebut,$this->DateFin);
            $heuredebverif = (int)date('H',$this->DateDebut);
            $heurefinverif = (int)date('H',$this->DateFin);
            $minutedebverif = (int)date('i',$this->DateDebut);
            $minutefinverif = (int)date('i',$this->DateFin);

            $jourdebverif = date('d',$this->DateDebut);
            $jourfinverif = date('d',$this->DateFin);
            if($jourdebverif != $jourfinverif)  $heurefinverif +=24;
            
            
            foreach ($dispos as $dispo){
                $heuredeb = (int)date('H',$dispo->Debut);
                $heurefin = (int)date('H',$dispo->Fin);
                $minutedeb = (int)date('i',$dispo->Debut);
                $minutefin = (int)date('i',$dispo->Fin);

                $jourdeb = date('d',$dispo->Debut);
                $jourfin = date('d',$dispo->Fin);
                if($jourdeb != $jourfin)  $heurefin +=24;


                foreach($dispo->_courts as $courtD){
                    if($court->Id == $courtD->Id) { // si la dispo et le court correspondent
                        //Cas ou la periode correspond à la periode d'indisponobilité
                        if(
                            (($heuredebverif == $heuredeb && $minutedebverif <= $minutedeb) || $heuredebverif < $heuredeb) &&
                            (($heurefinverif == $heurefin && $minutefinverif >= $minutefin) || $heurefinverif > $heurefin)
                        ){
                            return false;
                        }

                        //Cas ou la periode verif "mange" la fin de la periode d'indisponobilité
                        if(
                            (($heuredebverif == $heuredeb && $minutedebverif > $minutedeb) || $heuredebverif > $heuredeb) &&
                            (($heuredebverif == $heurefin && $minutedebverif < $minutefin) || $heuredebverif < $heurefin) &&
                            (($heurefinverif == $heurefin && $minutefinverif >= $minutefin) || $heurefinverif > $heurefin)
                        ){
                            return false;
                        }
                        //Cas ou la periode verif "mange" le debut de la periode d'indisponobilité
                        if(
                            (($heuredebverif == $heuredeb && $minutedebverif <= $minutedeb) || $heuredebverif < $heuredeb) &&
                            (($heurefinverif == $heuredeb && $minutefinverif > $minutedeb) || $heurefinverif > $heuredeb) &&
                            (($heurefinverif == $heurefin && $minutefinverif < $minutefin) || $heurefinverif < $heurefin)
                        ){
                            return false;
                        }

                        //Cas ou la periode verif "mange" le milieu de la periode d'indisponobilité
                        if(
                            (($heuredebverif == $heuredeb && $minutedebverif > $minutedeb) || $heuredebverif > $heuredeb) &&
                            (($heuredebverif == $heurefin && $minutedebverif < $minutefin) || $heuredebverif < $heurefin) &&
                            (($heurefinverif == $heuredeb && $minutefinverif > $minutedeb) || $heurefinverif > $heuredeb) &&
                            (($heurefinverif == $heurefin && $minutefinverif < $minutefin) || $heurefinverif < $heurefin)
                        ){
                            return false;
                        }
                    }
                }
            }

            return true;
        }else return false;
    }

    function Verify() {
        if(strpos($this->DateDebut,'/') || strpos($this->DateDebut,':')){
           // klog::l($this->DateDebut.PHP_EOL);
            $datetime = DateTime::createFromFormat('d/m/Y H:i',$this->DateDebut);
            $this->DateDebut = $datetime->getTimestamp();
           // klog::l($this->DateDebut.PHP_EOL);
        }
        if(strpos($this->DateFin,'/') || strpos($this->DateFin,':')){
           // klog::l($this->DateFin.PHP_EOL);
            $datetime = DateTime::createFromFormat('d/m/Y H:i',$this->DateFin);
            $this->DateFin = $datetime->getTimestamp();
           // klog::l($this->DateFin.PHP_EOL);
        }

        if (!$this->checkClient()){
            $this->addError(array("Message"=>"Veuillez saisir le client pour cette réservation"));
        }
        if (!$this->checkCourt()){
            $this->addError(array("Message"=>"Veuillez saisir le court pour cette réservation"));
        }
        //on récupère le type de court
        $court= $this->getCourt();
        if ($court) {
            $tc = $court->getParents('TypeCourt');
            $tc = $tc[0];
            if ($tc->Reservation == 'Horaire') {
                if (!$this->checkDate()) {
                    $this->addError(array("Message" => "Veuillez vérifier la saisie des dates, ainsi que la durée choisie"));
                }
                //vérifie le client
                if (!$this->checkService()) {
                    $this->addError(array("Message" => "Veuillez saisir le service pour cette réservation"));
                }
                if (!$this->checkDispo()) {
                    $this->addError(array("Message" => "Cette horaire n'est pas disponible"));
                    $this->Valide = false;
                }
            } else {
                if (!$this->checkDateJour()) {
                    $this->addError(array("Message" => "Veuillez vérifier la saisie de la date"));
                }
                if (!$this->checkDispo()) {
                    $this->addError(array("Message" => "Ce jour n'est pas disponible"));
                    $this->Valide = false;
                }
            }
        }
        if (sizeof($this->Error)) return false;

        //si tout est ok alors on configure la réservation
        $this->checkReservation();

        return parent::Verify();
    }

    function Save(){
        $this->Verify();
        if (sizeof($this->Error)) return false;

        $new = false;
        if (!$this->Id) {
            $new = true;
        }
        parent::Save();
        if ($new){
            //Ajout d'une ligne pour la réservation
            $service = $this->getService();
            $client = $this->getClient();

            //Saisie des produits
            if (is_array($this->_produits)) foreach ($this->_produits as $p) {
                //creation du partenaire
                $p->AddParent($this);
                $p->Save();
            }

            $ligne = $this->getOneChild('LigneFacture/Type=Reservation');
            $montant = $ligne->MontantTTC;
            if (is_array($this->_partenaires)){
                $nb_participant = sizeof($this->_partenaires);
                $nb_participant++;
                $montantPart = $montant / $nb_participant;
            }

            //Saisie des partenaires.
            if (is_array($this->_partenaires)) foreach ($this->_partenaires as $p) {
                //creation du partenaire
                $s = genericClass::createInstance('Reservations', 'StatusReservation');
                $s->Nom = 'Status_R'.$this->Id.'_P'.$p->Id;
                if($this->PaiementParticipant){
                    $s->MontantPaye=$montantPart;
                }else{
                    $s->Paye =true;
                }
                $s->AddParent($this);
                $s->Save();

                $p->AddParent($s);
                $p->Save();
            }

        }

        $cli = $this->getClient();
        if($this->Valide){
            $status = $this->getChildren('StatusReservation');

            foreach($status as $s){
                if($s->MailEnvoye) continue;
                $p = $s->getOneChild('Partenaire');
                $p->sendReservationMail($this,$s);
                $s->MailEnvoye = true;
                $s->Save();
            }
        }
        //enregistrement des jourresa.
        $jourdeb = date('d',$this->DateDebut);
        $moisdeb = date('m',$this->DateDebut);
        $anneedeb = date('Y',$this->DateDebut);
        $c = $this->getOneParent('Court');

        //recherche des journées correspondantes.
        $jrs = Sys::getData('Reservations', 'Court/' . $c->Id . '/ResaJour/Date=' . mktime(0, 0, 0, $moisdeb, $jourdeb, $anneedeb));
        foreach ($jrs as $jr) {
            $this->addParent($jr);
        }

        //enregistrement de la réservation
        return parent::Save();
    }

    function Delete() {
        if ($this->Id) {
            //suppression des lignesfactures
            $prods = Sys::getData('Reservations', 'Reservation/'.$this->Id.'/LigneFacture');
            foreach($prods as $p) $p->Delete();

            //suppression de la facture
            $facts = Sys::getData('Reservations', 'Reservation/'.$this->Id.'/Facture');
            foreach($facts as $f) $f->Delete();

            //suppression des status après envoi de mail pour prevenir
            $status = Sys::getData('Reservations', 'Reservation/'.$this->Id.'/StatusReservation');
            foreach($status as $s) {
                $p = $s->getOneChild('Partenaire');
                if($p && $s->MailEnvoye) $p->sendAnnulationMail($this);
                $s->Delete();
            }

            $cli = $this->getClient();
            $cli->sendAnnulationMail($this);

            parent::Delete();
        }
    }
    function getTotal() {
        $cli = $this -> getClient();
        //if($cli->Abonne) return 0;


        $lf = $this->getLigneFacture();
        $total = 0;
        foreach ($lf as $l){
            $total+= $l->MontantTTC;
        }
        return $total;
    }

    /**
     * sendSms
     * Envoi un sms
     */
    function sendSms($message) {
        $cli = $this -> getClient();
        if (!empty($cli->Tel)) {
            require_once("Class/Lib/Isendpro/autoload.php");
            $api_instance = new Isendpro\Api\SmsApi();
            $api_instance->getApiClient()->getConfig()->setSSLVerification(false);
            $smsrequest = new Isendpro\Model\SmsUniqueRequest();
            $smsrequest["keyid"] = SMS_API;
            $smsrequest["num"] = $cli->Tel;
            $smsrequest["sms"] = $message;
            try {
                $result = $api_instance->sendSms($smsrequest);
                //print_r($result);
            } catch (Exception $e) {
                /*print_r($smsrequest);
                echo 'Exception when calling SmsApi->sendSms: ',print_r($e), PHP_EOL;
                print_r($e->getResponseObject());
                die('ERREUR');*/
            }
        }
    }

    function setPending() {
        $this->Valide=0;
        $this->Attente=1;
        $this->Save();

        //envoi du mail
        $this->sendPendingMail();
    }
    function sendPendingMail() {
        $cli = $this -> getClient();

        $this->sendSms("Bonjour, nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " est actuellement en attente de validation. La mairie d'Amberieu.");

        $Civilite = $cli -> Civilite . " " . $cli -> Prenom . ' <span style="text-transform:uppercase">' . $cli -> Nom . '</span>';


        require_once ("Class/Lib/Mail.class.php");
        $Mail = new Mail();
        $Mail -> Subject("Mairie d'Amberieu : Accusé réception de votre demande de réservation");
        $Mail -> From( $GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> ReplyTo($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> To($cli -> Mail);
        //$Mail -> To('enguer@enguer.com');
        $Mail -> Bcc('gcandella@abtel.fr');
        $Mail -> Cc($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $bloc = new Bloc();

        $mailContent = "
            Bonjour " . $Civilite . ",<br /><br />
            Nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " est actuellement en attente de validation. Nous vous informerons de toute évolution.<br />
            <br />Toute l'équipe de la mairie d'Amberieu vous remercie de votre confiance,<br />
            <br />Pour nous contacter : " . $GLOBALS['Systeme']->Conf->get('MODULE::RESERVATIONS::CONTACT') . " .";


        $bloc -> setFromVar("Mail", $mailContent, array("BEACON" => "BLOC"));
        $Pr = new Process();
        $bloc -> init($Pr);
        $bloc -> generate($Pr);
        $Mail -> Body($bloc -> Affich());
        $Mail -> Send();
    }


    /**
     * Annulation / Refus de la réservation
     */
    function setInvalid() {
        $this->Valide=0;
        $this->Attente=0;
        if($this->Save()){
            //envoi du mail
            $this->sendInvalidMail();
        }
    }
    function sendInvalidMail() {
        $cli = $this -> getClient();


        $this->sendSms("Bonjour, nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " ne pourra être honorée. La mairie d'Amberieu.");


        $Civilite = $cli -> Civilite . " " . $cli -> Prenom . ' <span style="text-transform:uppercase">' . $cli -> Nom . '</span>';


        require_once ("Class/Lib/Mail.class.php");
        $Mail = new Mail();
        $Mail -> Subject("Mairie d'Amberieu : Annulation de réservation");
        $Mail -> From( $GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> ReplyTo($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> To($cli -> Mail);
        //$Mail -> To('enguer@enguer.com');
        $Mail -> Bcc('gcandella@abtel.fr');
        $Mail -> Cc($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $bloc = new Bloc();

        $mailContent = "
            Bonjour " . $Civilite . ",<br /><br />
            Nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " ne pourra être honorée pour la raison suivante:<br />
            ".$this->Commentaire."
            <br />Toute l'équipe de la mairie d'Amberieu vous prie de bien vouloir accepter ses excuses pour le dérangement occasionné,<br />
            <br />Pour nous contacter : " . $GLOBALS['Systeme']->Conf->get('MODULE::RESERVATIONS::CONTACT') . " .";


        $bloc -> setFromVar("Mail", $mailContent, array("BEACON" => "BLOC"));
        $Pr = new Process();
        $bloc -> init($Pr);
        $bloc -> generate($Pr);
        $Mail -> Body($bloc -> Affich());
        $Mail -> Send();
    }

    /**
     * Validation de la réservation
     */
    function setValide() {
        $this->Valide=1;
        $this->Attente=1;
        $this->Save();
        //TODO
        //envoi du mail
        $this->sendMail();
        //envoi de la notification
        $msg = array('title' => 'Réservation confirmée',
            'message' => 'Votre réservation a été validée avec succès',
            'store' => 'Reservations',
            'vibrate' => 1,
            'sound' => 1);
        Systeme::sendNotification($msg, Sys::$User->Id);
    }

    function sendMail() {
        $cli = $this -> getClient();
        $usr = Sys::getOneData('Systeme','User/'.$cli->UserId);


        $Civilite = $cli -> Civilite . " " . $cli -> Prenom . ' <span style="text-transform:uppercase">' . $cli -> Nom . '</span>';

        if($this->getTotal() >0) {

            $lf = $this->getLigneFacture();
            if (!sizeof($lf)) {
                $Lacommande = "Erreur";
            } else {
                $Lacommande = "<br /> Vous avez déclaré $this->NbParticipant participant(s) au total ";
                $parts = $this->getPartenaires();
                if (sizeof($parts)) {
                    $Lacommande .= "<br /> Vous jouerez avec le(s) partenaire(s) suivant(s): <ul>";
                    foreach ($parts as $p) {
                        $Lacommande .= "<li>$p->Nom $p->Prenom</li>";
                    }
                    $Lacommande .= "</ul>";
                }
                $Lacommande .= "<h2>Récapitulatif de votre réservation  : </h2><br /><br /><table width='100%'>";
                $Lacommande .= "<tr bgcolor='#666' padding='5'><td><font color='#ffffff'>Quantite</font></td><td><font color='#ffffff'>Titre</font></td><td><font color='#ffffff'>Tarif TTC</font></td></tr>";
                $total = 0;
                foreach ($lf as $l) :
                    //récupération du produit
                    $Lacommande .= "<td><h3>" . $l->Quantite . "</h3> </td>";
                    $Lacommande .= "<td><h3>" . $l->Libelle . "</h3></td>";
                    $Lacommande .= "<td><h2>" . $l->MontantTTC . " € TTC</h2></td></tr>";
                    $total += $l->MontantTTC;
                endforeach;
                $Lacommande .= "
                    <tr bgcolor='#666' padding='5'>
                        <td colspan='2'></td>
                        <td><font color='#ffffff'>TOTAL</font></td>
                        <td><font color='#ffffff'><h2>$total € TTC</h2></font></td>
                    </tr>
                </table>";

            }
        }else{
            $Lacommande = '';
        }

        $this->sendSms("Bonjour, nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " a bien été prise en compte. La mairie d'Amberieu.");


        require_once ("Class/Lib/Mail.class.php");
        $Mail = new Mail();
        $Mail -> Subject("Mairie d'Amberieu : Confirmation de réservation");
        $Mail -> From( $GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> ReplyTo($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $Mail -> To($cli -> Mail);
        //$Mail -> To('enguer@enguer.com');
        $Mail -> Bcc('gcandella@abtel.fr');
        $Mail -> Cc($GLOBALS['Systeme'] -> Conf -> get('MODULE::RESERVATIONS::CONTACT'));
        $bloc = new Bloc();

        if($usr->Privilege) {
            $mailContent = "
                Bonjour ,<br /><br />
                Nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " a bien été prise en compte.<br />";
            $parts = $this->getPartenaires();
            if (sizeof($parts)) {
                $mailContent .= "<br /> Partenaire(s) inscrit(s): <ul>";
                foreach ($parts as $p) {
                    $mailContent .= "<li>$p->Nom $p->Prenom</li>";
                }
                $mailContent .= "</ul>";
            }
        } else {
            $mailContent = "
                Bonjour " . $Civilite . ",<br /><br />
                Nous vous informons que votre réservation N° " . $this->Id . " pour le " . date("d/m/Y à H:i", $this->DateDebut) . " a bien été prise en compte.<br />
                <br />Toute l'équipe de la mairie d'Amberieu  vous remercie de votre confiance,<br />
                <br />Pour nous contacter : " . $GLOBALS['Systeme']->Conf->get('MODULE::RESERVATIONS::CONTACT') . " ." . $Lacommande;
        }

        $bloc -> setFromVar("Mail", $mailContent, array("BEACON" => "BLOC"));
        $Pr = new Process();
        $bloc -> init($Pr);
        $bloc -> generate($Pr);
        $Mail -> Body($bloc -> Affich());
        if (!$this->Cloture)
            $Mail -> Send();
    }


    function sendRappel(){
        $status = $this->getChildren('StatusReservation');
        $cli = $this->getClient();

        foreach ($status as $s){
            if(!$s->MailEnvoye) continue;
            $p = $s->getOneChild('Partenaire');
            $p->sendRappelMail($this);
        }

        $cli->sendRappelMail($this);
    }

}