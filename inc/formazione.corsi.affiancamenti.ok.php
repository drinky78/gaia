<?php
/*
 * ©2015 Croce Rossa Italiana
 */

paginaPresidenziale(null, null, APP_OBIETTIVO, OBIETTIVO_1);
controllaParametri(['id'], 'admin.corsi.crea&err');

$idoneitaDisc = filter_input(INPUT_POST, 'affiancamenti', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);


$c = null;
$affiancamenti = $daAggiungere = $daEliminare = [];

try {
    $c = Corso::id(intval($_POST['id']));
    
    if (!$c->modificabile() /*|| !$c->modificabileDa($me) */) {
        redirect('formazione.corsi.riepilogo&id='.$c->id.'&err=1');
    }

    if (empty($c) || !is_array($affiancamenti)) {
        throw new Exception('Manomissione');
    }
    
    if (!empty($_POST['affiancamenti'])) {
        if (is_array($_POST['affiancamenti'])) {
            $affiancamenti = array_merge($c->affiancamenti(), $c->affiancamentiPotenziali());

            // setta tutti i vecchi come da eliminare
            foreach ($affiancamenti as $i) {
                $daEliminare[$i->id] = true;
            }
            unset($affiancamenti); // non serve più e spreca solo memoria

            // cicla sui nuovi
            foreach ($_POST['affiancamenti'] as $id) {
                if (isset($daEliminare[$id])) {
                    // se il nuovo è anche tra i vecchi, lo toglie dalla lista di quelli da eliminare
                    unset($daEliminare[$id]);
                } else {
                    // se il nuovo non è tra i vecchi, lo aggiunge dalla lista di quelli da aggiungere
                    $daAggiungere[$id] = true;
                }
            }

            $daAggiungere = array_keys($daAggiungere);
            $daEliminare = array_keys($daEliminare);

            foreach ($daEliminare as $id) {
                PartecipazioneCorso::id($id)->cancella();
            }

            foreach ($daAggiungere as $id) {
                $docente = Volontario::id($id);

                // aggiungere verifica del fatto che sia effettivamente un docente

                $part = new PartecipazioneCorso();
                $part->aggiungi($c, $docente, CORSO_RUOLO_AFFIANCAMENTO);
            }
        } else {
            throw new Exception('Manomissione');
        }

        $c->aggiornaStato();
    }
} catch (Exception $e) {
    die($e->getMessage());
    redirect('admin.corsi.crea&err');
}


if (!empty($_POST['wizard'])) {
    redirect('formazione.corsi.discenti&id='.$c->id.'&wizard=1');
    die;
}

redirect('formazione.corsi.riepilogo&id='.$c->id);

?>