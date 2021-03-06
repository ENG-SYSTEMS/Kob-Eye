<?php
session_write_close();
$tmsStart = time() + 3600;

$tache = Sys::getOneData('AbtelGestion', 'Entite/Nom=taches');
$fields = $tache->getChildren('Champ');
$tFields = array();
foreach ($fields as $f) {
    $tFields[] = $f->Nom;
}

$apiKey = '3a497a64e0-5c13b60a2e9e8';


//Ouverture connection pdo
$sql_handle = new PDO('mysql:host=127.0.0.1;dbname=gestion', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$sql_handle->query("SET AUTOCOMMIT=1");
$sql_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$reqTickets = 'SELECT * FROM taches WHERE CreatTms > \'2018010100000000\' ORDER BY NumeroTicket;';//$reqTickets = 'SELECT * FROM taches WHERE NumeroTicket > \'I00487\' ORDER BY NumeroTicket;';
$q = $sql_handle->query($reqTickets);
$tickets = $q->fetchAll(PDO::FETCH_ASSOC);

//Clean mémoire
$sql_handle = null;
$q = null;

//on note la dernière fois qu'on a checké
file_put_contents('lastCheckTask.date', time());

//Ouverture connection curl
$curl_handle = curl_init('http://api.gestion.abtel.fr');
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
$data = json_encode(array('API_KEY' => $apiKey, 'login' => 'api_gestion', 'pass' => 'D4nsT0n208'));
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
);
curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);

$ret = json_decode(curl_exec($curl_handle), true);
$api_token = $ret['auth_token'];

$curl_handle = null;
$ret = null;

$cpt = count($tickets);
if ($cpt) {
    $cptr = 0;
    $pids = array();
    $statuses = array();
    foreach ($tickets as $t) {
        $cptr++;
        echo date('H:i:s', time() - $tmsStart) . ' > ******** ' . $cptr . ' / ' . $cpt . ' : ' . $t['NumeroTicket'] . ' ********' . PHP_EOL;
        switch ($pid = pcntl_fork()) {
            case -1:
                echo date('H:i:s', time() - $tmsStart) . ' > Erreur lors de la création du process pour le client ' . $c['Code'] . PHP_EOL;
                file_put_contents('/tmp/erreurclient', $c['Code'] . PHP_EOL, 8);
                // @fail
                break;
            case 0:
                // @child: Include() misbehaving code here
                $GLOBALS['Systeme']->connectSQL(true);
                Sys::$Modules['Systeme']->Db->clearLiteCache();
                unset($tickets);

                $props = array();
                foreach ($tFields as $tf) {
                    if (!empty($t[$tf]) || $t[$tf] === '0')
                        $props[$tf] = $t[$tf];
                }

                $url = 'http://api.gestion.abtel.fr/gestion/tache';
                $method = 'POST';
                //Ouverture connection curl
                $curl_handle = curl_init($url);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, $method);
                $data = json_encode(array('API_KEY' => $apiKey, 'AUTH_TOKEN' => $api_token, "params" => array('data' => $props)));
                curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data))
                );
                $rettemp = curl_exec($curl_handle);
                $ret = json_decode($rettemp, true);
                unset($rettemp);

                if ($ret && $ret['success']) {
                    echo date('H:i:s', time() - $tmsStart) . ' >Ticket ' . $t['NumeroTicket'] . ' créé avec succès' . PHP_EOL;
                } else {
                    $err = true;
                    if ($ret && $ret["error_description"] && is_array($ret["error_description"])) {
                        foreach ($ret["error_description"] as $err) {
                            if ($err['Prop'] == 'NumeroTicket' && strpos($err['Message'], "__ALREADY_EXISTS__")) { //Cas ou le ticket exsite déjà
                                echo date('H:i:s', time() - $tmsStart) . ' > Ticket ' . $t['NumeroTicket'] . ' déjà existant, mise à jour.' . PHP_EOL;
                                $url = 'http://api.gestion.abtel.fr/gestion/tache/' . rawurlencode($t['NumeroTicket']);
                                $method = 'PATCH';
                                curl_setopt($curl_handle, CURLOPT_URL, $url);
                                curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, $method);
                                $data = json_encode(array('API_KEY' => $apiKey, 'AUTH_TOKEN' => $api_token, "params" => array('data' => $props)));
                                curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',
                                        'Content-Length: ' . strlen($data))
                                );
                                $rettemp = curl_exec($curl_handle);
                                $ret2 = json_decode($rettemp, true);
                                if ($ret2 && !empty($ret2['success'])) {
                                    echo date('H:i:s', time() - $tmsStart) . ' > Ticket ' . $t['NumeroTicket'] . ' mis à jour avec succès' . PHP_EOL;
                                    $err = false;
                                } else {
                                    $ret = $ret2;
                                }
                                break;
                            }
                        }
                    }
                    if ($err) {
                        echo date('H:i:s', time() - $tmsStart) . ' > Erreur lors de la création du client ' . $t['NumeroTicket'] . PHP_EOL;
                        file_put_contents('/tmp/erreurticket', $t['NumeroTicket'] . PHP_EOL, 8);
                        //print_r($url); echo PHP_EOL;
                        //print_r($data); echo PHP_EOL;
                        //print_r($ret);
                        file_put_contents('/tmp/erreurticketDetail', '+++++++++++++++++++++ ' . date('H:i:s', time()) . '  +++++++++++++++++++++++++++  ' . $t['NumeroTicket'] . PHP_EOL, 8);
                        file_put_contents('/tmp/erreurticketDetail', print_r($url, true) . PHP_EOL, 8);
                        file_put_contents('/tmp/erreurticketDetail', print_r($data, true) . PHP_EOL, 8);
                        file_put_contents('/tmp/erreurticketDetail', print_r($ret, true) . PHP_EOL, 8);
                        file_put_contents('/tmp/erreurticketDetail', PHP_EOL, 8);
                        echo PHP_EOL;
                    }
                }
                curl_close($curl_handle);

                exit;
                break;

            default:
                $GLOBALS['Systeme']->connectSQL(true);
                $pids[$pid] = true;
                $statuses[$pid] = null;

                // @parent
                break;
        }

        while (count($pids) >= 50) {
            usleep(50);
            while ($dPid = pcntl_waitpid(-1, $statuses[$pid], WNOHANG)) {
                if ($dPid == -1) break;
                //echo 'Fin : '.$dPid;
                unset($pids[$dPid]);
            }
        }
    }

    while (count($pids) > 0) {
        usleep(50);
        while ($dPid = pcntl_waitpid(-1, $statuses[$pid], WNOHANG)) {
            if ($dPid == -1) break;
            //echo 'Fin : '.$dPid.PHP_EOL;
            unset($pids[$dPid]);
        }
    }

}


