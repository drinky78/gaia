$(document).ready( function() {
    
    var limite = new Date();
    
    $("#dataTitolo").datetimepicker({
        timeText: 'Alle:',
        hourText: 'Ore',
    	minuteText: 'Minuti',
    	showTime: false,
        showHour: false,
        showMinute: false,
        timeFormat: '',
        currentText: 'Ora',
    	closeText: 'Ok',
        defaultTimezone: '+0100',
        maxDate: limite
    });


});