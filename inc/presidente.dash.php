<?php

/*
 * ©2013 Croce Rossa Italiana
 */

paginaPresidenziale();
// caricaSelettore();


$comitati = $me->comitatiDiCompetenza();

?>

<div class="row-fluid">
    <div class="span3">
        <?php menuVolontario(); ?>
    </div>
    <div class="span9">

        
        <div class="row-fluid">
            
            <div class="span9">
                <h2>Salve, <?php if($me->admin()){?>Admin <?php }else{ ?> Presidente <?php } echo $me->cognome; ?>.</h2>
            </div>
            
            <div class="span3 allinea-destra">
                <br />
                <a href="?p=utente.supporto">Aiuto <i class="icon-question-sign"></i></a>
            </div>
            
        </div>
                    
        <div class="row-fluid">
            
            <div class="span3">
                <div class="btn-group btn-group-vertical span12">
                    <a href="?p=presidente.utenti" class="btn btn-primary btn-block">
                        <i class="icon-list"></i>
                        Elenco volontari
                    </a>
                    <a href="?p=presidente.titoli.ricerca" class="btn btn-block">
                        <i class="icon-search"></i>
                        Ricerca per titolo
                    </a>
                </div>
            </div>
            
            <div class="span6 allinea-centro">
                <img src="http://upload.wikimedia.org/wikipedia/it/thumb/4/4a/Emblema_CRI.svg/75px-Emblema_CRI.svg.png" />
            </div>
            
            
            <div class="span3">
                
                <table class="table table-striped table-condensed">
                
                    <tr><td>Num. comitati</td><td><?php echo count($comitati); ?></td></tr>
                    <tr><td>Num. volontari</td><td><?php echo $me->numVolontariDiCompetenza(); ?></td></tr>
                    
                </table>
                
                
            </div>
            

            
        </div>
        
        <hr />
        
        <p class="text-success"><i class="icon-info-sign"></i> <strong>Unità di competenza</strong> &mdash;
            Cliccare sul nome di una unità per modificarne <strong>delegati, obiettivi, responsabili, ecc.</strong>:</p>
        
        <div class="" id="comitati">
            
            <ul>
            <?php 

            $ricorsiva = function( $comitato ) use (&$ricorsiva) {
                ?>
                <li>
                    <a href="?p=presidente.comitato&oid=<?php echo $comitato->oid(); ?>">
                        <strong><?php echo $comitato->nomeCompleto(); ?></strong>
                    </a>
                    <?php if ($figli = $comitato->figli()) { ?>
                        <ul>
                            <?php foreach ( $figli as $figlio ) {
                                $ricorsiva($figlio);
                            } ?>
                        </ul>
                    <?php } ?>
                </li>
                <?php
            };

            foreach ( $me->entitaDelegazioni(APP_PRESIDENTE) as $c ) { 
                $ricorsiva($c);
            } ?>
            </ul>
            
        </div>
        
            


    </div>
</div>
            
