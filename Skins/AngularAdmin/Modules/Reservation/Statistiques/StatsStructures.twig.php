<?php
$data = json_decode(file_get_contents('php://input'),true);
if(empty($data)) $data = $_GET;
if (isset($data['state'])){
    $vars['state'] = $data['state'];
}else{
    $vars['state'] = 0;
}
if ($vars['state'] == 0){
    $vars['client'] = Sys::getData('Reservation','Client',0,100000);
    $html = 'Année : <input type="text" class="form-control" ng-model="StatsStructures.args.Date"><br>';
    $tuc = '';
    foreach ($data['client'] as $items){
        $tuc.= $items['Nom'];
    }
    $html .=  KeTwig::processTemplates(KeTwig::callModule('Reservation/Statistiques/Select'));
    $html .=  '<button ng-click="onSelecClient();">Valider</button>';
    $ret = array(
        'html'=>$html,
        'client'=>$vars['client'],
        'state'=>$data['state']
    );
    $vars['return'] = json_encode($ret);
}elseif ($vars['state'] == 1){
    $client = serialize($data['client']);
    $uid = uniqid();
    file_put_contents('/tmp/bidule'.$uid.'.cli',$client);

//    setcookie('clientSelect',$client,3600,'/',Sys::$domain);
    $html = '<button ng-click="reInitStatsStructures();">Retour à la selection</button>
    <iframe src="/Reservation/Statistiques/StatsStructures.pdf?state=3&Date='.$data['Date'].'&uid='.$uid.'" frameborder="0" style="width:100%;height:900px;"></iframe>';
    $ret = array(
        'html'=>$html,
        'client'=>$data['client'],
        'state'=>$data['state']
    );
    $vars['return'] = json_encode($ret);

}elseif ($vars['state'] == 3){
    $choix = file_get_contents('/tmp/bidule'.$data['uid'].'.cli');
    $choix = unserialize($choix);
    $Date = $data['Date'];
    $DateDebut = mktime(0, 0, 0, 1, 1, $Date);
    $DateFin = mktime(23, 59, 59, 12, 31, $Date);



    $html = '<table border="1" cellspacing="0" cellspadding="0" style="max-width:700px">';
    $html .= '<tr><th colspan="4" style="font-size:14px;font-weight:bold;text-align:center;">Liste des réservations du '.date('d/m/Y',$DateDebut).' au '.date('d/m/Y',$DateFin).'</th>   
                </tr>
                <tr>
                    <td style="text-align:center;font-weight:bold;">Structure</td>
                    <td style="text-align:center;font-weight:bold;">Ville</td>
                    <td style="text-align:center;font-weight:bold;">Nombre de réservation </td>
                    <td style="text-align:center;font-weight:bold;">Nombre de personnes </td>
                </tr>';

    $tab = array();
    $TotResa = 0;
    $TotPers = 0;
    $genre = array();
    foreach( $choix as $client ){
        $NbResa = 0;
        $NbPers = 0;
        $objCli = genericClass::createInstance('Reservation','Client');
        $objCli->initFromId($client);
        $tab = array();
        $reservations = $objCli->getChildren('Reservations');
        foreach ($reservations as $reserv) {
            $evenements = $reserv->getParents('Evenement');
            foreach ($evenements as $event) {
                if($event->DateDebut<$DateDebut || $event->DateDebut>$DateFin) continue;
                $sp = $event->getOneParent('Spectacle');
                $genre[] = $sp->Genre;

                $cpt = Sys::getCount('Reservation', 'Reservations/' . $reserv->Id . '/Personne');
                $NbResa++;
                $NbPers += $cpt;
                $genre[$sp->Genre] = $NbPers;
            }
        }
        $html .= '<tr>
                    <td style="width:300px">'.$objCli->Nom.'</td>
                    <td>'.$objCli->Ville.'</td>
                    <td>'.$NbResa.'</td>
                    <td>'.$NbPers.'</td>
                    </tr>';
        $TotResa += $NbResa;
        $TotPers += $NbPers;
    }
    $html .= '<tr style="width:190mm;font-size:10px;background-color:#ccc;">
                <td colspan="2" style="text-align:right;font-size:20px;font-weight:bold;color:#ff0000;padding:20px;">Total GÉNÉRAL</td>
                <td style="text-align:right;font-weight:bold;padding-right:10px;font-size:16px;">'.$TotResa.'</td>
                <td style="text-align:right;font-weight:bold;padding-right:10px;font-size:16px;">'.$TotPers.'</td>
            </tr>
            <tr style="width:190mm;font-size:10px;">
				<td colspan="4" style="text-align:center;font-size:12px;font-weight:bold;color:#000;padding:5px;">Répartition par genre</td>
			</tr>
            ';

//    $nombre = array_count_values($genre);
//    foreach ($genre as $value){
//        $nombre[$value]++;
//    }

    $test = print_r($genre,true);
//    $totNbGenre = sizeof($nombre);
//    $test = print_r($genre,true);

    $html .= '<tr><td>'.$test.'</td></tr>';

    $html .= '</table>';

    // Creation du pdf
    include $_SERVER['DOCUMENT_ROOT']."/Class/Lib/HTML2PDF.class.php";
    $tes = new HTML2PDF();
    $tes->writeHTML('<page pageset="old" backtop="14mm" backbottom="1mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
				'.$html.'
			</page>');
    ob_get_clean();
    $tes->Output('MailsStatsStructures.pdf');
    ob_end_clean();
//    print_r($tutu);
}


