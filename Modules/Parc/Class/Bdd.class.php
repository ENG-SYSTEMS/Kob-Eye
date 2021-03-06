<?php
class Bdd extends genericClass {
    public function Save () {
        $this->Nom = Bdd::checkName($this->Nom);
        $old = Sys::getOneData('Parc','Bdd/'.$this->Id);
        //test de modification du ApacheServerName
        if ($this->Id &&$old->Nom!=$this->Nom){
            $this->addError(array("Message"=>"Impossible de modifier le nom de la base de donnée. Si c'est nécessaire, veuillez supprimer et recréer cette base de donnée en réimportant vos données."));
            return false;
        }

        parent::Save();
        //$serv = $this->getOneParent('Server');
        $serv = Sys::getOneData('Parc','Server/Bdd/'.$this->Id,null,null,null,null,null, null,true);
        if (!$serv) {

            $host = Sys::getOneData('Parc','Host/Bdd/'.$this->Id,null,null,null,null,null, null,true);
            if (!$host){
                $this->addError(array('Message'=>'Impossible de trouver un hote associé'));
                //parent::Delete();
                return false;
            }
            $infra = $host->getInfra();
            $pref = '';
            if($infra)
                $pref = 'Infra/'.$infra->Id.'/';

            $serv = Sys::getOneData('Parc',$pref.'Server/defaultSqlServer=1',null,null,null,null,null, null,true);
            $this->addParent($serv);
            parent::Save();
        }
        if ($this->checkDatabase())
            return true;
        else return false;
    }
    public function Verify() {
        $old = Sys::getOneData('Parc','Bdd/'.$this->Id);
        //test de modification du ApacheServerName
        if ($this->Id &&$old->Nom!=$this->Nom){
            $this->addError(array("Message"=>"Impossible de modifier le nom de la base de donnée. Si c'est nécessaire, veuillez supprimer et recréer cette base de donnée en réimportant vos données."));
            return false;
        }
        return parent::Verify();
    }
    public function checkDatabase(){
        $serv = Sys::getOneData('Parc','Server/Bdd/'.$this->Id,null,null,null,null,null, null,true);
        $host = $this->getOneParent('Host');
        $ip = ($serv->usePublicIP)?$serv->IP:$serv->InternalIP;
        if (!is_object($serv)) return false;
        try {
            $dbGuac = new PDO('mysql:host=' . $ip . ';port='.$serv->SqlPort.';dbname=mysql', $serv->SqlUser, $serv->SqlPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $dbGuac->query("SET AUTOCOMMIT=1");
            $dbGuac->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //flush privileges
            $query = "FLUSH PRIVILEGES;";
            $dbGuac->query($query);

            //vérification de l'existence de l'utilisateur
            $query = "SELECT COUNT(*) FROM user WHERE User='" . $host->NomLDAP . "' AND Host='%';";
            $q = $dbGuac->query($query);
            $result = $q->fetchALL(PDO::FETCH_ASSOC);
            if (!$result[0]["COUNT(*)"]) {
                $query = "CREATE USER '" . $host->NomLDAP . "'@'%' IDENTIFIED BY '" . $host->Password . "';";
                //$this->addWarning(array('Message'=>'sql: '.$query));
                $dbGuac->query($query);
            }else{
                //sinon modification du mot de passe.
                //$query = "ALTER USER '" . $host->NomLDAP . "'@'%' IDENTIFIED BY '" . $host->Password . "';";
                $query = "SET PASSWORD FOR '" . $host->NomLDAP . "'@'%' = PASSWORD('" . $host->Password . "');";
                //$this->addWarning(array('Message'=>'sql: '.$query));
                $dbGuac->query($query);
            }

            //creation de la base de donnée et obtention des droits
            $query = "CREATE DATABASE IF NOT EXISTS `" . $this->Nom."`;";
            $dbGuac->query($query);
            $query = "GRANT ALL PRIVILEGES ON `" . $this->Nom . "` . * TO '" . $host->NomLDAP . "'@'%';";
            $dbGuac->query($query);

            //flush privileges
            $query = "FLUSH PRIVILEGES;";
            $dbGuac->query($query);
        }catch (Exception $e){
            $this->addError(Array("Message"=>"Erreur de base de donnée: ".$e->getMessage()));
            //$this->Delete(true);
            return false;
        }
        return true;
    }
    public function Delete($silent = false) {
        if ($this->removeFromDatabase($silent))
            parent::Delete();
        else {
            //$this->AddError(Array("Message"=> "Impossible de supprimer la base de donnée"));
            parent::Delete();
            return false;
        }
        return true;
    }
    private function removeFromDatabase($silent = false){
        $serv = Sys::getOneData('Parc','Server/Bdd/'.$this->Id,null,null,null,null,null, null,true);
        $ip = ($serv->usePublicIP)?$serv->IP:$serv->InternalIP;

        if (!is_object($serv)) return false;
        try {
            $dbGuac = new PDO('mysql:host=' . $ip . ';port='.$serv->SqlPort.';dbname=mysql', $serv->SqlUser, $serv->SqlPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $dbGuac->query("SET AUTOCOMMIT=1");
            $dbGuac->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(Exception $e){
            $this->addError(Array('Message'=>'Impossible de contacter le serveur SQL '.$serv->Nom));
            return true;
        }

        //flush privileges
        $query = "DROP DATABASE `" . $this->Nom."`;";
        try {
            $dbGuac->query($query);
        }catch (Exception $e){
            if (!$silent)
                $this->addError(Array('Message'=>'Impossible de supprimer une base de donnée inexistante'));
        }
        //flush privileges
        /*$query = "DROP USER `" . $this->Nom."`@'%';";
        try {
            $dbGuac->query($query);
        }catch (Exception $e){
            if (!$silent)
                $this->addError(Array('Message'=>'Impossible de supprimer un utilisateur inexistante'));
        }*/
        return true;
    }
    /**
     * checkName
     * Vérifie le nom de la base de donnée
     * @param $chaine
     * @return mixed|string
     */
    static function checkName($chaine) {
        $chaine=utf8_decode($chaine);
        $chaine=stripslashes($chaine);
        $chaine = preg_replace('`\s+`', '-', trim($chaine));
        $chaine = str_replace("'", "-", $chaine);
        $chaine = str_replace("&", "et", $chaine);
        $chaine = str_replace('"', "-", $chaine);
        $chaine = str_replace("?", "", $chaine);
        $chaine = str_replace("!", "", $chaine);
        //$chaine = str_replace(".", "", $chaine);
        $chaine = preg_replace('`[\,\ \(\)\+\'\/\:\;]`', '-', trim($chaine));
        $chaine=strtr($chaine,utf8_decode("ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ?"),"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn-");
        $chaine = preg_replace('`[-]+`', '-', trim($chaine));
        $chaine =  utf8_encode($chaine);
        $chaine = preg_replace('`[\/]`', '-', trim($chaine));

        return $chaine;
    }
    /**
     * getSize
     * Définit la taille des bases de données
     */
    public function getSize() {

        $sql = 'SELECT table_schema AS "Database", SUM(data_length + index_length) / 1024 AS "Size" FROM information_schema.TABLES WHERE table_schema="'.$this->Nom.'" GROUP BY table_schema';
        $serv = Sys::getOneData('Parc','Server/Bdd/'.$this->Id,null,null,null,null,null, null,true);
        $ip = ($serv->usePublicIP)?$serv->IP:$serv->InternalIP;

        if (!is_object($serv)) return false;
        try {
            $dbGuac = new PDO('mysql:host=' . $ip . ';port='.$serv->SqlPort.';dbname=information_schema', $serv->SqlUser, $serv->SqlPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $dbGuac->query("SET AUTOCOMMIT=1");
            $dbGuac->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $q = $dbGuac->query($sql);
            $result = $q->fetchALL(PDO::FETCH_ASSOC);
            if (isset($result[0]))
                $this->Size = $result[0]['Size'];
            else $this->Size = 0;
            parent::Save();
            return $this->Size;
        }catch (Exception $e){
            die('ERROR: '.$e->getMessage());
        }
    }

}