<?php

/*
 * ©2014 Croce Rossa Italiana
 */

paginaApp([APP_AUTOPARCO , APP_PRESIDENTE]);

controllaParametri(array('id'), 'autoparco.veicoli&err');
$veicolo = $_GET['id'];
$veicolo = Veicolo::id($veicolo);

$manutenzione = new Manutenzione();

$manutenzione->veicolo = $veicolo->id;
$manutenzione->intervento = $_POST['inputDescrizione'];

$tIntervento = @DateTime::createFromFormat('d/m/Y', $_POST['inputData']);
$tIntervento = @$tIntervento->getTimestamp();
$manutenzione->tIntervento = $tIntervento;

$manutenzione->tRegistra = time();
$manutenzione->pRegistra = $me;
$manutenzione->km = $_POST['inputKm'];
$manutenzione->tipo = $_POST['inputTipo'];

$costo = (float) $_POST['inputCosto'];
$costo = round($costo, 2);
$manutenzione->costo = $costo;

$manutenzione->fattura = $_POST['inputFattura'];
$manutenzione->azienda = $_POST['inputAzienda'];

if ( $veicolo->fermoTecnico() ){
	$fermotecnico = Fermotecnico::id($veicolo->fermoTecnico());
	$fermotecnico->fine  = time();
	$fermotecnico->pFine = $me;
	$fermotecnico->tFine = time();

	$veicolo->fermotecnico = null;

}

redirect('autoparco.veicoli&manOk');