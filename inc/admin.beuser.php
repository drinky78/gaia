<?php

/*
 * ©2013 Croce Rossa Italiana
 */

paginaAdmin();

$id = $_GET['id'];
$sessione->utente = $id;
redirect('utente.me');

?>
