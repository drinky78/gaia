<?php
/*
 * ©2015 Croce Rossa Italiana
 */

paginaPresidenziale(null, null, APP_OBIETTIVO, OBIETTIVO_1);
controllaParametri(['id','docenti'], 'formazione.corsi.docenti&err');

$c = null;
$docenti = $daAggiungere = $daEliminare = [];

try {
    $c = Corso::id(intval($_POST['id']));
    
    if (!$c->modificabile() /*|| !$c->modificabileDa($me) */) {

        $geoComitato = GeoPolitica::daOid($c->organizzatore);

//        redirect('formazione.corsi.riepilogo&id='.$c->id.'&err=1');
        $m = new Email('crs/modificaPeriodoBuffer', "Invito " . $this->id);

        // inviare a regionale e/o nazionale
        $m->a = $geoComitato->regionale()->email;
        $m->a = $geoComitato->nazionale()->email;

        $m->_NOME = $this->volontario()->nomeCompleto();
        $m->_HOSTNAME = filter_input(INPUT_SERVER, "SERVER_NAME");
        $m->_CORSO = $c->nome();
        $m->_DATA = $c->inizio();
        $m->_ID = $this->id;
        $m->_MD5 = $this->md5;
        $m->invia();
    }

    if (empty($c) || !is_array($docenti)) {
        throw new Exception('Manomissione');
    }
    
    if (is_array($_POST['docenti']) && !empty($_POST['docenti'])) {
        $docenti = array_merge($c->docenti(), $c->docentiPotenziali());

        // setta tutti i vecchi come da eliminare
        foreach ($docenti as $d) {
            $daEliminare[$d->id] = true;
        }
        unset($docenti); // non serve più e spreca solo memoria

        // cicla sui nuovi
        foreach ($_POST['docenti'] as $d) {
            if (isset($daEliminare[$d])) {
                // se il nuovo è anche tra i vecchi, lo toglie dalla lista di quelli da eliminare
                unset($daEliminare[$d]);
            } else {
                // se il nuovo non è tra i vecchi, lo aggiunge dalla lista di quelli da aggiungere
                $daAggiungere[$d] = true;
            }
        }

        $daAggiungere = array_keys($daAggiungere);
        $daEliminare = array_keys($daEliminare);

        foreach ($daEliminare as $d) {
            PartecipazioneCorso::id($d)->cancella();
        }
        
        foreach ($daAggiungere as $d) {
            $docente = Volontario::id($d);
            
            // aggiungere verifica del fatto che sia effettivamente un docente
            
            $part = new PartecipazioneCorso();
            $part->aggiungi($c, $docente, CORSO_RUOLO_DOCENTE);
        }   

    } else {
        throw new Exception('Manomissione');
    }
    
    $c->aggiornaStato();
    
} catch (Exception $e) {
    die($e->getMessage());
    redirect('admin.corsi.crea&err');
}


if (!empty($_POST['wizard'])) {
    redirect('formazione.corsi.affiancamenti&id='.$c->id.'&wizard=1');
    die;
}

redirect('formazione.corsi.riepilogo&id='.$c->id);

?>
