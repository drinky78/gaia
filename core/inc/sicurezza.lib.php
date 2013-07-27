<?php

/*
 * ©2012 Croce Rossa Italiana
 */

function criptaPassword ( $password ) {
    $password = sha1 ( md5 ( $password ) . '@CroceRossa' );
    return $password;
}

function debugOnly() {
    global $conf;
    if (!$conf['debug']) {
        throw new Errore(1009);
    }
}

/*
 * Usare per proteggere in una pagina i dati sensibili
 * di un volontario
 */
function proteggiDatiSensibili( $volontario, $app = [APP_PRESIDENTE] ) {
    global $me;
    $comitati = $me->comitatiApp($app);
    foreach ( $volontario->comitati() as $comitato ) {
        if (in_array($comitato, $comitati)) {
            return true;
        }
    }
    redirect('errore.permessi&cattivo');
}