<?php
    $e = genericClass::createInstance('Systeme','Event');
    $pr = json_decode(file_get_contents('php://input'));
    $vars['items'] = $e->pollAll($pr->pollStart,$pr->pollInterval,$pr->pollDuration);
    $vars['EvCount'] = sizeof($vars['items']['Ev']);
    $vars['AlCount'] = sizeof($vars['items']['Au']);
    if (!is_array($vars['items']['Ev']))$vars['items']['Ev'] = array();
    if (!isset($vars['items']['Au']))$vars['items']['Ev'] = array();
    if ($pr->pollStart=="0")$lastSearch = microtime(true);
    else $lastSearch = $pr->pollStart;
    $vars['Now'] = (sizeof($vars['items']['Ev']))?$vars['items']['Ev'][sizeof($vars['items']['Ev'])-1]['MicroTime']:$lastSearch;
