<?php
// fiche printFinance
$vars['Annee'] = Cadref::$Annee;
$vars['CurrentMenu'] = Sys::$CurrentMenu;
if(Sys::$User->Admin && !$vars['CurrentMenu']){
    $oc = $o->getObjectClass();
    $vars['CurrentMenu'] = ['Titre' =>$oc->Description ];
}
$vars['identifier'] = 'CadrefFinance';

$tmp = array();
$gs = Sys::getData('Cadref','Annee');
foreach($gs as $g) $tmp[] = $g->Annee;
$vars['annees'] = $tmp;


