<?php

class Apache extends genericClass {
	var $_KEHost;
	var $_KEServer;
	var $_KEInfra;
	var $_isVerified = false;
	/**
	 * Force la vérification avant enregistrement
	 * @param	boolean	Enregistrer aussi sur LDAP
	 * @return	void
	 */
	public function Save( $synchro = true , $force = false) {
        $old = Sys::getOneData('Parc','Apache/'.$this->Id);
	    //test de modification du ApacheServerName
        if ($this->Id &&$old->ApacheServerName!=$this->ApacheServerName){
            $this->addError(array("Message"=>"Impossible de modifier le ApacheServerName. Si c'est nécessaire, veuillez supprimer et recréer cet Hôte virtuel."));
            return false;
        }
		if ($this->Ssl&$this->Id){
			//test de l'activation ssl
			if (!$old->Ssl&&!$force) {
			    if (!$this->enableSsl()) return false;
            }
		}elseif($this->Ssl){
		    parent::Save();
            $this->enableSsl();
        }
        //config proxy
        if (empty($this->ProxyConfig)){
		    $this->ProxyConfig = "
proxy_cache            ".$this->ApacheServerName.";
proxy_cache_valid      200  1h;
proxy_cache_use_stale  error timeout updating http_500 http_502 http_503 http_504;
proxy_cache_key    \$uri\$is_args\$args;
proxy_cache_background_update on;
proxy_cache_revalidate on;
proxy_cache_min_uses 3;
proxy_cache_lock on;
if (\$http_cookie ~* \"comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|no_cache|wordpress_logged_in|PHPSESSID\") { set \$arg_nocache 1; }
";
        }
        if (empty($this->ProxyConfigSsl)){
            $this->ProxyConfigSsl = "
proxy_cache            ".$this->ApacheServerName.".ssl;
proxy_cache_valid      200  1h;
proxy_cache_use_stale  error timeout updating http_500 http_502 http_503 http_504;
proxy_cache_key    \$uri\$is_args\$args;
proxy_cache_background_update on;
proxy_cache_revalidate on;
proxy_cache_min_uses 3;
proxy_cache_lock on;
if (\$http_cookie ~* \"comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|no_cache|wordpress_logged_in|PHPSESSID\") { set \$arg_nocache 1; }
";
        }

		// Forcer la vérification
		$this->Verify( $synchro );
		// Enregistrement si pas d'erreur
		if($this->_isVerified) parent::Save();

		//mise à jour des serveurs
        try {
            $srvs = $this->getKEServer();
            $infra = $this->getInfra();

            foreach ($srvs as $srv) {
                $srv->callLdap2Service();
            }

            $pxs = $this->getProxy();
            foreach ($pxs as $px) {
                $px->callLdap2Service();
            }
            Server::createRestartProxyTask($infra);

        }catch (Exception $e){
            $this->addError(array("Message"=>"Impossible de mettre le serveur à jour. Serveur injoignable.".$e->getMessage()));
            return false;
        }
		return true;
	}

    /**
     * softSave
     * @return bool
     */
    public function softSave() {
        return parent::Save();
    }

    /**
     * enableSsl
     * @param bool $force
     * @param null $instance
     * @return bool
     */
	public function enableSsl($force = false) {
		if (empty($this->SslMethod))$this->SslMethod = "Letsencrypt";
		//check already exists
		if (!$force&&$this->Ssl&&!empty($this->SslCertificate)&&!empty($this->SslCertificateKey)&&$this->SslExpiration>time()+(7*86400)){
			$this->addError(array("Message"=>"Le certificat est déjà généré et valide. Son expiration n'interviendra pas dans la prochaine semaine."));
			return false;
		}
		//on vérifie qu'il n'y ait pas déjà une tache
        if (Sys::getCount('Parc','Apache/'.$this->Id.'/Tache/TaskCode=SSL_RENEW&Termine=0&Erreur=0&tmsEdit>'.(time()-86400))){
            $this->addError(array("Message"=>"Il y a déjà une tache à venir pour l'activation SSL."));
            return false;
        }
        $this->Ssl = false;
		switch($this->SslMethod){
            case "Manuel":
                if (!$force&&$this->Ssl&&(empty($this->SslCertificate)||empty($this->SslCertificateKey))){
                    $this->addError(array("Message"=>"Pour activer SSL il faut que la clef et le certificat soient renseignés"));
                    return false;
                }
                //check validity before enabling
                $key  = openssl_x509_check_private_key($this->SslCertificate,$this->SslCertificateKey);
                if (!$key){
                    $this->addError(array("Message"=>"Le certificat et la clef ne correspondent pas."));
                    return false;
                }
                $this->Ssl = true;
                break;

			case "Letsencrypt":
                require_once 'Net/DNS2.php';
                if (!class_exists('Net_DNS2_Resolver')){
                    $this->addError(array("Message"=>"La librairie Net_DNS2 n'est pas disponible. Veuillez l'installer avec la commande suivante: 'pear install NET/DNS2'"));
                    return false;
                }
                //définition de la date d'expiration
                $this->Ssl = true;
                //recherche du serveur proxy
                //$serv = Sys::getOneData('Parc','Server/Proxy=1',0,1,'ASC','Id',null,null,true);
                $serv = $this->getProxy();
                if(sizeof($serv)){
                    $servP = $serv[0];
                    $infra = $servP->getOneParent('Infra');
                    /*if( ($infra && !$this->_KEInfra) || ($infra && ($infra->Id != $this->_KEInfra->Id)) ){
                        $serv=array();
                    }*/
                }
                if (!sizeof($serv)) {
                    $serv = $this->getKEServer();
                }
                $serv = $serv[0];


                $sa = explode(" ",$this->getDomains());

                //test des entrées dns
                $resolver = new Net_DNS2_Resolver( array('nameservers' => array('8.8.8.8')) );
                try {
                    $t = array($resolver->query($this->ApacheServerName, 'A'));
                }catch (Exception $e){
                    $this->addWarning(array("Message"=>"Timeout DNS : ".$e->getMessage()));
                }
                foreach ($sa as $a){
                    $a = trim($a);
                    if (!empty($a)) {
                        try {
                            $t = array_merge($t, array($resolver->query($a, 'A')));
                        }catch (Exception $e){
                            $this->addWarning(array("Message"=>"Erreur DNS : ".$e->getMessage()));
                        }
                    }
                }
                //test des erreurs
                $err = false;
                foreach ($t as $dns){
                    if (!sizeof($dns->answer[sizeof($dns->answer)-1])){
                        $err = true;
                        $this->addError(array("Message"=>"Le domaine : '".$dns->question[sizeof($dns->question)-1]->qname."' ne pointe pas sur l'adresse ip ".$serv->IP." (actuellement il n'est pas configuré)"));
                    }/*elseif (trim($dns->answer[sizeof($dns->answer)-1]->address)!=trim($serv->IP)){
                        $err = true;
                        $this->addError(array("Message"=>"Le domaine : '".$dns->question[sizeof($dns->question)-1]->qname."' ne pointe pas sur l'adresse ip ".$serv->IP." (actuellement il pointe vers ".$dns->answer[sizeof($dns->answer)-1]->address."), ou sa propagation se terminera dans ".$dns->answer[sizeof($dns->answer)-1]->ttl." secondes"));
                    }*/
                }
                if ($err)return false;

                //pour activer ssl il faut déclencher une tache
                $task  = genericClass::createInstance('Systeme','Tache');
                $task->Nom = "Activation SSL pour la configuration Apache ".$this->getDomains()." ( ".$this->Id." )";
                $task->Type = "Fonction";
                $task->TaskType = "install";
                $task->TaskModule = "Parc";
                $task->TaskObject = "Apache";
                $task->TaskFunction = "executeLetsencrypt";
                $task->TaskCode = 'SSL_RENEW';
                $task->TaskId = $this->Id;
                $task->addParent($this);
                $task->addParent($serv);
                //on va charcher l'hébergement
                $host = $this->getOneParent('Host');
                $task->addParent($host);
                //on va chercher l'instance
                if (!$host) return false;
                $instance = $host->getOneChild('Instance');
                $task->addParent($instance);
                //recherch de la prochaine d'execution pour eviter les collision de letsencrypt
                Sys::$Modules['Systeme']->Db->clearLiteCache();
                $nb = Sys::getCount('Systeme','Tache/TaskCode=SSL_RENEW&DateDebut>'.time());
                $task->DateDebut = time()+(300*($nb+1));
                $task->Save();
                parent::Save();
			break;
			default:
			break;
		}
		return true;
	}

	public function getRootPath() {
		if (!$this->Id){
			//recherche de l'hote dans le parent
			foreach ($this->Parents as $p){
				if ($p['Titre']=='Host'){
					$ho = Sys::getOneData('Parc','Host/'.$p['Id']);
					return '/home/'.$ho->Nom.'/www';
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
            if (!$en = json_decode($this->LdapID, true))
                $en = array($KEServer->Id => $this->LdapID);
        }else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapID
     * défniit le ldapId d'une entrée pour un serveur spécifique
     */
    public function setLdapID($KEServer,$ldapId) {
        if (!empty($this->LdapDN))
            $en = json_decode($this->LdapID,true);
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
            if (!$en = json_decode($this->LdapDN, true))
                $en = array($KEServer->Id => $this->LdapDN);
        }else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapDN
     * définit le ldapDN d'une entrée pour un serveur spécifique
     */
    public function setLdapDN($KEServer,$ldapDn) {
        if (!empty($this->LdapDN))
            $en = json_decode($this->LdapDN,true);
        else $en = Array();
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
            if (!$en = json_decode($this->LdapTms, true))
                $en = array($KEServer->Id => $this->LdapTms);
        }else $en=array();
        return $en[$KEServer->Id];
    }
    /**
     * setLdapTms
     * définit le ldapTms d'une entrée pour un serveur spécifique
     */
    public function setLdapTms($KEServer,$ldapTms) {
        if (!empty($this->LdapTms))
            $en = json_decode($this->LdapTms,true);
        else $en = Array();
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
	    $this->ApacheServerName = SubDomain::checkName($this->ApacheServerName);
        if ($this->Ssl&&$this->SslMethod=='Letsencrypt') {
            $this->addWarning(array("Message" => "Veuillez bien vérifier que les ServerName soit bien configuré et pointe bien vers le serveur. Les ServerAlias doivent également bien pointer sur le serveur, sinon l'activation SSL échouera."));
        }
        $old = Sys::getOneData('Parc','Apache/'.$this->Id);
        //test de modification du ApacheServerName
        if ($this->Id &&$old->ApacheServerName!=$this->ApacheServerName){
            $this->addError(array("Message"=>"Impossible de modifier le ApacheServerName. Si c'est nécessaire, veuillez supprimer et recréer cet Hôte virtuel."));
            return false;
        }
		//check documentRoot
		if (substr($this->DocumentRoot,strlen($this->DocumentRoot)-1,1)=='/') $this->DocumentRoot = substr($this->DocumentRoot,0,-1);
        //test du documentroot
        $host = $this->getKEHost();
        $this->DocumentRoot = str_replace('/home/'.$host->NomLDAP.'/','',$this->DocumentRoot);
		if(parent::Verify()) {
            //check ssl
            if ($this->Ssl&&$this->SslMethod=='Manuel'&&(empty($this->SslCertificate)||empty($this->SslCertificateKey))){
                $this->addError(array("Message"=>"Pour activer SSL il faut que la clef et le certificat soient renseignés"));
                $this->_isVerified = false;
                return false;
            }
            if ($this->Ssl&&$this->SslMethod=='Manuel') {
                //check validity before enabling
                $key = openssl_x509_check_private_key($this->SslCertificate, $this->SslCertificateKey);
                if (!$key) {
                    $this->addError(array("Message" => "Le certificat et la clef ne correspondent pas."));
                    $this->_isVerified = false;
                    return false;
                }
            }

            $this->_isVerified = true;

			if($synchro) {
				// Outils
				$KEHost = $this->getKEHost();
				$KEServers = $this->getKEServer();
                if (empty($KEHost->NomLDAP)) {
                    print_r($KEHost);
                    echo  "L'hébergement n'est pas à jour... Enregistrement forcé... \n";
                    $this->addWarning(array("Message" => "L'hébergement n'est pas à jour... Enregistrement forcé..."));
                    $KEHost->Save();
                }
                foreach ($KEServers as $KEServer) {
                    $dn = 'apacheServerName=' . $this->ApacheServerName.',cn=' . $KEHost->NomLDAP . ',ou=' . $KEServer->LDAPNom . ',ou=servers,' . PARC_LDAP_BASE;
                    $base = 'cn=' . $KEHost->NomLDAP . ',ou=' . $KEServer->LDAPNom . ',ou=servers,' . PARC_LDAP_BASE;
                    // Verification à jour
                    $res = Server::checkTms($this,$KEServer,$base,'apacheServerName=' . $this->ApacheServerName);
                    if ($res['exists']) {
                        if (!$res['OK']) {
                            $this->AddError($res);
                            $this->_isVerified = false;
                        } else {
                            // Déplacement
                            $res = Server::ldapRename($this->getLdapDN($KEServer), 'apacheServerName=' . $this->ApacheServerName, 'cn=' . $KEHost->NomLDAP . ',ou=' . $KEServer->LDAPNom . ',ou=servers,' . PARC_LDAP_BASE);
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
                            $this->AddError(array('Message' => "Une configuration Apache doit obligatoirement être créé dans un hébergement donné.", 'Prop' => ''));
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
	 * Configuration d'une nouvelle entrée type
	 * Utilisé lors du test dans Verify
	 * puis lors du vrai ajout dans Save
	 * @param	boolean		Si FALSE c'est simplement une mise à jour
	 * @return	Array
	 */
	private function buildEntry($KEServer, $new = true ) {
	    //recherche multiple web servers
        $host= $this->getOneParent('Host');
        $webs= $this->getKEServer();
        $infra= $this->getInfra();
        //certificat par défaut si incomplet
        $defaultCert = Sys::getOneData('Parc','Certificate/Default=1');

		$entry = array();
		if(!empty($this->ApacheServerAlias)) {
			$alias = explode("\n", $this->ApacheServerAlias);
			$entry['apacheserveralias'] = array();
			foreach($alias as $k => $a)	$entry['apacheserveralias'][$k] = $a;
		}elseif (!$new) $entry['apacheserveralias'] = array();

		$entry['apachesuexecuid'] = $this->_KEHost->NomLDAP;
		$Client = $this->_KEHost->getKEClient();
		$entry['apachesuexecgid'] = $Client->NomLDAP;
		$entry['apacheservername'] = $this->ApacheServerName;
		$entry['apachescriptalias'] = '/cgi-bin/ /home/'.$this->_KEHost->NomLDAP.'/cgi-bin/';
		$entry['apachedocumentroot'] = '/home/'.$this->_KEHost->NomLDAP.'/'.$this->DocumentRoot;
		if($new) {
			$entry['objectclass'][0] = 'apacheConfig';
			$entry['objectclass'][1] = 'top';
		}
		$entry['apachevhostenabled'] = $this->Actif?'yes':'no';
		$entry['apacheHtPasswordEnabled'] = $this->PasswordProtected?'yes':'no';
		if ($this->PasswordProtected) {
			$entry['apacheOptions'][] = 'AuthType Basic';
			$entry['apacheOptions'][] = 'AuthName "Authentication Required"';
			$entry['apacheOptions'][] = 'AuthUserFile "'.'/home/'.$this->_KEHost->NomLDAP.'/'.$this->DocumentRoot.'/.htpasswd"';
			$entry['apacheOptions'][] = 'Require valid-user';
			$entry['apacheHtPasswordUser'] = $this->HtaccessUser;
			$entry['apacheHtPasswordPassword'] = $this->HtaccessPassword;
		}else{
			$entry['apacheOptions'] = Array('Require all granted');
		}
		if ($this->Ssl&&!empty($this->SslCertificate)&&!empty($this->SslCertificateKey)){
			$entry['apacheSslEnabled'] = 'yes';
			$entry['apacheCertificate'] = base64_encode($this->SslCertificate);
			$entry['apacheCertificateKey'] = base64_encode($this->SslCertificateKey);
			$entry['apacheCertificateExpiration'] = $this->SslExpiration;
		}else{
            if($defaultCert){
                $entry['apacheSslEnabled'] = 'yes';
                $entry['apacheCertificate'] = base64_encode($defaultCert->SslCertificate);
                $entry['apacheCertificateKey'] = base64_encode($defaultCert->SslCertificateKey);
                $entry['apacheCertificateExpiration'] = $defaultCert->SslExpiration;
            } else {
                $entry['apacheSslEnabled'] = 'no';
            }

		}

		//ALias Config
        if (!$new)
            $entry['apacheconfigalias'] = array();
        if (!empty($this->ApacheConfig))
            $entry['apacheconfigalias'] = $this->ApacheConfig;
        if ($this->RedirectSsl)
            $entry['apacheconfigalias'] = $this->ApacheConfig."
RewriteEngine On
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{SERVER_NAME}$1 [R,L]";


        //Proxy config
		if ($this->ProxyCache){
            //$entry['apacheProxyCacheConfig'] = "proxy_cache            ".$this->ApacheServerName.";\n  proxy_cache_valid      200  1h;\n  proxy_cache_use_stale  error timeout invalid_header updating http_500 http_502 http_503 http_504;\n  proxy_cache_key    \$uri\$is_args\$args;\n  proxy_cache_valid 200 10m;\n  proxy_cache_background_update on;\n  if (\$http_cookie ~* \"comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|no_cache|wordpress_logged_in\") { set \$arg_nocache 1; }\n";
            //$entry['apacheProxyCacheConfigSsl'] = "proxy_cache ".$this->ApacheServerName.".ssl;\n  proxy_cache_valid      200  1h;\n  proxy_cache_use_stale  error timeout invalid_header updating http_500 http_502 http_503 http_504;\n  proxy_cache_key    \$uri\$is_args\$args;\n  proxy_cache_valid 200 10m;\n  proxy_cache_background_update on;\n  if (\$http_cookie ~* \"comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|no_cache|wordpress_logged_in\") { set \$arg_nocache 1; }\n";
            $entry['apacheProxyCacheConfig'] = $this->ProxyConfig;
            $entry['apacheProxyCacheConfigSsl'] = $this->ProxyConfigSsl;
        }else if (!$new) {
		    $entry['apacheProxyCacheConfig'] = Array();
            $entry['apacheProxyCacheConfigSsl'] = Array();
        }

        //ROUTAGE PROXY
        $entry['apacheProxy'] = '';
        $entry['apacheProxyPagespeed'] = '';
        if (!$this->ProxyPageSpeed) {
            foreach ($webs as $web) {
                if (!empty($web->InternalIP)) {
                    $entry['apacheProxy'] .= 'server ' . $web->InternalIP . " fail_timeout=600s;\n";
                    $entry['apacheProxyPagespeed'] .= 'server ' . $web->InternalIP . " fail_timeout=600s;\n";
                }
            }
        }else{
            foreach ($webs as $web) {
                if (!empty($web->InternalIP))
                    $entry['apacheProxyPagespeed'] .= 'server ' . $web->InternalIP . " fail_timeout=600s;\n";
            }

            $pref = '';
            if($infra)
                $pref = 'Infra/'.$infra->Id.'/';

            $pxs = Sys::getData('Parc',$pref.'Server/PageSpeed=1',0,100,'','','','',true);
            foreach ($pxs as $px) {
                if (!empty($px->InternalIP))
                    $entry['apacheProxy'] .= 'server ' . $px->InternalIP . " fail_timeout=600s;\n";
            }

        }
        $entry['apacheProxyPagespeedConfig'] = trim($this->ProxyPageSpeedConfig);
        if($entry['apacheProxyPagespeedConfig'] == '') unset($entry['apacheProxyPagespeedConfig']);
        if($entry['apacheProxy'] == '') unset($entry['apacheProxy']);
        if($entry['apacheProxyPagespeed'] == '') unset($entry['apacheProxyPagespeed']);
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
		    $this->deleteFromServer($KEServer);
            Server::ldapDelete($this->LdapID);
        }
        //suppresion de la config sur les serveurs proxy
        $pxs = $this->getProxy();
        foreach ($pxs as $px){
            try {
                $px->remoteExec('rm /etc/nginx/conf.d/' . $this->ApacheServerName . '* -f && systemctl reload nginx');
            } catch (Exception $e) {
                $this->addError(Array("Message" => "Impossible d'effectuer la commande de suppression sur le serveur ".$px->Nom));
            }
        }

        parent::Delete();
        return true;
	}

    /**
     * deleteFromServer
     * Supprime les configuratio uniquement depuis un seul server
     */
    public function deleteFromServer($KEServer){
        try {
            $KEServer->remoteExec('rm /etc/httpd/sites-enabled/' . $this->ApacheServerName . '* -f && systemctl reload httpd');
        } catch (Exception $e) {
            $this->addError(Array("Message" => "Impossible d'effectuer la commande de suppression sur le serveur ".$KEServer->Nom));
        }
        return true;
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
     * Récupère les proxy qui ont un impact sur cet apache
     * @return	Array d'objet Kob-Eye
     */
    private function getProxy() {
        $pref = '';
        if($this->getInfra())
            $pref='Infra/'.$this->_KEInfra->Id.'/';

        $pxs = Sys::getData('Parc',$pref.'Server/Proxy=1',null,null,null,null,null,null,true);
        //if ($this->ProxyPageSpeed){
            $pxs = array_merge($pxs,Sys::getData('Parc',$pref.'Server/PageSpeed=1',null,null,null,null,null,null,true));
        //}
        return $pxs;
    }

    /**
     * Récupère une référence vers l'objet KE "Infra"
     * pour effectuer des requetes LDAP
     * On conserve une référence vers le serveur
     * pour le cas d'une utilisation ultérieure
     * @return	L'objet Kob-Eye
     */
    private function getInfra() {
        if(!is_object($this->_KEInfra)) {
            $srv = $this->getKEServer();
            if(is_array($srv)&&sizeof($srv)) {
                $srv = $srv[0];
                $this->_KEInfra = $srv->getOneParent('Infra');
            }
        }
        return $this->_KEInfra;
    }


	/**
	 * Retrouve les parents lors d'une synchronisation
	 * @return	void
	 */
	public function findParents() {
		$Parts = explode(',', $this->LdapDN);
		foreach($Parts as $i => $P) $Parts[$i] = explode('=', $P);
		// Parent Host
		$Tab = Sys::$Modules["Parc"]->callData("Parc/Host/Nom=".$Parts[1][1], "", 0, 1);
		if(!empty($Tab)) {
			$obj = genericClass::createInstance('Parc', $Tab[0]);
			$this->AddParent($obj);
		}
	}

	/**
	 * Retrouve les sous domaines qui correspondent
	 * ( Nécessité d'avoir ça dans les 2 sens )
	 * @return	void
	 */
	public function findSubDomains() {
		// Liste des domaines
		$Domains = array();
		if(!empty($this->ApacheServerName)) $Domains[] = $this->ApacheServerName;
		if(!empty($this->ApacheServerAlias)) {
			$Tab = explode("\r", $this->ApacheServerAlias);
			foreach($Tab as $url) if(!empty($url)) $Domains[] = $url;
		}
		$result = 0;
		// Recherche
		foreach($Domains as $url) {
			$Parts = explode('.', $url);
			$domain = "";
			$domain = array_pop($Parts);
			$domain = array_pop($Parts).'.'.$domain;
			$subdomain = implode('.', $Parts);
			$Tab = Sys::$Modules["Parc"]->callData("Parc/Domain/Url=$domain/Subdomain/Url=$subdomain", "", 0, 100);
			if(!empty($Tab)) {
				foreach($Tab as $o) {
					$result++;
					$obj = genericClass::createInstance('Parc', $Tab[0]);
					$obj->AddParent($this);
					$obj->Save(false);
				}
			}
		}
		return $result;
	}

	/**
	 * callBackTask
	 * function callback pour le retour de la tache
	 */
	public function callBackTask($msg){
		if (preg_match("#-----BEGIN CERTIFICATE-----#", $msg,$out)){
            $this->SslExpiration=time()+(86400*90);
			//enregistrement du fullchain
			$this->SslCertificate = $msg;
			parent::Save();
		}
		if (preg_match("#-----BEGIN PRIVATE KEY-----#", $msg,$out)){
			$this->SslCertificateKey = $msg;
			parent::Save();
		}
	}

	/**
     * getDomains
     * renvoie la slite séparée pâr des esapces de tous les domaines
     *
     */
	public function getDomains() {
	    return $this->ApacheServerName.' '.(implode(" ",explode("\n",str_replace("\r","",$this->ApacheServerAlias))));
    }
    /**
     * getDomainsToCheck
     * renvoie la liste séparée pâr des esapces de tous les domaines à vérifier
     *
     */
    public function getDomainsToCheck() {
        $exceptionDomains = Sys::getData('Parc','Domain/NoSSL=1',0,10,'','','','',true);
        $domains = explode("\n",str_replace("\r","",$this->ApacheServerAlias));
        $domains[] = $this->ApacheServerName;
        $out = array();
        foreach ($domains as $domain) {
            if (empty(trim($domain))) continue;
            $except = false;
            foreach ($exceptionDomains as $ed){
                //test des exceptions
                if (strpos($domain,$ed->Url) !== false){
                    $except = true;
                }
            }
            if (!$except)array_push($out,$domain);
        }
        return implode(' ',$out);
    }
    /**
     * getDomains
     * renvoie la slite séparée pâr des esapces de tous les domaines
     *
     */
    public function getDomainsLink() {{}
        $out = '<a href="http://'.$this->ApacheServerName.'">'.$this->ApacheServerName.'</a><br />';
        $oth = explode("\n",$this->ApacheServerAlias);
        foreach ($oth as $o){
            $out.='<a href="http://'.$o.'">'.$o.'</a><br />';
        }
        return $out;
    }
    public static function getDomainIp($domain){
        require_once 'Net/DNS2.php';
        if (!class_exists('Net_DNS2_Resolver')){
            die("La librairie Net_DNS2 n'est pas disponible. Veuillez l'installer avec la commande suivante: 'pear install NET/DNS2'");
            return false;
        }
        $resolver = new Net_DNS2_Resolver( array('nameservers' => array('8.8.8.8','8.8.4.4'),
                'use_tcp' => false,
                'timeout' => 20 ));
        try {
            $t = $resolver->query($domain, 'A');
        }catch (Exception $e){
            throw new Exception("Timeout DNS : ".$e->getMessage());
        }
        if (sizeof($t->answer))
            return $t->answer[sizeof($t->answer)-1]->address;
        else return false;
    }
    /**
     * getFirstDomain
     * Retrouve le domaine par défaut
     *
     */
    public function getFirstDomain() {
        $doms = explode(' ',$this->getDomains());
        $exceptionDomains = Sys::getData('Parc','Domain/NoSSL=1');

        foreach ($doms as $dom) {
            if (empty($dom)) continue;
            $exception = false;
            foreach ($exceptionDomains as $ed){
                //test des exceptions
                if (strpos($dom,$ed->Url) !== false){
                    $exception = true;
                }

            }
            if (!$exception) {
                return $dom;
            }
        }
    }
    /**
     * executeLetsencrypt
     * Execution de letsencrypt sur le serveur
     */
    public function executeLetsencrypt($task) {
        $first=null;//$this->SslMainDomain;
        if (empty($first)){
            $first = $this->getFirstDomain();
        }
        //récupération du serveur apache
        $host = $this->getOneParent('Host');
        $srv = $host->getOneParent('Server');
        //récupération de l'infrastructure
        $infra = $srv->getOneParent('Infra');

        //sélection du proxy par défaut
        if ($infra) {
            $infraPrefixe = 'Infra/'.$infra->Id.'/';
        }else {
            $infraPrefixe = '';
            $act = $task->createActivity('Pas d\'infrastructure détectée');
            $act->Terminate(false);
        }
        //on vérifie l'ip du domaine principal
        $act = $task->createActivity('Recherche de l\'ip pour le domaine '.$this->getFirstDomain());
        try {
            $ip = Apache::getDomainIp($this->getFirstDomain());
            $act->addDetails(print_r($ip, true));
        }catch (Exception $e) {
            $act->addDetails(print_r($ip?$ip:'no ip', true));
            Incident::createIncident('Le domaine ' . $this->getFirstDomain() . ' n\'existe pas .', 'Le code de retour est ' . print_r($e->getCode(), true), $this, 'CERTIFICATE_DOMAIN', $this->getFirstDomain(), 4, false);
            return false;
        }
//        Incident::createIncident('Le domaine ' . $this->getFirstDomain() . ' n\'existe pas .', 'Le code de retour est ' . print_r($code, true), $this, 'CERTIFICATE_DOMAIN', $this->getFirstDomain(), 4, true);

        if ($ip){
            $act = $task->createActivity('Recherche du proxy pour l\'ip '.$ip);

            //on prends celui qui correspond à l'ip
            $pxsrv = Sys::getOneData('Parc',$infraPrefixe.'Server/Proxy=1&IP='.$ip,0,1,'ASC','Id');
            if (!$pxsrv){
                //on prends le premier par défaut su rl'infra
                $pxsrv = Sys::getOneData('Parc',$infraPrefixe.'Server/Proxy=1',0,1,'ASC','Id');
                if (!$pxsrv) {
                    $act->Terminate(false);
                }else{
                    $srv = $pxsrv;
                    $act->Terminate(true);
                }
            }else{
                $srv = $pxsrv;
                $act->Terminate(true);
            }
        }else{
            //on prends le premier par défaut su rl'infra
            $pxsrv = Sys::getOneData('Parc',$infraPrefixe.'Server/Proxy=1',0,1,'ASC','Id');
            if (!$pxsrv) {
                $act->Terminate(false);
            }else{
                $srv = $pxsrv;
                $act->Terminate(true);
            }
        }
        $act = $task->createActivity('Execution Letsencrypt de l\'hote virtuel '.$this->ApacheServerName.' de l\'hébergement '.$host->getFirstSearchOrder().' du serveur '.$srv->getFirstSearchOrder() );


        //Vérification du dépot letsencrypt
        $act = $task->createActivity('Vérification du dépot letsencrypt ');
        $err = false;
        $valid = false;
        $cert = $srv->getFileContent("/etc/letsencrypt/live/".$first."/fullchain.pem");
        $exceptionDomains = Sys::getData('Parc','Domain/NoSSL=1');
        $incompleteDomain = false;
        $act->Terminate(true);
        if (!empty($cert)) {
            $act = $task->createActivity('Vérification du certificat actuel');
            $certinfo = openssl_x509_parse($cert);
            //on compare la liste des domaines à certifier et les domaines dans le certificat
            $domains=explode(' ',$this->getDomains());
            $certdomains = array();
            preg_match_all('#DNS:([^\ ,]*)#',$certinfo['extensions']['subjectAltName'],$othersdomains);
            $certdomains=array_merge($certdomains,$othersdomains[1]);
            //récupérations des exception de domaine
            foreach ($domains as $d){
                $exception = false;
                if (empty(trim($d))) continue;
                foreach ($exceptionDomains as $ed){
                    //test des exceptions
                    if (strpos($d,$ed->Url) !== false){
                        $exception = true;
                    }

                }
                if (!in_array($d,$certdomains)&&!$exception){
                    $this->addError(array('Message'=>'Le domaine '.$d.' n\' est pas compris dans le certificat en production. Il serait nécessaire de le regénérer.'));
                    $incompleteDomain = true;
                    $act = $task->createActivity('Domaines incomplets - extension du domaine ('.$d.')... sauf si le certif est expiré');
                }
            }

            //on vérifie qu'on a la bonne date d'expiration et qu'il est différent de celui actif
            if ($certinfo['validTo_time_t'] > time() + (86400 * 30) && $this->SslCertificate != $cert) {
                $valid = true;
                if (!$incompleteDomain) {
                    //alors on utilise ce certificat
                    $act = $task->createActivity('Récupération des certificats');
                    //récupération des certificats
                    $this->SslCertificate = $srv->getFileContent("/etc/letsencrypt/live/" . $first . "/fullchain.pem");
                    $act->addDetails($this->SslCertificate);
                    $this->SslCertificateKey = $srv->getFileContent("/etc/letsencrypt/live/" . $first . "/privkey.pem");
                    $act->addDetails($this->SslCertificateKey);
                    $this->SslExpiration = $certinfo['validTo_time_t'];
                    $act->addDetails('Date d\'Expiration: ' . $this->SslExpiration);
                    $act->Terminate(true);
                    $this->Save();
                    return true;
                }else {
                    //certificat non valide donc on le regénère peu importe qi les domaines sont incomplets
                    $act = $task->createActivity('Date du certificat valide. Mais des domaines sont manquants. Donc extension du certificat');
                    $act->Terminate(true);
                }
            }else {
                //certificat non valide donc on le regénère peu importe qi les domaines sont incomplets
                $valid=$incompleteDomain=false;
                $act = $task->createActivity('Certificat expiré donc regénération totale du certificat.');
            }
        }
        $act = $task->createActivity('Préparation de la commande certbot');
        //execution de la commande
        if ($valid && $incompleteDomain)
            $prefixe = "/usr/src/certbot/certbot-auto --expand -n --webroot certonly --webroot-path /var/www/letsencrypt ";
        else
            $prefixe = "/usr/src/certbot/certbot-auto --renew-by-default --webroot certonly --webroot-path /var/www/letsencrypt ";
        $cmd = '';
        // ajout des server alias
        $sa = explode(' ',$this->getDomains());
        foreach ($sa as $s ){
            $s = trim($s);
            $exception = false;
            foreach ($exceptionDomains as $ed){
                //test des exceptions
                if (strpos($s,$ed->Url) !== false){
                    $exception = true;
                }

            }
            if (!$exception&&!empty($s)) {
                if (empty($first)) $first=$s;
                $cmd .= " -d " . $s;
            }
        }
        if (empty($cmd)){
            $act = $task->createActivity('Aucun domaine à certifier.');
            $act->Terminate(true);
            return true;
        }
        $cmd = $prefixe.$cmd;
        $act = $task->createActivity('Execution de la commande certbot sur le serveur '.$srv->Nom);
        $act->addDetails($cmd);
        try {
            $out = $srv->remoteExec($cmd);
        }catch (Exception $e) {
            $act->addDetails($e->getMessage());
            $act->Terminate(false);
            $task->Erreur = 1;
            Incident::createIncident('Le domaine ' . $this->getFirstDomain() . ' n\'existe pas .', 'Le code de retour est ' . print_r($code, true), $this, 'CERTIFICATE_DOMAIN', $this->getFirstDomain(), 4, false);
            $task->Save();
            return false;
        }
        Incident::createIncident('Le domaine ' . $this->getFirstDomain() . ' n\'existe pas .', 'Le code de retour est ' . print_r($code, true), $this, 'CERTIFICATE_DOMAIN', $this->getFirstDomain(), 4, true);
        $act->addDetails($out);
        $act->Terminate(true);
        if (preg_match('#/etc/letsencrypt/live/(.*?)/fullchain.pem#',$out,$path)) {
            //analyse du retour et récupération du path
            $first = $this->SslMainDomain = $path[1];
            parent::Save();
            $act->addDetails('Définition du domaine par défaut du certificat: '.$this->SslMainDomain);
        }

        $act = $task->createActivity('Récupération des certificats');
        //récupération des certificats
        $this->SslCertificate = $srv->getFileContent("/etc/letsencrypt/live/".$first."/fullchain.pem");
        $certinfo = openssl_x509_parse($this->SslCertificate);
        $act->addDetails($this->SslCertificate);
        $this->SslCertificateKey = $srv->getFileContent("/etc/letsencrypt/live/".$first."/privkey.pem");
        $act->addDetails($this->SslCertificateKey);
        $this->SslExpiration = $certinfo['validTo_time_t'];
        $act->addDetails('Date d\'Expiration: '.$this->SslExpiration);
        $act->Terminate(true);
        $this->Save();
        return true;
    }
    /**
     * checkCertificate
     * Vérifie la validité du certificat et récupère sa date d'expiration
     */
    public function checkCertificate($task = null) {
        if ($this->Ssl) {
            $exceptionDomains = Sys::getData('Parc','Domain/NoSSL=1');

            $certinfo = openssl_x509_parse($this->SslCertificate);
            //on vérifie qu'on a la bonne date d'expiration
            if ($this->SslExpiration!=$certinfo['validTo_time_t']){
                $this->SslExpiration = $certinfo['validTo_time_t'];
            }

            //on compare la liste des domaines à certifier et les domaines dans le certificat
            $domains=explode(' ',$this->getDomains());
            $certdomains = array();
            preg_match_all('#DNS:([^\ ,]*)#',$certinfo['extensions']['subjectAltName'],$othersdomains);
            $certdomains=array_merge($certdomains,$othersdomains[1]);
            $domainstocert = array();
            foreach ($domains as $d){
                if (empty($d))continue;
                $exception = false;
                foreach ($exceptionDomains as $ed){
                    //test des exceptions
                    if (strpos($d,$ed->Url) !== false){
                        $exception = true;
                    }

                }
                if (!in_array($d,$certdomains)&&!$exception){
                    $this->addError(array('Message'=>'Le domaine '.$d.' n\' est pas compris dans le certificat en production. Il serait nécessaire de le regénérer.'));
                }
                if (!$exception)array_push($domainstocert,$d);
            }
            //si pas de domaine à certifier , on désactive ssl
            if (!sizeof($domainstocert)){
                $this->Ssl = 0;
                $this->softSave();
                $this->addError(array('Message'=>'Aucun domaine à certifier. Désactivation du ssl.'));
                return false;
            }

            //on sauvegarde
            $this->softSave();

            if ($task){
                foreach ($this->Error as $err){
                    $task->addRetour($err['Message']."\r\n");
                }
            }

            if ($this->SslExpiration>time()){
                return true;
            }else{
                $this->addError(array('Message'=>'Le certificat a expiré le '.date('d/m/Y à H:i:s',$certinfo['validTo_time_t']).'. Il serait nécessaire de le regénérer.'));
                return false;
            }
        }
        $this->addError(array('Message'=>'Aucun domaine à certifier.'));
        return false;
    }

    /**
     * addDomain
     * Ajoute un sous domaine au vhost si il n'est pas déjà présent
     * @param string $domain  Url du sous-domaine a ajouter
     * @return boolean
     */
    public function addDomain($domain){
        $subs = $this->getDomains();
        $subs = explode(' ',$subs);
        if (!in_array($domain, $subs)) {
            $this->ApacheServerAlias .= ' ' . $domain;
            $this->Save();
        }
        return true;
    }

    /**
     * addDomain
     * Ajoute un sous domaine au vhost si il n'est pas déjà présent
     * @param string $domain  Url du sous-domaine a retirer
     * @return boolean
     */
    public function delDomain($domain){
        $subs = $this->getDomains();
        $subs = explode(' ',$subs);
        if (in_array($domain, $subs) && $domain != $this->ApacheServerName) {
            $this->ApacheServerAlias = str_replace($domain,'',$this->ApacheServerAlias);
            $this->Save();
        } else{
            return false;
        }
        return true;
    }

    /**
     * emptyProxyCacheTask
     * Supprime le chache des serveurs proxy pour cet hote virtuel
     */
    public function emptyProxyCacheTask(){
        $infra=$this->getInfra();
        Server::emptyProxyCacheTask($this,$infra);
        return true;
    }
    /**
     * emptyPageSpeedCacheTask
     * Supprime le cache pagespeed des serveurs proxy pour cet hote virtuel
     */
    public function emptyPageSpeedCacheTask(){
        $infra=$this->getInfra();
        Server::emptyPageSpeedCacheTask($this,$infra);
        return true;
    }

}