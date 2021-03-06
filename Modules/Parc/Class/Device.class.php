<?php
require_once 'Class/Lib/Zabbix.class.php';

class Device extends genericClass{

    /*
     *
     * @override
     */
    function save(){
        if (parent::save()){
            //checking port redirect
            $this->checkRedirectPort();
            //check base connection creation
            $this->checkBaseConnection();
            return true;
        }
        return false;
    }

    /**
     * delete
     * @return bool
     */
    public function Delete() {
        $conns = $this->getChildren('DeviceConnexion');
        foreach ($conns as $conn) $conn->Delete();
        $ports = $this->getChildren('DevicePort');
        foreach ($ports as $port) $port->Delete();
        $tasks = $this->getChildren('DeviceTask');
        foreach ($tasks as $task) $task->Delete();
        parent::Delete();

        return true;
    }

    private function checkRedirectPort(){
        $ports = $this->getChildren('DevicePort');
        switch ($this->OS){
            case "Windows":
                if (sizeof($ports)>=3)return true;
                //RDP
                $port = genericClass::createInstance('Parc','DevicePort');
                $port->PortRedirectLocal =  12000 + $this->Id;
                $port->PortRedirectDistant =  3389;
                $port->IpRedirectDistant = 'localhost';
                $port->addParent($this);
                $port->Save();
                //VNC
                $port = genericClass::createInstance('Parc','DevicePort');
                $port->PortRedirectLocal =  22000 + $this->Id;
                $port->PortRedirectDistant =  15900;
                $port->IpRedirectDistant = 'localhost';
                $port->addParent($this);
                $port->Save();
                //MSG
                $port = genericClass::createInstance('Parc','DevicePort');
                $port->PortRedirectLocal =  32000 + $this->Id;
                $port->PortRedirectDistant =  15902;
                $port->IpRedirectDistant = 'localhost';
                $port->addParent($this);
                $port->Save();
                break;
        }
    }


    function getVersion($uuid) {
        /*if(!isset($uuid))
            return false;*/
        $prod = true;
        $Commands = "";
        //recherche de la machine
        $dev = Sys::getOneData('Parc','Device/Uuid='.$uuid);
        if (!$dev&&isset($uuid)){
            //si machine existe pas on la créé
            $this->getConfig($uuid);
            $dev = Sys::getOneData('Parc','Device/Uuid='.$uuid);
        }
        if ($dev){
            //if(!$dev->Online) Zabbix::enableOnline($dev->Uuid);
            //mise à jour de la machine
            $dev->LastSeen = time();
            $dev->PublicIP = $_SERVER['REMOTE_ADDR'];
            $dev->Online = true;

            if ($dev->ModeTest) $prod=false;

            //Rafraichissement de la version du dev si besoin
            if (isset($_GET['version'])&&$_GET['version']!=$dev->CurrentVersion){
                $dev->CurrentVersion = $_GET['version'];
            }

            // raffraichissement du NS du BIOS
            if (isset($_GET['bios'])&&$_GET['bios']!=$dev->SerialNumber){
                $dev->SerialNumber = $_GET['bios'];
            }

            //Gestion des erreurs
            if (isset($_GET['error'])&&sizeof($_GET['error'])>1){
                $dev->Erreur = true;
                $err = explode(';',$_GET['error']);
                foreach ($err as $e){
                    switch ($e){
                        case 'tunnel':
                            $this->DetailErreur.="\r\n ".date('d/m/Y H:i:s')." Le tunnel a échoué et tente de redémarrer.";
                            break;
                        case 'vnc':
                            $this->DetailErreur.="\r\n ".date('d/m/Y H:i:s')." Le service VNC a échoué et tente de redémarrer.";
                            break;
                        case 'connexion':
                            $this->DetailErreur.="\r\n ".date('d/m/Y H:i:s')." La connexion a été interrompue.";
                            break;
                        default:
                            break;
                    }
                }
            }else{
                $dev->Erreur = false;
            }


            //Gestion des commandes
            if ($dev->RestartTunnel){
                $Commands = "\r\nCommande=tunnel";
                $dev->RestartTunnel = false;
            }

            if ($dev->RestartZabbix){
                $Commands = "\r\nCommande=zabbix";
                $dev->RestartZabbix = false;
            }

            $dev->Save();
        }

        //recherche d'un tache à accomplir
        $t = $dev->getChildren('DeviceTask/Enabled=1');
        if (sizeof($t)>0){
            $task = 'http://'.Sys::$domain.'/Parc/DeviceTask/'.$t[0]->Id.'/getTask.json';
        }else $task = '';
        //recherche de la version
        $log = Sys::getOneData('Parc','LogicielVersion/Release='.$prod);
        if (!$log) $log = Sys::getOneData('Parc','LogicielVersion/Release='.!$prod);

        $cos=$dev->getChildren('DevicePort');
        $Redirect = "Ports=";
        //TODO : checker les doublons pour modif de mot de passe
        foreach ($cos as $co){
            $ip = $co->IpRedirectDistant!=''?$co->IpRedirectDistant:'localhost';
            $Redirect .= 'R'.$co->PortRedirectLocal.'='.$ip.':'.$co->PortRedirectDistant.',';
        }
        $Redirect = rtrim($Redirect,',');


        return "Version=$log->Version
Install=http://".Sys::$domain."/$log->InstallFile
Service=http://".Sys::$domain."/$log->ServiceFile
Tunnel32=http://".Sys::$domain."/$log->TunnelFile
Tunnel64=http://".Sys::$domain."/$log->TunnelFile64
Vnc32=http://".Sys::$domain."/$log->VncFile
Vnc64=http://".Sys::$domain."/$log->VncFile64
VncDll32=http://".Sys::$domain."/$log->VncDllFile
VncDll64=http://".Sys::$domain."/$log->VncDllFile64
ZabbixAgent32=http://".Sys::$domain."/$log->ZabbixAgent32
ZabbixAgent64=http://".Sys::$domain."/$log->ZabbixAgent64
Guardian=http://".Sys::$domain."/$log->Guardian
Info=http://".Sys::$domain."/$log->Info
TunnelSsl=http://".Sys::$domain."/$log->TunnelSsl
Client=$dev->CodeClient
Computer=$dev->Nom
Type=$dev->DeviceType
Task=$task
".$Redirect."
";


    }

    function getConfig($uuid) {
        if (empty($uuid)) return;

        $exists = Sys::getOneData('Parc','Device/Uuid='.$uuid);
        if ($exists){
            $exists->Nom = $_GET["name"];
            $exists->Description = $_GET["os"];
            if(isset($_GET["machine"]))
                $exists->DeviceType = ($_GET["machine"]=='station')?'Poste':'Serveur';
            else $exists->DeviceType = 'Poste';
            $obj = $exists;
        }else{
            //creation du device
            $obj = genericClass::createInstance('Parc','Device');
            if(!isset($_GET["name"])){
                $obj->Nom = 'Noname_'.$uuid;
            } else{
                $obj->Nom = $_GET["name"];
            }
            if(!isset($_GET["os"])){
                $obj->Description = 'OS non spécifié';
            } else{
                $obj->Description = $_GET["os"];
            }
            if(isset($_GET["machine"]))
                $obj->DeviceType = ($_GET["machine"]=='station')?'Poste':'Serveur';
            else $obj->DeviceType = 'Poste';
            $obj->Uuid = $uuid;
            //klog::l('$obj',$obj);
        }

        //affectation du client
        $client = isset($_GET["clientid"])? $_GET["clientid"] : false;
        if (!empty($client)){
            $obj->CodeClient = $client;
            if (is_int($client)) {
                $cli = Sys::getOneData('Parc', 'Client/Id=' . $client);
            }else{
                $cli = Sys::getOneData('Parc','Client/CodeGestion='.$client);
            }
            if ($cli) {
                $obj->addParent($cli);
            }
            //if($cli->CodeGestion=='ADMR') $obj->ModeTest = true;

            $obj->Save();
        }else die('CLIENT INTROUVABLE');
        return true;
    }

    /**
     * check client name and device hostname while installing ALS
     */
    function checkClient($uuid) {
        //check client
        if (!Sys::getCount('Parc', 'Client/CodeGestion=' . $_GET["client"])){
            return 'client';
        }
        //check hostname
        if (Sys::getCount('Parc', 'Device/Uuid!='.$uuid.'&Nom=' . $_GET["system"])){
            return 'system';
        }
        return 'ok';
    }



    public static function getOffline(){
        //Mise à jour des devices offline
        $devs = Sys::getData('Parc','Device/Online=1&&LastSeen<'.(time()-600));
        foreach ($devs as $d) {
            $d->Online = false;
            $d->Save();
           //TODO Zabbix desactivé le temps de stabiliser l'acces au serveur via proxy
            //Zabbix::disableOffline($d->Uuid);
        }
    }

    public function checkBaseConnection(){

        switch ($this->OS){
            case "Windows":
                $rdp = $this->getOneChild('DeviceConnexion/Type=RDP&(!PortRedirectLocal='.(12000 + $this->Id).'+PortRedirectLocal='.(32000 + $this->Id).'!)');
                $vnc = $this->getOneChild('DeviceConnexion/PortRedirectLocal='.(22000 + $this->Id).'&Type=VNC');

                if(!isset($rdp) || !$rdp){
                    $coRdp = genericClass::createInstance('Parc','DeviceConnexion');
                    $coRdp->Nom = $this->Nom . "_rdp";
                    $coRdp->Type = 'RDP';
                    $coRdp->PortRedirectLocal = 12000 + $this->Id;
                    $coRdp->addParent($this);
                    $coRdp->Save();
                }
                if(!isset($vnc) || !$vnc){
                    $coVnc = genericClass::createInstance('Parc','DeviceConnexion');
                    $coVnc->Nom = $this->Nom . "_vnc";
                    $coVnc->Type = 'VNC';
                    $coVnc->PortRedirectLocal = 22000 + $this->Id;
                    $coVnc->addParent($this);
                    $coVnc->Save();
                }
                break;
        }
    }
    /**
     * getIps
     * retourne la liste des ips actives du poste
     */
    public function getIps() {
        $out = Zabbix::getInterface($this->CodeClient.' '.$this->Nom);
        //print_r($out);
        return $out;
    }

    public function callRemoteInfo($mess = "ALERTUn technicien ABTEL vient de se connecter\x06"){
        $ip = '10.0.189.12';
        $log = new Klog('Log/socket.log');
        $port = Sys::getOneData('Parc','Device/'.$this->Id.'/DevicePort/PortRedirectDistant=15902');
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        $time = time();
        $timeout = 2;
        $log->log('Connexion mystere '.$ip.':'.$port->PortRedirectLocal);
        while (!socket_connect($socket, $ip, $port->PortRedirectLocal)) {
            $log->log('Socket retry '.time());
            $err = socket_last_error($socket);
            $errormsg = socket_strerror($err);
            if ((time() - $time) >= $timeout) {
                socket_close($socket);
                throw new Exception('Erreur de connexion socket '.$errormsg);
            }
            $log->log('error '.$err);
            sleep(1);
        }
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array('sec'=>$timeout,'usec'=>0));
        $toto = socket_send($socket,$mess,strlen($mess),MSG_DONTROUTE);
        if($toto === false || $toto === null)  {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            $log->log('Erreur send '.$errormsg);
        }

        /***************************
         * RESULTAT
         */
        $log->log('Resultat');
        //On lit le résultat
        $res = '';
        while(true){
            $return = socket_recv($socket, $buf, 1024, 0);
            $res .= $buf;
            $log->log('attends pour le resultat');

            if($return == false) break;
            if(strpos($buf, "\x06") !== FALSE){
                $res = str_replace("\x06",'',$res);
                break;
            }
        }

        if($buf === false || $return === false)  {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            $log->log('erreur mystere '.$errormsg);
        }

        //Fermeture du socket
        socket_close($socket);


        //traitement du retour
        $infos = utf8_encode($res);
        $infos = $res;
        //if (empty(trim($infos)))$infos= 'OK';
        return nl2br($infos);

    }

    public function sendStatus($uuid,$status){
        if (empty($uuid)) return;

        $exists = Sys::getOneData('Parc','Device/Uuid='.$uuid);
        if ($exists) {
            $exists->Status = $status;
            $exists->Save();
        }
    }
}