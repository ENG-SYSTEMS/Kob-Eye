<?php

// Récupération de l'adherent
$Minisite = Sys::getOneData("Parc", "MiniSite/Domaine=".Sys::$domain);
$LeClient = Sys::getOneData("Parc","Client/MiniSite/".$Minisite->Id);

$lAdherent = $LeClient->getOneChild("Adherent");
$lAdherent->Module="Vetoccitan";

$donnees = $lAdherent->Recup_Service($lAdherent);

$vars['Services']=$donnees;
//var_dump($vars['Services']);
