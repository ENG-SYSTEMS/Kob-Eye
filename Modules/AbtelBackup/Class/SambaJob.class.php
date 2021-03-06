<?php
require_once 'Modules/AbtelBackup/Class/Job.class.php';

class SambaJob extends Job {
    protected $tag = '[SAMBAJOB]';
    protected static $KEObj = 'SambaJob';
    protected static $desc = 'Job Samba';
    protected $pStarts = array(
        0,
        10,
        20,
        100
    );

    /**
     * stop
     * Stoppe un job de backup
     */
    public function stop()
    {
        $task = genericClass::createInstance('Systeme', 'Tache');
        $task->Type = 'Collecteur';
        $task->Nom = 'Stop :' . $this->Titre;
        $task->addParent($this);
        $task->Save();

        $v = Sys::getOneData('AbtelBackup', 'SambaShare/'.$this->CurrentShare);
        if($v){
            $act = $task->createActivity($v->Titre . ' > Arret Utilisateur: Step ' . $this->Step,'Info');
            $act->addDetails($v->Titre . "Arret Utilisateur", 'red', true);
        } else{
            $act = $task->createActivity('Share non défini > Arret Utilisateur: Step ' . $this->Step,'Info');
            $act->addDetails("Arret Utilisateur", 'red', true);
        }


        if ($this->Running){
            switch ($this->Step) {
                case 1:
                    $this->addError(Array('Message' => 'Impossible de stopper le job pendant l\'initialisation.'));
                    $act->addDetails("Impossible de stopper le job pendant l'initialisation.", 'red', true);
                    $act->Terminate(false);

                    return false;
                    break;
                case 2:
                    $this->addError(Array('Message' => 'Impossible de stopper le job pendant la configuration.'));
                    $act->addDetails("Impossible de stopper le job pendant la configuration.", 'red', true);
                    $act->Terminate(false);

                    return false;
                    break;
                case 3:
                    if (AbtelBackup::getPid('borg')) {
                        $this->clearAct(false);
                        AbtelBackup::localExec('sudo killall -9 borg');
                        $vms = Sys::getData('AbtelBackup', 'SambaJob/' . $this->Id . '/SambaShare');
                        foreach ($vms as $v) {
                            $borg = $v->getOneParent('BorgRepo');
                            AbtelBackup::localExec('sudo rm ' . $borg->Path . '/lock.* -Rf');
                        }
                        $this->addSuccess(Array('Message' => 'Déduplication stoppée avec succès.'));
                        $act->Terminate();

                    } else {
                        $this->clearAct(true);
                        $this->addWarning(Array('Message' => 'Le processus n\'a pas été trouvé.'));
                        $act->addDetails("Le processus n'a pas été trouvé.", 'red', true);
                        $act->Terminate(false);

                    }
                    $this->Running = false;
                    parent::Save();
                    return true;
                    break;
                default:
                    $this->clearAct(true);
                    $this->resetState();
                    parent::Save();
                    $this->addSuccess(Array('Message' => 'Job stoppé avec succès.'));
                    $act->Terminate();

                    return true;
                    break;
            }
        }else{

            $act->addDetails("Arret Utilisateur : Impossible de stopper le job $this->Titre. Il n'est pas démarré.", 'red',true);
            $act->Terminate(false);

            $this->addError(Array('Message'=>'Impossible de stopper le job. Il n\'est pas démarré.'));
            return false;
        }
    }
    /**
     * run
     * Demarre ou resume un job de backup de vm
     */
    public function run($task) {
        //test running
        if ($this->Running) {
            $act = $task->createActivity(' > Impossible de démarrer, le job est déjà en cours d\'éxécution','Info');
            $act->Terminate(false);
            return;
        }
        $this->resetState();
        $this->Running = true;
        parent::Save();
        //init
        Klog::l('DEBUG demarrage samba');
        $act = $task->createActivity(' > Demarrage du Job Samba : '.$this->Titre.' ('.$this->Id.')','Info');
        $act->Terminate();

        $GLOBALS['Systeme']->Db[0]->query("SET AUTOCOMMIT=1");
        //pour chaque partage
        $sss = Sys::getData('AbtelBackup','SambaJob/'.$this->Id.'/SambaShare');
        $this->TotalProgression =  Sys::getCount('AbtelBackup','SambaJob/'.$this->Id.'/SambaShare')*100;

        //calcul des progress span
        $pSpan = array();
        if (empty($sss)){
            throw new Exception('Pas de partage séléctionné',4);
        }

        try {
            for($n =0; $n < count($this->pStarts);$n++){
                $pSpan[] = ($this->pStarts[$n+1] - $this->pStarts[$n])/count($sss);
            }
        }catch (Exception $e){

        }

        foreach ($sss as $ss){
            Klog::l('DEBUG share ==> '.$ss->Id.' STEP: '.$this->Step);
            $act = $task->createActivity(' > Demarrage du partage : '.$ss->Titre.' ('.$ss->Id.')','Info');
            $act->Terminate();

            //définition de la vm en cours
            $this->setStep(1);
            $this->setCurrentSambaShare($ss->Id);
            $dev = $ss->getOneParent('SambaDevice');
            $borg = $dev->getOneParent('BorgRepo');
            try {
                //initialisation
                if (intval($this->Step)<=1){
                    unset($act);
                    $act = $task->createActivity($ss->Titre.' > Initialisation du job Samba','Exec',$pSpan[0]);
                    $this->initJob($ss,$dev,$act);
                }

                //montage
                if (intval($this->Step)<=2){
                    unset($act);
                    $act = $task->createActivity($ss->Titre.' > Montage du partage Samba','Exec',$pSpan[1]);
                    $act = $this->mountJob($ss,$dev,$act);
                }

                //déduplication
                if (intval($this->Step)<=3){
                    unset($act);
                    $act = $task->createActivity($ss->Titre.' > Déduplication Sambajob','Exec',$pSpan[2]);
                    $act = $this->deduplicateJob($ss,$dev,$borg,$act);
                }

                $act = $task->createActivity(' > Fin du partage : '.$ss->Titre.' ('.$ss->Id.')','Info');
                $act->Terminate();

            }catch (Exception $e){
                if(!$act) $act = $task->createActivity($ss->Titre.' > Exception: Step '.$this->Step);
                $act->addDetails($ss->Titre." ERROR => ".$e->getMessage(),'red');
                $act->Terminated = true;
                $act->Errors = true;
                $act->Save();
                //opération terminée
                $this->Running = false;
                $this->Errors = true;
                parent::Save();
                return;
            }
        }
        //opération terminée
        $this->resetState();

        ///tache de retention
        $this->createRetentionTask();
        return true;
    }

    /**
     * setCurrentVm
     * Déinfition de la vm en cours de traitement
     */
    private function setCurrentSambaShare($ss){
        $this->CurrentShare = $ss;
        parent::Save();
    }
    /**
     * Nettoyage de l'esx et du store local
     */
    private function initJob($ss,$dev,$act) {
        Klog::l('DEBUG Test INIT JOB');
        $this->setStep(1); //Initialisation
        $act->addDetails('Création des dossier','yellow');
        AbtelBackup::localExec("if [ ! -d '/backup/samba/".$dev->IP."/".$ss->Partage."' ]; then mkdir -p '/backup/samba/".$dev->IP."/".$ss->Partage."'; fi");
        //démontage si toujours présent
        try {
            AbtelBackup::localExec("sudo umount '/backup/samba/" . $dev->IP . "/" . $ss->Partage . "'");
        } catch (Exception $e){

        }
        //nettoyage sauvegarde précédentes
        $act->addProgression(100);
        parent::Save();
        return $act;
    }
    /**
     * mountJob
     * Montage du partage
     */
    private function mountJob($ss,$dev,$act){
        $this->setStep(2); //Montage
        $act->addDetails('-> Montage du partage '.$ss->Titre,'yellow');
        $cmd = 'sudo mount -t cifs -v "//'.$dev->IP.'/'.$ss->Partage.'" \'/backup/samba/'.$dev->IP.'/'.$ss->Partage."' -o ";

        if (!empty($dev->Login)) {
            $cmd .= "user='" . $dev->Login . "',pass='" . $dev->Password . "'";
            if (!empty($dev->Domain))
                $cmd .= ",dom='".$dev->Domain."'";
        }
        $cmd.=((!empty($dev->Login))?",":"")."uid=1000,gid=1000";
        $act->addDetails('CMD: '.$cmd);
        //AbtelBackup::localExec($cmd);
        shell_exec($cmd);
        $act->addProgression(100);
        parent::Save();
        return $act;
    }
    /**
     * deduplicateJob
     * Déduplication de la vm
     */
    private function deduplicateJob($ss,$dev,$borg,$act){
        $this->setStep(3); //'Déduplication'
        AbtelBackup::localExec('borg break-lock '.$borg->Path); //Supression des locks borg
        //AbtelBackup::localExec('borg delete --cache-only '.$borg->Path); //Supression du cache eventuellement corrompu
        AbtelBackup::localExec('sudo chown -R backup:backup '.$borg->Path.''); //On s'assure que les fichiers borg ne soient pas en root
        $total = AbtelBackup::getSize('/backup/samba/'.$dev->IP.'/'.$ss->Partage.'');
        $act->addDetails($ss->Titre.' ---> TOTAL (Mo):'.$total);
        $act->addDetails($ss->Titre.' ---> déduplication du partage');

        //Recup taille pour graphique/progression
        //$this->Size = $total;

        $point = time();
        //file_put_contents('tototoottoto',"export BORG_PASSPHRASE='".BORG_SECRET."' && borg create --progress --compression lz4 ".$borg->Path."::".$point." '/backup/nfs/".$v->Titre.".tar'");
        $det = AbtelBackup::localExec("export BORG_PASSPHRASE='".BORG_SECRET."' && borg create --progress --compression lz4 ".$borg->Path."::".$point." '/backup/samba/".$dev->IP."/".$ss->Partage."'",$act,$total,null);

        //Recup taille pour graphique/progression
        //$this->BackupSize = AbtelBackup::getSize($borg->Path);
        //parent::Save();

        //création du point de restauration
        $dev->createRestorePoint($point,$det);
        $act->setProgression(100);
        return $act;
    }
    /**
     * resetState
     * Reinitialisation du job
     */
    private function resetState(){
        $this->setStep(0); //'Attente'
        $this->setCurrentSambaShare(0);
        $this->Running = false;
        $this->Progression = 0;
        parent::Save();
    }

    /**
     * rotate
     * Rotation des backups
     */
    public function rotate($task) {
        $act = $task->createActivity(' > Demarrage de la rotation du Job Samba : '.$this->Titre.' ('.$this->Id.')','Info');
        $act->Terminate();
        $GLOBALS['Systeme']->Db[0]->query("SET AUTOCOMMIT=1");

        //pour chaque vm
        $vms = Sys::getData('AbtelBackup','SambaJob/'.$this->Id.'/SambaShare');

        foreach ($vms as $v){
            $act = $task->createActivity(' > Rotation du partage Samba : '.$v->Titre.' ('.$v->Id.')','Info');
            $act->Terminate();
            $dev = $v->getOneParent('SambaDevice');
            $borg = $dev->getOneParent('BorgRepo');
            try {
                $act->addDetails($v->Titre.' Redéfinition des droits '.$borg->Path);
                //AbtelBackup::localExec('borg delete --cache-only '.$borg->Path); //Suppression du cache eventuellement corrompu
                AbtelBackup::localExec('sudo chown -R backup:backup '.$borg->Path.''); //On s'assure que les fichiers borg ne soient pas en root
                $act->addDetails($v->Titre.' Suppression du borg lock '.$borg->Path);
                AbtelBackup::localExec('borg break-lock '.$borg->Path); //Suppression des locks borg

                //Recup taille pour graphique/progression
                /*$v->BackupSize = AbtelBackup::getSize($borg->Path);
                $v->Save();*/
                $prs = Sys::getData('AbtelBackup','SambaDevice/'.$dev->Id.'/RestorePoint/tmsCreate<'.(time()-(86400*intval($this->Retention))));
                foreach ($prs as $pr){
                    $pr->Delete();
                }

                //rotation du dépot pour nettoyer
                #TODO désactiver à terme ...
                $det = AbtelBackup::localExec("export BORG_PASSPHRASE='".BORG_SECRET."' && borg prune -v --list --keep-within=".$this->Retention."d  ".$borg->Path."", $act);
                $act->addDetails($det);

                $act->setProgression(100);
                $act = $task->createActivity(' > Rotation Terminée : suppression de '.sizeof($prs).' version(s)','Info');
                $act->Terminate(true);
            }catch (Exception $e){
                if(!$act) $act = $task->createActivity($v->Titre.' > Exception: Step '.$this->Step,'Info');
                $act->addDetails($v->Titre." ERROR => ".$e->getMessage(),'red');
                $act->Terminate(false);
                //opération terminée
                $this->Running = false;
                $this->Errors = true;
                parent::Save();
                continue;
            }
        }
        $this->saveStats($task);
        return true;
    }

    /**
     * createRetentionTask
     * Création de la tache de retention
     */
    public function createRetentionTask() {
        $task = genericClass::createInstance('Systeme', 'Tache');
        $task->Type = 'Fonction';
        $task->Nom = 'Rotation job machine virtuelle :' . $this->Titre.'. rotation du '.date('d/m/Y H:i:s');
        $task->TaskModule = 'AbtelBackup';
        $task->TaskObject = 'SambaJob';
        $task->TaskType = 'rotate';
        $task->TaskId = $this->Id;
        $task->TaskFunction = 'rotate';
        $task->addParent($this);
        $task->Save();
        return array('task'=>$task);
    }



}