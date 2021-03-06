<?php
session_write_close();
$info = Info::getInfos($vars['Query']);

$o = genericClass::createInstance($info['Module'],$info['ObjectType']);
//$vars['fields'] = $o->getElementsByAttribute('list','',true);
$vars['functions'] = $o->getFunctions();
$vars['fichefields'] = $o->getElementsByAttribute('fiche','',true);
if (!is_object(Sys::$CurrentMenu) && Sys::$User->Admin){
    $vars['fichefields'] = $o->getElementsByAttribute('','',true);
}

foreach ($vars['fichefields'] as $k=>$f){
    if ($f['type']=='fkey'&&$f['card']=='short'){
        $vars['fichefields'][$k]['link'] = Sys::getMenu($f['objectModule'].'/'.$f['objectName']);

        if ($vars['fichefields'][$k]['link']==$f['objectModule'].'/'.$f['objectName'])
            $vars['fichefields'][$k]['link'] = false;
    }
}
$vars['fields'] = $vars['fichefields'];


$vars['formfields'] = $o->getElementsByAttribute('form','',true);
$vars['CurrentMenu'] = Sys::$CurrentMenu;
$vars["CurrentObj"] = genericClass::createInstance($info['Module'],$info['ObjectType']);
$vars["ObjectClass"] = $vars["CurrentObj"]->getObjectClass();
$vars['operation'] = $vars['ObjectClass']->getOperations();
$childs = $vars["ObjectClass"]->getChildElements();
$vars["ChildrenElements"] = array();

foreach ($childs as $child){
    if (
        //test role
        ((!isset($child['hasRole'])||Sys::$User->hasRole($child['hasRole']))&&
            //test hidden
            !isset($child['childrenHidden'])&&!isset($child['hidden']))
        //test admin
        || (!is_object(Sys::$CurrentMenu) && Sys::$User->Admin))
        array_push($vars["ChildrenElements"],$child);
}
$vars["Interfaces"] = $vars["ObjectClass"]->getInterfaces();
$vars['identifier'] = $info['Module'] . $info['ObjectType'];
if (is_object(Sys::$CurrentMenu))
    $vars['CurrentUrl'] = Sys::$CurrentMenu->Url;
else $vars['CurrentUrl'] = $vars['Query'];


$vars['browseable'] = $vars["ObjectClass"]->browseable;
$vars['CurrentObjQuery'] = $vars['Path'];

$vars['formPath'] = 'Systeme/Utils/Form';

if (!isset($info['ObjectType'])) {
    $tab = explode('/', $info['Query']);
    array_push($tab, 'Form');
} else {
    $tab = array($info['Module'], $info['ObjectType'], 'Form');
}
$blinfo = Bloc::lookForInterface($tab, 'Skins/AngularAdmin/Modules', true);
if(strpos($blinfo, '/'.$info['Module'].'/')) {
    $p = strpos($blinfo, 'Modules/') + strlen('Modules/');
    $vars['formPath'] = substr(trim($blinfo, '.twig'), $p);
}

?>