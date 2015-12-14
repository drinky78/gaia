$(document).ready(function () {

    $('#nuova-persona').on('submit', function () {

        api('aggiungi:civile', {
                "nome": $('#inputNome').val(),
                "cognome": $('#inputCognome').val(),
                "sesso": $('#inputSesso option:selected').val(),
                "cf": $('#inputCodiceFiscale').val(),
                "dnascita": $('#inputDataNascita').val(),
                "prnascita": $('#inputProvinciaNascita').val(),
                "conascita": $('#inputComuneNascita').val(),
                "coresidenza": $('#inputComuneResidenza').val(),
                "caresidenza": $('#inputCAPResidenza').val(),
                "prresidenza": $('#inputProvinciaResidenza').val(),
                "indirizzo": $('#inputIndirizzo').val(),
                "civico": $('#inputCivico').val(),
                "email": $('#inputEmail').val(),
                "cellulare": $('#inputCellulare').val()
            }, 
            function (x) {
                
                if (x.risposta && x.risposta.errore) {
                    alert(x.risposta.errore.info);
                    return;
                }
                
                if (x.risposta && x.risposta.codiceFiscale) {
                    $('#nuovo-utente').modal('hide');
//                    $('.chosen-select.discenti').each(function(idx, el) {
//                        $(el).append('<option value="'+x.risposta.id+'">'+x.risposta.nomeCompleto+'</option>');
//                    });
                }
            }
        );
        // recuperare i discenti già selezionati 
        // salvarli in un cookie?! per recuperarli al caricamento successivo dopo l'inserimento di una nuova persona

        return false;
    });

});
