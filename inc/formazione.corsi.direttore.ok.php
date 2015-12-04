<?php
/*
 * ©2015 Croce Rossa Italiana
 */
paginaPresidenziale(null, null, APP_OBIETTIVO, OBIETTIVO_1);

controllaParametri(['id','direttori'], 'admin.corsi.crea&err');

$c = $direttore = null;
try {
    $c = Corso::id(intval($_POST['id']));
    
    if (!$c->modificabile() /*|| !$c->modificabileDa($me)*/ ) {
        
        $geoComitato = GeoPolitica::daOid($c->organizzatore);
        
//        redirect('formazione.corsi.riepilogo&id='.$c->id.'&err=1');
        $m = new Email('crs/modificaPeriodoBuffer', "Invito ".$this->id);

        // inviare a regionale e/o nazionale
        $m->a = $geoComitato->regionale()->email;
        $m->a = $geoComitato->nazionale()->email;

        $m->_NOME = $this->volontario()->nomeCompleto();
        $m->_HOSTNAME = filter_input(INPUT_SERVER, "SERVER_NAME");
        $m->_CORSO = $c->nome();
        $m->_DATA = $c->inizio();
        $m->_ID = $this->id;
        $m->_MD5 = $this->md5;
        $m->invia();
    }

    $direttore = Volontario::id(intval($_POST['direttori'][0]));
    
    if (empty($c) || empty($direttore)) {
        throw new Exception('Manomissione');
    }
} catch (Exception $e) {
    redirect('admin.corsi.crea&err');
}

$partecipazione = new PartecipazioneCorso();
$partecipazione->aggiungi($c, $direttore, CORSO_RUOLO_DIRETTORE);


$c->direttore = $direttore->id;
$c->aggiornaStato();


if (!empty($_POST['wizard'])) {
    $tipoCorso = TipoCorso::id($c->tipo);
    
    if ($tipoCorso->giorni>1) {
        redirect('formazione.corsi.lezioni&id='.$c->id.'&wizard=1');
        die;
    } else {
        redirect('formazione.corsi.docenti&id='.$c->id.'&wizard=1');
        die;
    }
}

redirect('formazione.corsi.riepilogo&id='.$c->id);

?>
