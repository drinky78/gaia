<?php
if (isset($volontari) && is_array($volontari) && !empty($volontari)) {
    $sel = '';
    foreach ($volontari as $i) {
        if (empty($sel)) {
            $sel .= ',';
        }
        if (is_int($sel)) {
            $sel .= $i;
        }
    }
}
if (!isset($maxDiscenti)) {
    $maxDiscenti = 1;
}
?>

<script type="text/javascript">
    $(document).ready( function () { 
        var value;
        var element = $(".chosen-select.volontari");
        var ruolo = element.data("ruolo");
        var qualifica = element.data("qualifica");
        var comitato = element.data("comitato");
        
        var select = $(".chosen-select.volontari");
        var input = null;
        var notfound = null;

        select.on('chosen:ready', function(event) {
                var $button = $('<button class="btn btn-sm btn-primary pull-right">cerca</button>');
                $(".chosen-select.volontari").next('.chosen-container').find('.search-field').append($button);
                
                input = $(".chosen-select.volontari").next('.chosen-container').find('input')[0];

                $button.on('click', function() {
                    
                    $button.addClass('loading');
                    $button.html('.....');
                    
                    var stato = '';

                    //var insertlink = '?p='+select.data('insert-page');

                    value = $(input).val();
                if (value.length < 1) {
                    return;
                }
                                
                    api('volontari:cerca', { query: value, perPagina: 80, ordine: 'selettoreDiscente', comitati: comitato, stato: stato }, function (x) {
                    select.children().remove('option:not(:selected)');
                        if (x.risposta.risultati.length) {
                    for (var i in x.risposta.risultati) {
                        select.append('<option value="'+x.risposta.risultati[i].id+'">'+x.risposta.risultati[i].nome + ' ' + x.risposta.risultati[i].cognome+'</option>');
                    }
                    select.trigger("chosen:updated");
                        } else {
                            $('.chosen-select.volontari + .chosen-container .no-results').html('NESSUN RISULTATO trovato per "'+value+'"');
                        }

                        $(input).val(value);
                    
                        $button.removeClass('loading');
                        $button.html('cerca');
                });

                })
            })
            .chosen({
                max_selected_options: <?php echo $maxDiscenti ?>, 
                no_results_text: "Premere CERCA per trovare un volontario",
                width: '100%'
            })
            .data('chosen')
            .container.on('keyup', function(event) {

                var input = $(this).find('input')[0];

                value = $(input).val();
                if (value.length < 1) {
                    return;
                }
/*
                var code = event.which;
                if (code==13) {
                    event.preventDefault();
                } else {
                    return;
                }
*/              
                
            });
            
    });
</script>