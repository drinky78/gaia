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

$id = intval($_POST['volontario']);
if (empty($id))
    $id = intval($_GET['volontario']);

if ($id<=0) {
    redirect('formazione.titoli.cerca&err=1');
    die;
}

$v = Volontario::id($id);

$tipoCorsi = TipoCorso::elenco();

$titoli = TitoloCorso::filtra([
    ['volontario', $id]
]);

?>
<div class="row-fluid">

    <div class="span12">
        <h2><i class="icon-plus-square icon-calendar muted"></i> Titoli di <?php echo $v->nomeCompleto() ?> [<?php echo $v->codiceFiscale ?>]</h2>
        <?php if (isset($_GET['err'])) { ?><p class="alert alert-block alert-danger">E' necessario inserire tutti i dati richiesti</p><?php } ?>
        <form action="?p=formazione.titoli.aggiungi" method="POST">
            <input value="<?php echo @$id ?>" name="volontario" type="hidden">
            <div class="alert alert-block alert-success">
                <div class="row-fluid">
                    <h4><i class="icon-question-sign"></i> Aggiungi un nuovo titolo per il volontario </h4>
                </div>
                <hr>
                <div class="row-fluid">
                    <div class="span4">
                        <label for="titolo"><i class="icon-certificate"></i> Titolo</label>
                    </div>
                    <div class="span8">
                        <select name="titolo">
                            <?php foreach ($tipoCorsi as $t) { ?>
                                <option value="<?php echo $t->id ?>"><?php echo $t->nome ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span4">
                        <label for="titolo"><i class="icon-certificate"></i> Eventuali affiancamenti</label>
                    </div>
                    <div class="span8">
                        <select name="affiancamenti">
                            <option value="0">NON ANCORA COMPLETATI</option>
                            <option value="1">SUPERATI CON SUCCESSO</option>
                        </select>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span4">
                        <label for="dataInizio"><i class="icon-calendar"></i> Data del titolo</label>
                    </div>
                    <div class="span8">
                        <input id="dataTitolo" class="span4" name="inizio" type="text" required>
                        <label>Inserire la data presente sul certificato emesso in seguito al conseguimento del titolo</label>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span4 offset4">
                        <button type="submit" class="btn btn-success">
                            <i class="icon-ok"></i>
                            Aggiungi titolo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>
<div class="row-fluid">

    <div class="span12">
        <?php $ttt = $me->titoliTipo($t); ?>
        <h3><i class="icon-list muted"></i> Titoli  <span class="muted"><?php echo count($titoli); ?> inseriti</span></h3>
        <table class="table table-striped">
            <tr>
                <th>Titolo</th>
                <th>Comitato</th>
                <th>Dettagli</th>
                <th>Azioni</th>
            </tr>
            <?php 
                foreach ( $titoli as $titolo ) { 
                    $corso = $titolo->corso();
                    $organizzatore = empty($corso) ? 'N/A' : $corso->organizzatore()->nome;
                  
                    $nomeTitolo =  (!$titolo->ruolo || !$titolo->qualifica) ? "Titolo con dati non corretti" : $titolo->qualifica()->nome.' '.$titolo->ruolo()->ruolo;
                    ?>
                    <tr>
                        <td><strong><?php echo $nomeTitolo ?></strong></td>
                        <td><strong><?php echo $organizzatore ?></strong></td>
                        <td><small>
                            <?php if ( $titolo->inizio ) { ?>
                            <i class="icon-calendar muted"></i>
                            <?php echo date('d-m-Y', $titolo->inizio); ?>
                            <br />
                            <?php } ?>
                            <?php if ( $titolo->fine ) { ?>
                            <i class="icon-time muted"></i>
                            <?php echo date('d-m-Y', $titolo->fine); ?>
                            <br />
                            <?php } ?>
                            <?php if ( $titolo->luogo ) { ?>
                            <i class="icon-road muted"></i>
                            <?php echo $titolo->luogo; ?>
                            <br />
                            <?php } ?>
                            <?php if ( $titolo->codice ) { ?>
                            <i class="icon-barcode muted"></i>
                            <?php echo $titolo->codice; ?>
                            <br />
                            <?php } ?>

                        </small></td>


                        <td>
                            <div class="btn-group">
                                <a  href="?p=formazione.titoli.cancella&volontario=<?php echo $v->id ?>&id=<?php echo $titolo->id; ?>" title="Cancella il titolo" class="btn btn-small btn-warning">
                                    <i class="icon-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php 
                } 
            ?>
        </table>

    </div>
</div>