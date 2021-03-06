<?php

class Ftpuser extends genericClass {
	var $_KEHost;
	var $_KEServer;
	var $_isVerified = false;

	/**
	 * Force la vérification avant enregistrement
	 * @param	boolean	Enregistrer aussi sur LDAP
	 * @return	void
	 */
	public function Save( $synchro = true ) {
		parent::Save();
		// Forcer la vérification
		$this->Verify( $synchro );
		// Enregistrement si pas d'erreur + Récupération GID & UID HOST
		if($this->_isVerified) {
			parent::Save();
			$this->getUidGidFromHost( $synchro );
			//vérification de l'existence du dossier
            $srvs = $this->getKEServer();
            $host = $this->getKEHost();
            foreach ($srvs as $srv){
                //die('creatio du dossier '.'/home/'.$host->NomLDAP.'/'.$this->DocumentRoot);
                if (!$srv->folderExists('/home/'.$host->NomLDAP.'/'.$this->DocumentRoot)){
                    $srv->createFolder('/home/'.$host->NomLDAP.'/'.$this->DocumentRoot,$host->NomLDAP);
                }
            }
		}
		return true;
	}
	public function getRootPath() {
		if (!$this->Id){
			//recherche de l'hote dans le parent
			foreach ($this->Parents as $p){
				if ($p['Titre']=='Host'){
					$ho = Sys::getOneData('Parc','Host/'.$p['Id']);
					return '/home/'.$ho->Nom.'/';
				}
			}
			return 'Tout neuf';
		}else return $this->DocumentRoot;
	}
    /**
     * getLdapID
     * récupère le ldapId d'une entrée pour un serveur spécifique
     */
    public function getLdapID($KEServer) {
        if (!empty($this->LdapID)) {
            $en = json_decode($this->LdapID, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapID);
        }
        else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapID
     * défniit le ldapId d'une entrée pour un serveur spécifique
     */
    public function setLdapID($KEServer,$ldapId) {
        if (!empty($this->LdapID)) {
            $en = json_decode($this->LdapID, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapID);
        }
        else $en = Array();
        if (!is_array($en))$en = array();
        $en[$KEServer->Id] = $ldapId;
        $this->LdapID = json_encode($en);
    }
    /**
     * getLdapDN
     * récupère le ldapDN d'une entrée pour un serveur spécifique
     */
    public function getLdapDN($KEServer) {
        if (!empty($this->LdapDN)) {
            $en = json_decode($this->LdapDN, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapDN);
        }else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapDN
     * définit le ldapDN d'une entrée pour un serveur spécifique
     */
    public function setLdapDN($KEServer,$ldapDn) {
        if (!empty($this->LdapDN)) {
            $en = json_decode($this->LdapDN, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapDN);
        }else $en = Array();
        if (!is_array($en))$en = array();
        $en[$KEServer->Id] = $ldapDn;
        $this->LdapDN = json_encode($en);
    }
    /**
     * getLdapTms
     * récupère le ldapTms d'une entrée pour un serveur spécifique
     */
    public function getLdapTms($KEServer) {
        if (!empty($this->LdapTms)) {
            $en = json_decode($this->LdapTms, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapTms);
        }else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapTms
     * définit le ldapTms d'une entrée pour un serveur spécifique
     */
    public function setLdapTms($KEServer,$ldapTms) {
        if (!empty($this->LdapTms)) {
            $en = json_decode($this->LdapTms, true);
            if (!is_array($en))
                $en = array($KEServer->Id => $this->LdapTms);
        }else $en = Array();
        if (!is_array($en))$en = array();
        $en[$KEServer->Id] = $ldapTms;
        $this->LdapTms = json_encode($en);
    }
	/**
	 * Verification des erreurs possibles
	 * @param	boolean	Verifie aussi sur LDAP
	 * @return	Verification OK ou NON
	 */
	public function Verify( $synchro = false ) {
		if (substr($this->DocumentRoot,strlen($this->DocumentRoot)-1,1)=='/') $this->DocumentRoot = substr($this->DocumentRoot,0,-1);
        //check host
        if (!$this->getOneParent('Host')) return false;

		if(parent::Verify()) {

			$this->_isVerified = true;

			if($synchro) {

				// Outils
				$KEHost = $this->getKEHost();
				$KEServers = $this->getKEServer();
				foreach ($KEServers as $KEServer) {
                    if (!$KEServer) return false;
                    $dn = 'uid=' . $this->Identifiant . ',ou=users,cn=' . $KEHost->NomLDAP . ',ou=' . $KEServer->LDAPNom . ',ou=servers,' . PARC_LDAP_BASE;
                    // Verification à jour
                    $res = Server::checkTms($this,$KEServer);
                    if ($res['exists']) {
                        if (!$res['OK']) {
                            $this->AddError($res);
                            $this->_isVerified = false;
                        } else {
                            // Déplacement
                            $res = Server::ldapRename($this->getLdapDN($KEServer), 'uid=' . $this->Identifiant, 'ou=users,cn=' . $KEHost->Nom . ',ou=' . $KEServer->LDAPNom . ',ou=servers,' . PARC_LDAP_BASE);
                            if ($res['OK']) {
                                // Modification
                                $entry = $this->buildEntry($KEServer,false);
                                $res = Server::ldapModify($this->getLdapID($KEServer), $entry);
                                if ($res['OK']) {
                                    // Tout s'est passé correctement
                                    $this->setLdapDN($KEServer,$dn);
                                    $this->setLdapTms($KEServer,$res['LdapTms']);
                                } else {
                                    // Erreur
                                    $this->AddError($res);
                                    $this->_isVerified = false;
                                    // Rollback du déplacement
                                    $tab = explode(',', $this->getLdapDN($KEServer));
                                    $leaf = array_shift($tab);
                                    $rest = implode(',', $tab);
                                    Server::ldapRename($dn, $leaf, $rest);
                                }
                            } else {
                                $this->AddError($res);
                                $this->_isVerified = false;
                            }
                        }

                    } else {
                        ////////// Nouvel élément
                        if ($KEHost) {
                            $entry = $this->buildEntry($KEServer);
                            $res = Server::ldapAdd($dn, $entry);
                            if ($res['OK']) {
                                $this->setLdapDN($KEServer,$dn);
                                $this->setLdapID($KEServer,$res['LdapID']);
                                $this->setLdapTms($KEServer,$res['LdapTms']);
                            } else {
                                $this->AddError($res);
                                $this->_isVerified = false;
                            }
                        } else {
                            $this->AddError(array('Message' => "Un utilisateur FTP doit obligatoirement être créé dans un hébergement donné.", 'Prop' => ''));
                            $this->_isVerified = false;
                        }
                    }
                }
			}

		}
		else {

			$this->_isVerified = false;

		}

		return $this->_isVerified;

	}

	/**
	 * Récupère le Gid et le Uid de l'Host Parent s'il existe
	 * @param	boolean		Synchroniser aussi sur LDAP
	 * @return	void
	 */
	private function getUidGidFromHost($synchro) {
		
		$tab = $this->getParents('Host');
		if(!empty($tab)) {
			$this->LdapGid = "100";//$tab[0]->LdapGid;
			$this->LdapUid = $tab[0]->LdapUid;
			if($synchro) {
				$entry = array('ftpgid' => $this->LdapGid, 'ftpuid' => $this->LdapUid);
				$KEServer = $this->getKEServer();
				$res = Server::ldapModify($this->LdapID, $entry);
				$this->LdapTms = $res['LdapTms'];
				parent::Save();
			}
		}
	}

	/**
	 * Configuration d'une nouvelle entrée type
	 * Utilisé lors du test dans Verify
	 * puis lors du vrai ajout dans Save
	 * @param	boolean		Si FALSE c'est simplement une mise à jour
	 * @return	Array
	 */
	private function buildEntry($KEServer, $new = true ) {
		$entry = array();
		$host = $this->getKEHost();
		$entry['cn'] = $KEServer->Nom;
		if($this->DocumentRoot){
		    $entry['homedirectory'] = '/home/'.$host->NomLDAP.'/'.$this->DocumentRoot;
        } else{
            $entry['homedirectory'] = '/home/'.$host->NomLDAP;
        }
		$entry['uid'] = $this->Identifiant;
		if($this->Password != '*******') $entry['userpassword'] = "{MD5}".base64_encode(pack("H*",md5($this->Password)));
		$entry['ftpquotambytes'] = $this->QuotaMb;
		if($new) {
			$KEHost = $this->getKEHost();
			$entry['ftpuid'] = $KEHost->getLdapUid($KEServer);
			$entry['ftpgid'] = $KEHost->LdapGid;
			$entry['uidnumber'] = $KEServer->getNextUid();
			$entry['gidnumber'] = $KEHost->LdapGid;
			$entry['objectclass'][0] = 'posixAccount';
			$entry['objectclass'][1] = 'PureFTPdUser';
			$entry['objectclass'][2] = 'top';
			$entry['ftpstatus'] = 'enabled';
		}
		return $entry;
	}


	/**
	 * Suppression de la BDD
	 * Relai de cette suppression à LDAP
	 * On utilise aussi la fonction de la superclasse
	 * @return	void
	 */
	public function Delete() {
	    $KEServers = $this->getKEServer();
	    foreach ($KEServers as $KEServer) {
            Server::ldapDelete($this->LdapID);
        }
        parent::Delete();
	}


	/**
	 * Récupère une référence vers l'objet KE "Server"
	 * pour effectuer des requetes LDAP
	 * On conserve une référence vers le serveur
	 * pour le cas d'une utilisation ultérieure
	 * @return	L'objet Kob-Eye
	 */
	private function getKEServer() {
		if(!is_object($this->_KEServer)) {
			$KEHost = $this->getKEHost();
			if($KEHost)	$this->_KEServer = $KEHost->getKEServer();
			else return false;
		}
		return $this->_KEServer;
	}

	/**
	 * Récupère une référence vers l'objet KE "Host" parent
	 * On conserve une référence vers le host
	 * pour le cas d'une utilisation ultérieure
	 * @return	L'objet Kob-Eye
	 */
	private function getKEHost() {
		if(!is_object($this->_KEHost)) {
			$tab = $this->getParents('Host');
			if(empty($tab)) return false;
			else $this->_KEHost = $tab[0];
		}
		return $this->_KEHost;
	}

	/**
	 * Retrouve les parents lors d'une synchronisation
	 * @return	void
	 */
	public function findParents() {
		$Parts = explode(',', $this->LdapDN);
		foreach($Parts as $i => $P) $Parts[$i] = explode('=', $P);
		// Parent Host
		$Tab = Sys::$Modules["Parc"]->callData("Parc/Host/Nom=".$Parts[2][1], "", 0, 1);
		if(!empty($Tab)) {
			$obj = genericClass::createInstance('Parc', $Tab[0]);
			$this->AddParent($obj);
		}
	}
}
