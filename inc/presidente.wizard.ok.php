<?php

/*
 * ©2013 Croce Rossa Italiana
 */


controllaParametri(array('oid'));

$c = $_POST['oid'];
$c = GeoPolitica::daOid($c);

paginaApp([APP_PRESIDENTE], $c);

// non dobbiamo permettere modifiche del nome del comitato
// $c->nome        =   normalizzaNome($_POST['inputNome']);
$c->telefono    =   maiuscolo($_POST['inputTelefono']);
$c->email       =   minuscolo($_POST['inputEmail']);
$c->fax         =   maiuscolo($_POST['inputFax']);
if (!$c instanceof Comitato) {
	if(!$c->cf() || $me->admin())
		$c->cf 			=   $_POST['inputCF'];
	if(!$c->piva() || $me->admin())
		$c->piva 		=   $_POST['inputPIVA'];
}

$ricerca  = $_POST['inputIndirizzo'] . ', ';
$ricerca .= $_POST['inputCivico'] . ' ';
$ricerca .= $_POST['inputCAP'] . ' ';
$ricerca .= $_POST['inputComune'] . ' (';
$ricerca .= $_POST['inputProvincia'] . ')';

$g = new Geocoder($ricerca);
$r = $g->risultati[0];

$c->indirizzo   = $r->via;
$c->civico      = $r->civico;
$c->cap         = $r->cap;
$c->comune      = $r->comune;
$c->provincia   = $r->provincia;
$c->formattato  = $r->formattato;

$c->localizzaStringa($c->formattato);

redirect("presidente.wizard&oid={$c->oid()}&ok");
