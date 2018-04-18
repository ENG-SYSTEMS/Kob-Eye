<?php

class IncidentClient extends Module {

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
     * Vérifie l'existence des groupes, roles, access nécéssaires
     */
    public function Check(){
        //Verif de l'existance des groupes nécéssaires
        $cliGrp = Sys::getOneData('Systeme','Group/Nom=[INCIDENT] Client');
        if(!$cliGrp){
            $cliGrp = genericClass::createInstance('Systeme','Group');
            $cliGrp->Nom = '[INCIDENT] Client';
            $cliGrp->Save();
        }
        $cMenus = $cliGrp->getChildren('Menu');
        if(count($cMenus) == 0)
            $cliGrp->importMenus('YToyOntpOjA7YTozMTp7czozOiJVcmwiO3M6MTM6IlRhYmxlYXVEZUJvcmQiO3M6NToiVGl0cmUiO3M6MTU6IlRBQkxFQVUgREUgQk9SRCI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoxODoiQ21zL0Rhc2hCb2FyZE9jZWFuIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjEwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiIwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToxO2E6MzE6e3M6MzoiVXJsIjtzOjEyOiJlc3BhY2VjbGllbnQiO3M6NToiVGl0cmUiO3M6MTM6IkVTUEFDRSBDTElFTlQiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MDoiIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjIwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiIwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjM6e2k6MDthOjMxOntzOjM6IlVybCI7czoxMjoidm9zaW5jaWRlbnRzIjtzOjU6IlRpdHJlIjtzOjE5OiJMaXN0ZSBkZXMgaW5jaWRlbnRzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjIzOiJJbmNpZGVudENsaWVudC9JbmNpZGVudCI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIxMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMTEiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjE7YTozMTp7czozOiJVcmwiO3M6MTE6InZvc2ZpY2hpZXJzIjtzOjU6IlRpdHJlIjtzOjIzOiJSYXBwb3J0IGRcJ2ludGVydmVudGlvbiI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyMjoiSW5jaWRlbnRDbGllbnQvUmFwcG9ydCI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIyMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMTEiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjI7YTozMTp7czozOiJVcmwiO3M6OToiZG9jdW1lbnRzIjtzOjU6IlRpdHJlIjtzOjEzOiJWb3MgZG9jdW1lbnRzIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjIzOiJJbmNpZGVudENsaWVudC9Eb2N1bWVudCI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIzMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMTEiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX19fX0=');
        $cliGrp->Set('Skin','AngularAdmin');
        $cliGrp->Save();


        $techGrp = Sys::getOneData('Systeme','Group/Nom=[INCIDENT] Technicien');
        if(!$techGrp){
            $techGrp = genericClass::createInstance('Systeme','Group');
            $techGrp->Nom = '[INCIDENT] Technicien';
            $techGrp->Save();
        }
        $tMenus = $techGrp->getChildren('Menu');
        if(count($tMenus) == 0)
            $techGrp->importMenus('YToyOntpOjA7YTozMTp7czozOiJVcmwiO3M6MTM6IlRhYmxlYXVEZUJvcmQiO3M6NToiVGl0cmUiO3M6OToiREFTSEJPQVJEIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjE4OiJDbXMvRGFzaEJvYXJkT2NlYW4iO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MToiMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMCI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MTthOjMxOntzOjM6IlVybCI7czoxNjoiZXNwYWNldGVjaG5pY2llbiI7czo1OiJUaXRyZSI7czoxNzoiRVNQQUNFIFRFQ0hOSUNJRU4iO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MDoiIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjIwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiIwIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjE6e2k6MDthOjMxOntzOjM6IlVybCI7czoxNToiZ2VzdGlvbmluY2lkZW50IjtzOjU6IlRpdHJlIjtzOjIxOiJHZXN0aW9uIGRlcyBpbmNpZGVudHMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjM6IkluY2lkZW50Q2xpZW50L0luY2lkZW50IjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjEwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiI4IjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319fX19');
        $techGrp->Set('Skin','AngularAdmin');
        $techGrp->Save();


        $adminGrp = Sys::getOneData('Systeme','Group/Nom=[INCIDENT] Administrateur');
        if(!$adminGrp){
            $adminGrp = genericClass::createInstance('Systeme','Group');
            $adminGrp->Nom = '[INCIDENT] Administrateur';
            $adminGrp->Save();
        }
        $aMenus = $adminGrp->getChildren('Menu');
        if(count($aMenus) == 0)
            $adminGrp->importMenus('YTozOntpOjA7YTozMTp7czozOiJVcmwiO3M6MTM6IlRhYmxlYXVEZUJvcmQiO3M6NToiVGl0cmUiO3M6OToiREFTSEJPQVJEIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjE4OiJDbXMvRGFzaEJvYXJkT2NlYW4iO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMTAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjE6IjAiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjE7YTozMTp7czozOiJVcmwiO3M6MTY6Ikdlc3Rpb25JbmNpZGVudHMiO3M6NToiVGl0cmUiO3M6MjE6IkVTUEFDRSBBRE1JTklTVFJBVEVVUiI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czowOiIiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMjAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjE6IjAiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6NDp7aTowO2E6MzE6e3M6MzoiVXJsIjtzOjc6IkNsaWVudHMiO3M6NToiVGl0cmUiO3M6MTE6IkxFUyBDTElFTlRTIjtzOjk6IlNvdXNUaXRyZSI7czowOiIiO3M6NDoiTGllbiI7czowOiIiO3M6NzoiQWZmaWNoZSI7czoxOiIxIjtzOjU6IkFsaWFzIjtzOjIxOiJJbmNpZGVudENsaWVudC9DbGllbnQiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiMTUiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjE6IjIiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjE7YTozMTp7czozOiJVcmwiO3M6MTA6IlRlY2huaWNpZW4iO3M6NToiVGl0cmUiO3M6MTU6IkxFUyBURUNITklDSUVOUyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyNToiSW5jaWRlbnRDbGllbnQvVGVjaG5pY2llbiI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiIyMCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMiI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MjthOjMxOntzOjM6IlVybCI7czo5OiJpbmNpZGVudHMiO3M6NToiVGl0cmUiO3M6MTM6IkxFUyBJTkNJREVOVFMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MjM6IkluY2lkZW50Q2xpZW50L0luY2lkZW50IjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6IjMwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoxOiIyIjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aTozO2E6MzE6e3M6MzoiVXJsIjtzOjc6IlJhcHBvcnQiO3M6NToiVGl0cmUiO3M6MTI6IkxFUyBSQVBQT1JUUyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyMjoiSW5jaWRlbnRDbGllbnQvUmFwcG9ydCI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiI0MCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MToiMiI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fX19aToyO2E6MzE6e3M6MzoiVXJsIjtzOjExOiJQYXJhbWV0cmFnZSI7czo1OiJUaXRyZSI7czoxMzoiUEFSQU3DiVRSQUdFUyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czowOiIiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiNTAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjE6IjAiO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6NDp7aTowO2E6MzE6e3M6MzoiVXJsIjtzOjExOiJ0eXBlZGVtYW5kZSI7czo1OiJUaXRyZSI7czozNzoiUGFyYW3DqXRyYWdlIGRlcyBvcmlnaW5lcyBkZSBkZW1hbmRlcyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czozMjoiSW5jaWRlbnRDbGllbnQvUGFyYW1ldHJlc0RlbWFuZGUiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MjoiNzAiO3M6ODoiTWVudUhhdXQiO3M6MToiMCI7czo3OiJNZW51QmFzIjtzOjE6IjAiO3M6MTM6Ik1lbnVQcmluY2lwYWwiO3M6MToiMSI7czoxMToiTWVudVNwZWNpYWwiO3M6MToiMCI7czoxMDoiQXV0b1N1YkdlbiI7czoxOiIwIjtzOjU6IlRpdGxlIjtzOjA6IiI7czoxMToiRGVzY3JpcHRpb24iO3M6MDoiIjtzOjg6IktleXdvcmRzIjtzOjA6IiI7czo1OiJJbWFnZSI7czowOiIiO3M6ODoiVGVtcGxhdGUiO3M6MToiMCI7czo5OiJQYWdlVGl0cmUiO3M6MDoiIjtzOjE1OiJQYWdlRGVzY3JpcHRpb24iO3M6MDoiIjtzOjEwOiJNZW51UGFyZW50IjtzOjI6IjE3IjtzOjEwOiJPYmplY3RUeXBlIjtzOjQ6Ik1lbnUiO3M6NDoibm90ZSI7aToxMDtzOjY6Ik1vZHVsZSI7czo3OiJTeXN0ZW1lIjtzOjU6Ik1lbnVzIjthOjA6e319aToxO2E6MzE6e3M6MzoiVXJsIjtzOjExOiJ0eXBvZGVtYW5kZSI7czo1OiJUaXRyZSI7czozODoiUGFyYW3DqXRyYWdlIGRlcyB0eXBvbG9naWUgZGUgZGVtYW5kZXMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6Mjk6IkluY2lkZW50Q2xpZW50L1BhcmFtZXRyZXNUeXBvIjtzOjc6IkZpbHRlcnMiO3M6MDoiIjtzOjE2OiJQcmVmaXhlQ29kZWJhcnJlIjtzOjA6IiI7czo0OiJBaWRlIjtzOjA6IiI7czo1OiJJY29uZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRJbWFnZSI7czowOiIiO3M6MTU6IkJhY2tncm91bmRDb2xvciI7czowOiIiO3M6ODoiQ2xhc3NDc3MiO3M6MDoiIjtzOjU6Ik9yZHJlIjtzOjI6Ijc1IjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoyOiIxNyI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fWk6MjthOjMxOntzOjM6IlVybCI7czoxMjoiZGVsYWlkZW1hbmRlIjtzOjU6IlRpdHJlIjtzOjI0OiJQYXJhbcOpdHJhZ2UgZGVzIGTDqWxhaXMiO3M6OToiU291c1RpdHJlIjtzOjA6IiI7czo0OiJMaWVuIjtzOjA6IiI7czo3OiJBZmZpY2hlIjtzOjE6IjEiO3M6NToiQWxpYXMiO3M6MzA6IkluY2lkZW50Q2xpZW50L1BhcmFtZXRyZXNEZWxhaSI7czo3OiJGaWx0ZXJzIjtzOjA6IiI7czoxNjoiUHJlZml4ZUNvZGViYXJyZSI7czowOiIiO3M6NDoiQWlkZSI7czowOiIiO3M6NToiSWNvbmUiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kSW1hZ2UiO3M6MDoiIjtzOjE1OiJCYWNrZ3JvdW5kQ29sb3IiO3M6MDoiIjtzOjg6IkNsYXNzQ3NzIjtzOjA6IiI7czo1OiJPcmRyZSI7czoyOiI4MCI7czo4OiJNZW51SGF1dCI7czoxOiIwIjtzOjc6Ik1lbnVCYXMiO3M6MToiMCI7czoxMzoiTWVudVByaW5jaXBhbCI7czoxOiIxIjtzOjExOiJNZW51U3BlY2lhbCI7czoxOiIwIjtzOjEwOiJBdXRvU3ViR2VuIjtzOjE6IjAiO3M6NToiVGl0bGUiO3M6MDoiIjtzOjExOiJEZXNjcmlwdGlvbiI7czowOiIiO3M6ODoiS2V5d29yZHMiO3M6MDoiIjtzOjU6IkltYWdlIjtzOjA6IiI7czo4OiJUZW1wbGF0ZSI7czoxOiIwIjtzOjk6IlBhZ2VUaXRyZSI7czowOiIiO3M6MTU6IlBhZ2VEZXNjcmlwdGlvbiI7czowOiIiO3M6MTA6Ik1lbnVQYXJlbnQiO3M6MjoiMTciO3M6MTA6Ik9iamVjdFR5cGUiO3M6NDoiTWVudSI7czo0OiJub3RlIjtpOjEwO3M6NjoiTW9kdWxlIjtzOjc6IlN5c3RlbWUiO3M6NToiTWVudXMiO2E6MDp7fX1pOjM7YTozMTp7czozOiJVcmwiO3M6MTE6ImV0YXRkZW1hbmRlIjtzOjU6IlRpdHJlIjtzOjM1OiJQYXJhbcOpdHJhZ2UgZGVzIMOpdGF0cyBkZSBkZW1hbmRlcyI7czo5OiJTb3VzVGl0cmUiO3M6MDoiIjtzOjQ6IkxpZW4iO3M6MDoiIjtzOjc6IkFmZmljaGUiO3M6MToiMSI7czo1OiJBbGlhcyI7czoyOToiSW5jaWRlbnRDbGllbnQvUGFyYW1ldHJlc0V0YXQiO3M6NzoiRmlsdGVycyI7czowOiIiO3M6MTY6IlByZWZpeGVDb2RlYmFycmUiO3M6MDoiIjtzOjQ6IkFpZGUiO3M6MDoiIjtzOjU6Ikljb25lIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZEltYWdlIjtzOjA6IiI7czoxNToiQmFja2dyb3VuZENvbG9yIjtzOjA6IiI7czo4OiJDbGFzc0NzcyI7czowOiIiO3M6NToiT3JkcmUiO3M6MzoiMTAwIjtzOjg6Ik1lbnVIYXV0IjtzOjE6IjAiO3M6NzoiTWVudUJhcyI7czoxOiIwIjtzOjEzOiJNZW51UHJpbmNpcGFsIjtzOjE6IjEiO3M6MTE6Ik1lbnVTcGVjaWFsIjtzOjE6IjAiO3M6MTA6IkF1dG9TdWJHZW4iO3M6MToiMCI7czo1OiJUaXRsZSI7czowOiIiO3M6MTE6IkRlc2NyaXB0aW9uIjtzOjA6IiI7czo4OiJLZXl3b3JkcyI7czowOiIiO3M6NToiSW1hZ2UiO3M6MDoiIjtzOjg6IlRlbXBsYXRlIjtzOjE6IjAiO3M6OToiUGFnZVRpdHJlIjtzOjA6IiI7czoxNToiUGFnZURlc2NyaXB0aW9uIjtzOjA6IiI7czoxMDoiTWVudVBhcmVudCI7czoyOiIxNyI7czoxMDoiT2JqZWN0VHlwZSI7czo0OiJNZW51IjtzOjQ6Im5vdGUiO2k6MTA7czo2OiJNb2R1bGUiO3M6NzoiU3lzdGVtZSI7czo1OiJNZW51cyI7YTowOnt9fX19fQ==');
        $adminGrp->Set('Skin','AngularAdmin');
        $adminGrp->Save();




        //Verif de l'existance des roles nécéssaires
        $cliRole = Sys::getOneData('Systeme','Role/Title=INCIDENT_CLIENT');
        if(!$cliRole){
            $cliRole = genericClass::createInstance('Systeme','Role');
            $cliRole->Title = 'INCIDENT_CLIENT';
            $cliRole->Save();
        }
        $cliGrp->addParent($cliRole);
        $cliGrp->Save();

        $techRole = Sys::getOneData('Systeme','Role/Title=INCIDENT_TECHNICIEN');
        if(!$techRole){
            $techRole = genericClass::createInstance('Systeme','Role');
            $techRole->Title = 'INCIDENT_TECHNICIEN';
            $techRole->Save();
        }
        $techGrp->addParent($techRole);
        $techGrp->Save();

        $adminRole = Sys::getOneData('Systeme','Role/Title=INCIDENT_ADMINISTRATEUR');
        if(!$adminRole){
            $adminRole = genericClass::createInstance('Systeme','Role');
            $adminRole->Title = 'INCIDENT_ADMINISTRATEUR';
            $adminRole->Save();
        }
        $adminGrp->addParent($adminRole);
        $adminGrp->Save();


        //Verif de l'existance des access
        $cliAccess = Sys::getOneData('Systeme','Access/ObjectModule=IncidentClient&&ObjectClass=Incident');

        if(!$cliAccess){
            $cliAccess = genericClass::createInstance('Systeme','Access');
            $cliAccess->Titre = 'Accès Client/Incident';
            $cliAccess->ObjectModule = 'IncidentClient';
            $cliAccess->ObjectClass = 'Incident';
            $cliAccess->Alias = 'IncidentClient/Client/[!IncidentClientClient::Id!]/Incident';
            $cliAccess->addParent($cliRole);
            $cliAccess->Save();
        }

        $cliAccess2 = Sys::getOneData('Systeme','Access/ObjectModule=IncidentClient&&ObjectClass=DocumentClient');

        if(!$cliAccess2){
            $cliAccess2 = genericClass::createInstance('Systeme','Access');
            $cliAccess2->Titre = 'Accès Client/DocumentClient';
            $cliAccess2->ObjectModule = 'IncidentClient';
            $cliAccess2->ObjectClass = 'DocumentClient';
            $cliAccess2->Alias = 'IncidentClient/Client/[!IncidentClientClient::Id!]/DocumentClient';
            $cliAccess2->addParent($cliRole);
            $cliAccess2->Save();
        }

        parent::Check();
    }


    /**
     * Initilisation des variables globales disponibles pour la boutique
     */
    private function initGlobalVars(){
        if (!Sys::$User->Public){
            $usr = Sys::$User;
            //initialisation client si connecté
            $cli = Sys::getOneData('IncidentClient','Client/UserId='.$usr->Id);
            if ($cli){
                $GLOBALS["Systeme"]->registerVar("IncidentClientClient",$cli);
            }else{
                //test si Technicien
                $tech = Sys::$User->getOneChild('Technicien');
                if ($tech){
                    $GLOBALS["Systeme"]->registerVar("IncidentClientTechnicien",$tech);
                }
            }
        }
    }

}
