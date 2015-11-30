$(document).ready( function() {
    
    var limite = new Date();
    limite.setDate( limite.getDate() + minDateOffset );
    
    $("#dataInizio").datetimepicker({
        timeText: 'Alle:',
        hourText: 'Ore',
    	minuteText: 'Minuti',
    	currentText: 'Ora',
    	closeText: 'Ok',
        defaultTimezone: '+0100',
        minDate: limite
    });
    
    $("#tipoCbx").change(function(evt){
        
        var min = $("#tipoCbx option:selected").data("min");
        var max = $("#tipoCbx option:selected").data("max");
        
        $("#partecipantiCbx").empty();
        for(var i=min; i<max; i++){
            $("#partecipantiCbx").append('<option value="'+i+'">'+i+'</option>');
        }
        
    });

});