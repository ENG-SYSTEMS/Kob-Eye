<?php
$vars['Annee'] = Cadref::$Annee;
$vars['module'] = 'Cadref';
$vars['objecttype'] = 'Adherent';
$vars['controller'] = $vars['Url'];
$vars['function'] = 'PrintRecapitulatif';

$vars['identifier'] = $vars['module'].$vars['objecttype'];
$vars['url'] = $vars['module'].'/'.$vars['objecttype'];
?>