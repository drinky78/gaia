<?php
/*
 * ©2015 Croce Rossa Italiana
 */

/*
 * QUESTA PAGINA DEVE ESSERE ACCESSIBILE SOLO A: PRESIDENTE NAZIONALE, DTN OBIETTIVO 1 E SUOI DELEGATI
 */

$deleghe = $me->delegazioniAsHashMap(APP_OBIETTIVO);
if (!(sizeof($deleghe[OBIETTIVO_1]) > 0 || $me->admin())) {
    redirect('formazione.corsi');
    die;
}

controllaParametri(['volontario', 'titolo', 'inizio']);

$volontario = intval($_POST['volontario']);

if (empty($volontario)) {
    redirect("formazione.titoli.cerca&err=2");    
    die;
}

$data     = DT::createFromFormat('d/m/Y', trim($_POST["inizio"]));
if (empty($data)) {
    redirect("formazione.titoli.modifica&volontario=".$volontario."&err=3");    
    die;
}

$c = $err = null;

$l = new TitoloCorso();
$l->volontario 	= $volontario;
$l->titolo 	= intval($_POST['titolo']);

$l->inizio = $data->getTimestamp();

$data->add(new DateInterval('P1Y'));
$l->fine = $data->getTimestamp();

$l->codice 	= normalizzaNome(@$_POST['codice']);
    
redirect("formazione.titoli.modifica&volontario={$volontario}");
