<?php

/*
 * (c)2013 Croce Rossa Italiana
 */

require 'core.inc.php';

ignoraTransazione();

/* Controlla che l'autopull sia attivo */
if (!$conf['autopull']['abilitato']) {
    die('Autopull disabilitato da configurazione.');
}

$GIT_BIN = $conf['autopull']['bin'];
$REMOTE  = $conf['autopull']['origin'];
$BRANCH  = $conf['autopull']['ramo'];

header('Content-Type: text/plain');

$gito = [];
exec("$GIT_BIN fetch $REMOTE 2>&1; $GIT_BIN checkout -q $REMOTE/$BRANCH 2>&1; $GIT_BIN log -1 2>&1;", $gito);
$output = date('d-m-Y H:i:s') . "\n\n" . print_r($gito, true);

/* Salva nel file di log */
file_put_contents('upload/log/autopull.txt', $output);

/* Output direttamente a video per logging di cronjob */
var_dump($output);
