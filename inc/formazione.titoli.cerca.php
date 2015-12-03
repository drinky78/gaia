<?php
error_reporting(E_ALL);
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

caricaSelettoreDiscente([
    'max_selected_options' => $maxDirettori,
    'no_results_text' => 'Ricerca volontario in corso...',
]);

?>
<div class="row-fluid">

    <div class="span8">
        <h2><i class="icon-plus-square icon-calendar muted"></i> Assegnazione titoli Formazione</h2>
        <form action="?p=formazione.titoli.modifica" method="POST"><!-- come mai il metodo GET non manda alla action giusta?! -->
            <div class="alert alert-block alert-success">
                <div class="row-fluid">
                    <h4><i class="icon-question-sign"></i> Ricerca volontario per gestione titoli area Formazione</h4>
                </div>
                <hr>
                <div class="row-fluid">
                    <div class="span4">
                        <label for="dataFine"><i class="icon-user"></i> Volontario</label>
                    </div>
                    <div class="span8">
                        <select name="volontario"
                                data-insert-page="formazione.titoli.modifica"
                                data-placeholder="Scegli un volontario..." 
                                multiple 
                                class="chosen-select discenti">
                        </select>
                        <span>Inserisci il testo necessario per ricercare il volontario (nome, cognome, email o codice fiscale),<br/>
                            premi INVIO per aggiornare la lista e scegli un volontario dalla lista che appare.</span><br/>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span4 offset4">
                        <button type="submit" class="btn btn-success">
                            <i class="icon-ok"></i>
                            Procedi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="span4">
        <h2><i class="icon-plus-square icon-calendar muted"></i> Opzioni</h2>
    </div>
    
</div>