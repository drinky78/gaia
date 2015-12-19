<?php

/*
 * ©2013 Croce Rossa Italiana
 */

paginaAdmin();

$persone = PartecipazioneCorso::istruttoriPotenziali();
               ?>

<script type="text/javascript"><?php require './assets/js/presidente.utenti.js'; ?></script>
<br/>
<div class="row-fluid">
    <div class="span8">
        <h2>
            <i class="icon-certificate muted"></i>
            Istruttori Potenziali 
        </h2>
    </div>
    
    <div class="span4 allinea-destra">
        <div class="input-prepend">
            <span class="add-on"><i class="icon-search"></i></span>
            <input autofocus required id="cercaUtente" placeholder="Cerca Titolo..." type="text">
        </div>
    </div>
</div>

<hr />

<div class="row-fluid">
 <div class="span12">
     <table class="table table-striped table-bordered table-condensed" id="tabellaUtenti">
        <thead>
            <th>Nome</th>
            <th>Qualifiche</th>
            <th>Ruolo</th>
            <th>Azioni</th>
        </thead>
        <?php
        foreach($persone as $p){
            ?>
            <tr>
                <td><?php echo $p->nome; ?> <?php echo $p->cognome; ?></td>
                <td><?php echo $p->qualifiche(); ?></td>
                <td><?php echo $p->ruoli(); ?></td>
                <td>
                    <div class="btn-group">
                        <!--
                        <a class="btn btn-small btn-danger" onClick="return confirm('Vuoi veramente cancellare questo titolo ?');" href="?p=admin.titolo.cancella&id=<?php echo $c->id; ?>" title="Cancella Titolo">
                            <i class="icon-trash"></i> Cancella
                        </a>
                        <a class="btn btn-small" href="?p=admin.titolo.modifica&id=<?php echo $c->id; ?>" title="Modifica Titolo">
                            <i class="icon-edit"></i> Modifica
                        </a>
                        <a class="btn btn-small btn-primary" href="?p=admin.titolo.volontari&id=<?= $c->id; ?>">
                            <i class="icon-group"></i> Certificati
                        </a>
                        -->
                    </div>
                </td>
            </tr>
            
            
            
            <?php }
            
            ?>
        </table>

    </div>
    
</div>


