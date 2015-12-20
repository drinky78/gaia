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

$inizio     = DT::createFromFormat('d/m/Y', trim($_POST["inizio"]));
if (empty($inizio)) {
    redirect("formazione.titoli.modifica&volontario=".$volontario."&err=3");    
    die;
}

$c = $err = null;

$t = TipoCorso::id(intval($_POST['titolo']));
if (empty($t)) {
    redirect("formazione.titoli.modifica&volontario={$volontario}&err=4");
    die;
}


if ($_POST['affiancamenti']) {
    if (empty(intval($t->ruoloAttestatoPostAffiancamenti))) {
        redirect("formazione.titoli.modifica&volontario={$volontario}&err=5");
        die;
    }
} else {
    if (empty(intval($t->ruolo))) {
        redirect("formazione.titoli.modifica&volontario={$volontario}&err=6");
        die;
    }
}
if (empty(intval($t->qualifica))) {
    redirect("formazione.titoli.modifica&volontario={$volontario}&err=7");
    die;
}

$l = new TitoloCorso();
$l->volontario 	= $volontario;

$l->qualifica 	= $t->qualifica;

if ($_POST['affiancamenti']) {
    $l->ruolo 	= $t->ruoloAttestatoPostAffiancamenti;
} else {
    $l->ruolo 	= $t->ruoloAttestato;
}

$l->inizio = $inizio->getTimestamp();

$inizio->add(new DateInterval('P1Y'));
$l->fine = $inizio->getTimestamp();

$l->codice 	= normalizzaNome(@$_POST['codice']);
    
redirect("formazione.titoli.modifica&volontario={$volontario}");
