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

controllaParametri(['id', 'volontario']);

$id = intval($_GET['id']);
$volontario = intval($_GET['volontario']);

if (empty($id) || empty($volontario)) {
    redirect("formazione.titoli.cerca&err=2");    
    die;
}

$t = TitoloCorso::id($id);

if ($t->volontario == $volontario)
    $t->cancella ();

redirect("formazione.titoli.modifica&volontario={$volontario}");
