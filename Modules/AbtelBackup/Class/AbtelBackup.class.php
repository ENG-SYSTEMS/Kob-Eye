<?php
/**
 * Class AbtelBackup
 * Installation des taches plafnifiées
 * 0 6 * * * apache /usr/bin/php /var/www/html/cron.php parc.azko.fr Parc/Bash/Renew.cron
 * *\/5 * * * * apache /usr/bin/php /var/www/html/cron.php parc.azko.fr Parc/Bash/Execute.cron
 */
class AbtelBackup extends Module{
    public $classLoader=null;
    /**
     * Surcharge de la fonction init
     * Avant l'authentification de l'utilisateur
     * @void
     */
    function init (){
        parent::init();
    }
    /**
     * Surcharge de la fonction postInit
     * Après l'authentification de l'utilisateur
     * Toutes les fonctionnalités sont disponibles
     * @void
     */
    function postInit (){
        parent::postInit();
        //chargement des variables globales par défaut pour le module boutique
        $this->initGlobalVars();
    }
    /**
     * Surcharge de la fonction Check
     * Vérifie l'existence du role PARC_CLIENT et son association à un groupe
     * Sinon génère le ROLE et créé un Group à la racine et lui affecte le ROLE
     */
    function Check () {
        parent::Check();
        //teste le role
        $r = Sys::getData('Systeme','Role/Title=BACKUP_ADMIN');
        if (sizeof($r)){
            $r = $r[0];
            //teste le groupe
            $g = $r->getChildren('Group');
            if (sizeof($g)){
                //test user
                $u = $g[0]->getChildren('User');
                if (!sizeof($u))
                    $this->createUser($g[0]);
            }else{
                $g = $this->createGroup($r);
                $this->createUser($g);
            }
        }else{
            //il faut tout créer
            //création du role
            $r = genericClass::createInstance('Systeme','Role');
            $r->Title = "BACKUP_ADMIN";
            $r->Save();
            //création du groupe
            $g = $this->createGroup($r);
            $this->createUser($g);
        }

        $store = Sys::getData('AbtelBackup','BackupStore/Titre=Sauvegarde Locale');
        if (!sizeof($store)){
            $s = genericClass::createInstance('AbtelBackup','BackupStore');
            $s->Titre = 'Sauvegarde Locale';
            $s->Type = 'Local';
            $s->Save();
        }
        //BANDWITH
        $t = Sys::getCount('Systeme','ScheduledTask/TaskModule=AbtelBackup&TaskObject=AbtelBackup&TaskFunction=getBandWidthCron');
        if (!$t) {
            //creation du groupe public
            $t = genericClass::createInstance('Systeme', 'ScheduledTask');
            $t->Titre = 'Moniteur systeme';
            $t->Enabled = 1;
            $t->TaskModule = 'AbtelBackup';
            $t->TaskObject = 'AbtelBackup';
            $t->TaskFunction = 'getBandWidthCron';
            $t->Save();
        }
        //REMOTE JOB
        $t = Sys::getCount('Systeme','ScheduledTask/TaskModule=AbtelBackup&TaskObject=RemoteJob&TaskFunction=execute');
        if (!$t) {
            //creation du groupe public
            $t = genericClass::createInstance('Systeme', 'ScheduledTask');
            $t->Titre = 'Execution AbtelBackup RemoteJob toutes les minutes';
            $t->Enabled = 1;
            $t->TaskModule = 'AbtelBackup';
            $t->TaskObject = 'RemoteJob';
            $t->TaskFunction = 'execute';
            $t->Save();
        }

        $t = Sys::getCount('Systeme','ScheduledTask/TaskModule=AbtelBackup&TaskObject=SambaJob&TaskFunction=execute');
        if (!$t) {
            //creation du groupe public
            $t = genericClass::createInstance('Systeme', 'ScheduledTask');
            $t->Titre = 'Execution AbtelBackup SambaJob toutes les minutes';
            $t->Enabled = 1;
            $t->TaskModule = 'AbtelBackup';
            $t->TaskObject = 'SambaJob';
            $t->TaskFunction = 'execute';
            $t->Save();
        }
        $t = Sys::getCount('Systeme','ScheduledTask/TaskModule=AbtelBackup&TaskObject=VmJob&TaskFunction=execute');
        if (!$t) {
            //creation du groupe public
            $t = genericClass::createInstance('Systeme', 'ScheduledTask');
            $t->Titre = 'Execution AbtelBackup VmJob toutes les minutes';
            $t->Enabled = 1;
            $t->TaskModule = 'AbtelBackup';
            $t->TaskObject = 'VmJob';
            $t->TaskFunction = 'execute';
            $t->Save();
        }
        $t = Sys::getCount('Systeme','ScheduledTask/TaskModule=AbtelBackup&TaskObject=HyperJob&TaskFunction=execute');
        if (!$t) {
            //creation du groupe public
            $t = genericClass::createInstance('Systeme', 'ScheduledTask');
            $t->Titre = 'Execution AbtelBackup HyperJob toutes les minutes';
            $t->Enabled = 1;
            $t->TaskModule = 'AbtelBackup';
            $t->TaskObject = 'HyperJob';
            $t->TaskFunction = 'execute';
            $t->Save();
        }

    }
    /**
     * Creation du groupe et de tout ses menus
     */
    private function createGroup($role){
        //creation du groupe
        $g = genericClass::createInstance('Systeme','Group');
        $g->Nom = "[BACKUP] Accès admin";
        $g->Skin = "AngularAdmin";
        $g->AddParent($role);
        $g->Save();
        $g->importMenus('YTo2OntpOjA7YTozMTp7czozOiJVcmwiO3M6MTM6IlRhYmxlYXVEZUJvcmQiO3M6NToiVGl0cmUiO3M6MTU6IlRhYmxlYXUgZGUgYm9yZCI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyMToiQWJ0ZWxCYWNrdXAvRGFzaGJvYXJkIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjE6IjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjI2OiJBQlRFTEJBQ0tVUCAtIEFkbWluIEFjY2VzcyI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMCI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MTthOjMxOntzOjM6IlVybCI7czoyOiJ2bSI7czo1OiJUaXRyZSI7czo2OiJWbVdhcmUiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MDoiIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjEwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiIwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjM6e2k6MDthOjMxOntzOjM6IlVybCI7czozOiJlc3giO3M6NToiVGl0cmUiO3M6NDoiRVNYaSI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoxNToiQWJ0ZWxCYWNrdXAvRXN4IjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtOO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoxOiIwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtOO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7TjtzOjEwOiJNZW51UGFyZW50IjtzOjE6IjYiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjE7YTozMTp7czozOiJVcmwiO3M6Mzoidm1zIjtzOjU6IlRpdHJlIjtzOjE5OiJNYWNoaW5lcyB2aXJ0dWVsbGVzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjE3OiJBYnRlbEJhY2t1cC9Fc3hWbSI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7TjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiNiI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MjthOjMxOntzOjM6IlVybCI7czo3OiJlc3hqb2JzIjtzOjU6IlRpdHJlIjtzOjI1OiJKb2JzIGRlIG1hY2hpbmUgdmlydHVlbGxlIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjE3OiJBYnRlbEJhY2t1cC9WbUpvYiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7TjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiNiI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fX19aToyO2E6MzE6e3M6MzoiVXJsIjtzOjU6InNoYXJlIjtzOjU6IlRpdHJlIjtzOjE2OiJQYXJ0YWdlcyB3aW5kb3dzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjA6IiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7TjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMCI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTozOntpOjA7YTozMTp7czozOiJVcmwiO3M6MTE6InNhbWJhZGV2aWNlIjtzOjU6IlRpdHJlIjtzOjIzOiJNYWNoaW5lcyDDoCBzYXV2ZWdhcmRlciI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyMzoiQWJ0ZWxCYWNrdXAvU2FtYmFEZXZpY2UiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO047czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjEwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtOO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7TjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjEwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToxO2E6MzE6e3M6MzoiVXJsIjtzOjEyOiJ3aW5kb3dzc2hhcmUiO3M6NToiVGl0cmUiO3M6ODoiUGFydGFnZXMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjI6IkFidGVsQmFja3VwL1NhbWJhU2hhcmUiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO047czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjIwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtOO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7TjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjEwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToyO2E6MzE6e3M6MzoiVXJsIjtzOjk6IndpbmRvd2pvYiI7czo1OiJUaXRyZSI7czoyMToiSm9icyBwYXJ0YWdlcyB3aW5kb3dzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjIwOiJBYnRlbEJhY2t1cC9TYW1iYUpvYiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7TjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMzAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMTAiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX19fWk6MzthOjMxOntzOjM6IlVybCI7czo2OiJoeXBlcnYiO3M6NToiVGl0cmUiO3M6NjoiSHlwZXJWIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjExOiJBYnRlbEJhY2t1cCI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIyMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMCI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTozOntpOjA7YTozMTp7czozOiJVcmwiO3M6Njoic2VydmVyIjtzOjU6IlRpdHJlIjtzOjE0OiJTZXJ2ZXVyIEh5cGVyViI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoxODoiQWJ0ZWxCYWNrdXAvSHlwZXJ2IjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjE6IjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjIyIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToxO2E6MzE6e3M6MzoiVXJsIjtzOjg6Imh5cGVydm1zIjtzOjU6IlRpdHJlIjtzOjI2OiJNYWNoaW5lcyB2aXJ0dWVsbGVzIEh5cGVyViI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyMDoiQWJ0ZWxCYWNrdXAvSHlwZXJ2Vm0iO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjIyIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToyO2E6MzE6e3M6MzoiVXJsIjtzOjg6Imh5cGVyam9iIjtzOjU6IlRpdHJlIjtzOjEwOiJKb2IgSHlwZXJWIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjIwOiJBYnRlbEJhY2t1cC9IeXBlckpvYiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIzMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMjIiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX19fWk6NDthOjMxOntzOjM6IlVybCI7czo2OiJyZW1vdGUiO3M6NToiVGl0cmUiO3M6MTU6IkV4dGVybmFsaXNhdGlvbiI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czowOiIiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiNDAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMCI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YToyOntpOjA7YTozMTp7czozOiJVcmwiO3M6NDoic3luYyI7czo1OiJUaXRyZSI7czozMjoiSm9icyBkZSBzeW5jaHJvbmlzYXRpb24gZGlzdGFudGUiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjE6IkFidGVsQmFja3VwL1JlbW90ZUpvYiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7TjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO047czoxNToiUGFnZURlc2NyaXB0aW9uIjtOO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMjEiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjE7YTozMTp7czozOiJVcmwiO3M6Njoic2VydmVyIjtzOjU6IlRpdHJlIjtzOjE3OiJEw6lww7R0cyBkaXN0YW50cyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyNDoiQWJ0ZWxCYWNrdXAvUmVtb3RlU2VydmVyIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjMwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtOO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7TjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjIxIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319fX1pOjU7YTozMTp7czozOiJVcmwiO3M6NjoiY29uZmlnIjtzOjU6IlRpdHJlIjtzOjEzOiJDb25maWd1cmF0aW9uIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjA6IiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiI1MCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7TjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO047czoxMDoiTWVudVBhcmVudCI7czoxOiIwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjM6e2k6MDthOjMxOntzOjM6IlVybCI7czo2OiJ0YWNoZXMiO3M6NToiVGl0cmUiO3M6NjoiVGFjaGVzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjEzOiJTeXN0ZW1lL1RhY2hlIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjE6IjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjEzIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToxO2E6MzE6e3M6MzoiVXJsIjtzOjQ6ImJvcmciO3M6NToiVGl0cmUiO3M6MTM6IkTDqXDDtHRzIEJvcmciO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjA6IkFidGVsQmFja3VwL0JvcmdSZXBvIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtOO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIxMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7TjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO047czoxMDoiTWVudVBhcmVudCI7czoyOiIxMyI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MjthOjMxOntzOjM6IlVybCI7czo1OiJ1dGlscyI7czo1OiJUaXRyZSI7czoxMToiVXRpbGl0YWlyZXMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjM6IkFidGVsQmFja3VwL1V0aWxpdGFpcmVzIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtOO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIyMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7TjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO047czoxMDoiTWVudVBhcmVudCI7czoyOiIxMyI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fX19fQ==');
        return $g;
    }
    /**
     * Creation du groupe et de tout ses menus
     */
    private function createUser($group){
        //creation du groupe
        $g = genericClass::createInstance('Systeme','User');
        $g->Mail = "backup@abtel.fr";
        $g->Nom = "Backup";
        $g->Prenom = "Admin";
        $g->Login = "Backup";
        $g->Pass = md5("backup");
        $g->Actif = 1;
        $g->AddParent($group);
        $g->Save();
        return $g;
    }
    /**
     * Initilisation des variables globales disponibles pour la boutique
     */
    private function initGlobalVars(){
    }
    /**
     * UTILS FUNCTIONS
     */
    static public function localExec( $command, $activity = null,$total=0,$path=null,$stderr = false)
    {
        /*exec( $command,$output,$return);
        if( $return ) {
            throw new RuntimeException( "L'éxécution de la commande locale a échoué. commande : ".$command." \n ".print_r($output,true));
        }
        return implode("\n",$output);*/
        $proc = popen($command.' '.($stderr?'2>&1':'').' ; echo Exit status : $?', 'r');
        $complete_output = "";
        if ($path && is_file($path) && is_readable($path)){
            //On fork le process pour calculer le progress en parallele
            switch ($pid = pcntl_fork()) {
                case -1:
                    // @fail
                    $activity->addDetails('No Fork , No Progress');
                    break;

                case 0:
                    // @child: Include() misbehaving code here
                    while (!feof($proc)) {
                        $size = AbtelBackup::getSize($path);
                        $progress = floatval($size)*100/$total;
                        $progress = intval($progress);
                        if ($progress != $activity->Progression){
                            $activity->setProgression($progress);
                        }

                        sleep(5);
                    }
                    exit;
                    break;

                default:
                    // @parent
                    break;
            }
        }

        while (!feof($proc)){
            $buf     = fread($proc, 4096);
            $progress = 0;

            //cas borg
            if (preg_match('#O ([0-9\.]+)? MB C#',$buf,$out)&&$activity&&$total) {
                $progress = (floatval($out[1]))/$total;
                $buf = '';
            }
            //347.08 GB O 285.33 GB C 212.73 G
            if (preg_match('#O ([0-9\.]+)? GB C#',$buf,$out)&&$activity&&$total) {
                $progress = (floatval($out[1])*1024)/$total;
                $buf = '';
            }
            if (preg_match('#O ([0-9\.]+)? TB C#',$buf,$out)&&$activity&&$total) {
                $progress = (floatval($out[1])*1048576)/$total;
                $buf = '';
            }
            //cas rsync
            if (preg_match('#([0-9]+)?%#',$buf,$out)&&$activity) {
                $progress = intval($out[1])/100;
                $buf = '';
            }
            if($progress&&intval($progress*100)!=$activity->Progression){
                $activity->setProgression($progress*100);
            }


            $complete_output .= $buf;
        }


        if($path){
            //On tue le fork pour eviter les process zombies
            if($pid > 0){
                posix_kill ( $pid , SIGKILL );
                //Si le fork a marché on attend la mort de l'enfant
                pcntl_waitpid($pid, $status);
            }
        }

        pclose($proc);
        // get exit status
        preg_match('/(?<=Exit status : )[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        if(  isset($matches[0]) && $matches[0] !== "0" && !$stderr) {
            throw new RuntimeException( $complete_output, (int)$matches[0] );
        }
        return str_replace("Exit status : " . ((isset($matches[0]))?$matches[0]:0), '', $complete_output);
    }

    static public function getPid($prog){
        try {
            $output = AbtelBackup::localExec('sudo pgrep ' . $prog);
        }catch (Exception $e){
            return false;
        }
        return $output;
    }
    static public function getMyIp($last = false){
        $output = AbtelBackup::localExec('/usr/sbin/ifconfig');
        preg_match_all('#inet ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)#',$output,$out);
        if (sizeof($out[1])>2&&$last)
            return $out[1][1];
        else return $out[1][0];
    }
    static function getFileSize($path){
        $output = AbtelBackup::localExec('/usr/bin/ls -l "'.$path.'"');
        preg_match('#^[rwx-]+ [0-9]{1} [^ ]+ [^ ]+ ([0-9]+)#',$output,$out);
        return $out[1];
    }
    static function resetData(){
        //Suppression des fichiers
        AbtelBackup::localExec('sudo rm /backup/nfs/* -Rf');
        AbtelBackup::localExec('sudo rm /backup/borg/* -Rf');
        AbtelBackup::localExec('sudo rm /backup/restore/* -Rf');
        //AbtelBackup::localExec('sudo rm /backup/samba/* -Rf');
        //vidage des tables
        $GLOBALS["Systeme"]->Db[0]->query('TRUNCATE `kob-AbtelBackup-Activity`;TRUNCATE `kob-AbtelBackup-BackupStore`;TRUNCATE `kob-AbtelBackup-BorgRepo`;TRUNCATE `kob-AbtelBackup-Esx`;TRUNCATE `kob-AbtelBackup-EsxVm`;TRUNCATE `kob-AbtelBackup-EsxVmRestorePointId`;TRUNCATE `kob-AbtelBackup-HyperJob`;TRUNCATE `kob-AbtelBackup-HyperJobHypervVmJobId`;TRUNCATE `kob-AbtelBackup-Hyperv`;TRUNCATE `kob-AbtelBackup-HypervVm`;TRUNCATE `kob-AbtelBackup-HypervVmRestorePointId`;TRUNCATE `kob-AbtelBackup-RemoteJob`;TRUNCATE `kob-AbtelBackup-RemoteJob-Interval`;TRUNCATE `kob-AbtelBackup-RemoteJobBorgRepoId`;TRUNCATE `kob-AbtelBackup-RemoteJob`;TRUNCATE `kob-AbtelBackup-RestorePoint`;TRUNCATE `kob-AbtelBackup-SambaDevice`;TRUNCATE `kob-AbtelBackup-SambaDevicePartages`;TRUNCATE `kob-AbtelBackup-SambaDeviceRestorePointSambaDeviceId`;TRUNCATE `kob-AbtelBackup-SambaJob`;TRUNCATE `kob-AbtelBackup-SambaJobSambaShareId`;TRUNCATE `kob-AbtelBackup-SambaShare`;TRUNCATE `kob-AbtelBackup-State`;TRUNCATE `kob-AbtelBackup-VmJob`;TRUNCATE `kob-AbtelBackup-VmJobEsxVmJobId`;TRUNCATE `kob-AbtelBackup-VmJobVmJobId`;TRUNCATE `kob-AbtelBackup-VmJob`;TRUNCATE `kob-Systeme-Activity`;TRUNCATE `kob-Systeme-Tache`;TRUNCATE `kob-AbtelBackup-VmJob`;');

        //Recalcul espace disque
        BackupStore::getDiskUsage();

        return true;
    }
    static function initFolders(){
        //Suppression des fichiers
        AbtelBackup::localExec('if [ ! -d /backup ]; then sudo mkdir /backup; fi');
        AbtelBackup::localExec('if [ ! -d /backup/nfs ]; then sudo mkdir /backup/nfs; fi');
        AbtelBackup::localExec('sudo chown nfsnobody:nfsnobody /backup/nfs');
        AbtelBackup::localExec('if [ ! -d /backup/borg ]; then sudo mkdir /backup/borg; fi');
        AbtelBackup::localExec('sudo chown backup:backup /backup/borg');
        AbtelBackup::localExec('if [ ! -d /backup/restore ]; then sudo mkdir /backup/restore; fi');
        AbtelBackup::localExec('sudo chown backup:backup /backup/restore');
        AbtelBackup::localExec('if [ ! -d /backup/samba ]; then sudo mkdir /backup/samba; fi');
        AbtelBackup::localExec('sudo chown backup:backup /backup/samba');
        AbtelBackup::localExec('if [ ! -d /backup/nas]; then sudo mkdir /backup/nas; fi');
        AbtelBackup::localExec('sudo chown backup:backup /backup/nas');
        AbtelBackup::localExec('sudo chown backup:backup /backup');
        //redemarrge des services
        AbtelBackup::localExec('sudo systemctl restart nfs-server');
        return true;
    }
    static function getSize($path){
        return AbtelBackup::localExec('sudo du -sBM "'.$path.'" | sed "s/^\([0-9]\+\).*/\1/g"');
    }
    static function initDD(){
        return AbtelBackup::localExec('sudo /var/www/html/Modules/AbtelBackup/InitDD/initDD.sh');
    }
    /**
     * sync
     * Rsync command avec limite de bande passante.
     * @param $path
     * @param string $bw
     * @return mixed
     */
    public static function sync( $path,$dest,$user,$ip,$bw = '5000',$act = null,$progData=null){
        $cmd = 'rsync -az --delete --info=progress2 -e " ssh -o StrictHostKeychecking=no -i /var/www/html/.ssh/id_'.$ip.'" --bwlimit='.$bw.' '.$path.' '.$user.'@'.$ip.':/home/'.$user.'/'.$dest;
        if ($act){
            $act->addDetails('CMD: '.$cmd);
        }
        return AbtelBackup::localExec($cmd,$act,0,null,$progData);
    }

    /**
     * getBandWidthCron
     * Recolte les informations systeme et stocke les statistiques en base de donnée
     */
    static function getBandWidthCron(){
        $start = time();
        Sys::autocommitTransaction();
        //enregistrement des tailles
        $FreeSize = intval(AbtelBackup::localExec('df -BM --output=avail /backup | tail -n 1')); //pour passe en ko virer -BG
        $NfsSize = intval(AbtelBackup::getSize('/backup/nfs'));
        $BorgSize = intval(AbtelBackup::getSize('/backup/borg'));
        $NasSize = intval(AbtelBackup::getSize('/backup/nas'));
        $RestoreSize = intval(AbtelBackup::getSize('/backup/restore'));
        //prépare le sar pour une prochaine execution
        while(time()<$start+60){
            //CPU
            $cpu = AbtelBackup::localExec('top -b -n1 | grep "Cpu(s)" | awk \'{print $2 + $4}\'');
            //RAM
            //free -m | awk 'NR==2{printf "Memory Usage: %s/%sMB (%.2f%%)\n", $3,$2,$3*100/$2 }
            $ram = AbtelBackup::localExec('free -m | awk \'NR==2{printf "%.2f", $3*100/$2 }\'');
            //IO
            try {
                $cmd = 'nohup sar 1 1| tail -n2 | head -n 1 | awk \'{ print $NF}\' | sed -e \'s/,/./\' | awk \'{print 100-$NF}\' > '.getcwd().'/Modules/AbtelBackup/Cron/sar.tmp';
                AbtelBackup::localExec($cmd);
            }catch (Exception $e){
            }
            $io = AbtelBackup::localExec('cat '.getcwd().'/Modules/AbtelBackup/Cron/sar.tmp');
            //NETWORK
            $network = AbtelBackup::localExec('sh '.getcwd().'/Modules/AbtelBackup/Cron/bandwidth.sh');
            $network = explode('|',$network);
            //enregistrement des valeurs
            $s = genericClass::createInstance('AbtelBackup','State');
            $s->FreeSize = $FreeSize;
            $s->NfsSize = $NfsSize;
            $s->BorgSize = $BorgSize;
            $s->NasSize = $NasSize;
            $s->RestoreSize = $RestoreSize;
            $s->CpuUsage = $cpu;
            $s->RamUsage = $ram;
            $s->IOUsage = $io;
            $s->RX = $network[1];
            $s->TX = $network[0];
            $s->Save();
            //file_put_contents('/tmp/debugbw',time().PHP_EOL,8);
            sleep(8);
            //file_put_contents('/tmp/debugbw',time().PHP_EOL,8);
        }
        //suppression des données supérieurs à 3j
        $GLOBALS['Systeme']->Db[0]->query('DELETE FROM `'.MAIN_DB_PREFIX.'AbtelBackup-State` WHERE tmsCreate<'.(time()-(86400*3)).';');
        return true;
    }
    static function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

}
