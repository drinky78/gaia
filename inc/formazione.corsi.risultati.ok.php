<?php
/*
 * ©2015 Croce Rossa Italiana
 */
paginaPresidenziale(null, null, APP_OBIETTIVO, OBIETTIVO_1);

controllaParametri(['id','discIdoneita','discAffiancamenti'], 'formazione.corsi.risultati&err');

$idoneitaDisc = filter_input(INPUT_POST, 'discIdoneita', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$idoneitaAff = filter_input(INPUT_POST, 'affIdoneita', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$scrittoDisc = filter_input(INPUT_POST, 'discScritto', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$praticaDisc = filter_input(INPUT_POST, 'discPratica', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$presidioDisc = filter_input(INPUT_POST, 'discPresidio', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$noteDisc = filter_input(INPUT_POST, 'discNote', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$affiancamentiDisc = filter_input(INPUT_POST, 'discAffiancamenti', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$segnalazioniDisc = filter_input(INPUT_POST, 'discSegnalazioni', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$c = null;
try {
    $c = Corso::id(intval($_POST['id']));
    
//    $c->chiudi();
//    die;
    
    // controllare che l'utente attuale sia il direttore del corso 

    $size = $c->numeroDiscenti();
    if (    empty($c) 
            || !is_array($idoneitaDisc) 
            || !is_array($affiancamentiDisc)
            || sizeof($idoneitaDisc) != $size
            || sizeof($affiancamentiDisc) != $size 
            || array_keys($affiancamentiDisc) != array_keys($idoneitaDisc)
       ) {
        throw new Exception('Manomissione');
    }
    
    if (!$c->finito()) {
        redirect('formazione.corsi.riepilogo&id='.$c->id.'&err='.CORSO_ERRORE_NON_ANCORA_CONCLUSO);
    }

} catch (Exception $e) {
    die($e->getMessage());
    redirect('formazione.corsi.crea&err');
}

$now = new DT();
$docenti = $c->docenti();
$idDocenti = array();
foreach ($docenti as $i) {
    $idDocenti[] = $i->volontario;
}
unset($docenti);

$err = 0;
foreach ($idoneitaDisc as $volontario => $risultato) {
    if ($risultato==CORSO_RISULTATO_NESSUNO ||
        intval($volontario)<=0
        )
        $err++;
}

if (!empty($idoneitaAff)) {
    foreach ($idoneitaAff as $volontario => $risultato) {
        if ($risultato==CORSO_RISULTATO_NESSUNO ||
            intval($volontario)<=0
            )
            $err++;
    }
}
if ($err) {
    redirect('formazione.corsi.risultati&id='.$c->id.'&err='.CORSO_ERRORE_RISULTATI_NON_COERENTI);
    die;
}

foreach ($idoneitaDisc as $volontario => $risultato) {
    $r = new RisultatoCorso();
    $r->corso = $c->id;
    $r->volontario = intval($volontario);
    $r->idoneita = intval($risultato);
    $r->affiancamenti = ($r->idoneita >= CORSO_RISULTATO_IDONEO) ? intval($affiancamentiDisc[$volontario]) : 0;
    
    if (!empty($segnalazioniDisc[$volontario]) && is_array($segnalazioniDisc[$volontario])) {

        $size = sizeof($segnalazioniDisc[$volontario]);
        for ($idx = 0; $idx < $size; ++$idx) {
            
            if (!in_array(intval($segnalazioniDisc[$volontario][$idx]), $idDocenti) ) {
                throw new Exception('Manomissione');
            }
            
            $r->{'segnalazione_0'.($idx+1)} = intval($segnalazioniDisc[$volontario][$idx]);
        }
    }
    $r->timestamp = $now->getTimestamp();
    $r->note = $r->note  . "\r\n\r\nProva scritta: ".@$scrittoDisc[$volontario].
            "\r\nProva pratica: ".@$praticaDisc[$volontario].
            "\r\nUtilizzo presidio: ".@$presidioDisc[$volontario].
            "\r\n".@$noteDisc[$volontario];
}

if (!empty($idoneitaAff)) {
    foreach ($idoneitaAff as $volontario => $risultato) {
        $r = new RisultatoCorso();
        $r->corso = $c->id;
        $r->volontario = intval($volontario);
        $r->idoneita = intval($risultato);
        $r->affiancamenti = -1; // convenzione per determinare che si tratta di un affiancamento
        $r->timestamp = $now->getTimestamp();
        $r->note = $r->note  . "";
    }
}

/*
$c->stato = CORSO_S_DA_ELABORARE;
*/
$c->chiudi();

redirect('formazione.corsi.riepilogo&id='.$c->id);

?>
