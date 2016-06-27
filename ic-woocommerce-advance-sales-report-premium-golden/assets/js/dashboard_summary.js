jQuery(document).ready(function($) {	
	
	//$('.ic_dashboard_summary_form').hide();
	
	jQuery( "#default_date_rage_start_date" ).datepicker({
		dateFormat : 'yy-mm-dd',		
		changeMonth: true,
		changeYear: true,
		maxDate:0,
		onClose: function( selectedDate ) {
			$( "#default_date_rage_end_date" ).datepicker( "option", "minDate", selectedDate );
		}
	});							
	
	jQuery( "#default_date_rage_end_date" ).datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		///maxDate: 0,
		onClose: function( selectedDate ) {
			$( "#default_date_rage_start_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	}); 
	
	/*
	$('.ic_dashboard_summary_title span').click(function(){
		
	})
	*/
	
	var ic_dashboard_summary_form = false;
	
	$('.ic_dashboard_summary_change span').click(function(){
		if(!ic_dashboard_summary_form){
			$('.ic_dashboard_summary_title').hide();
			$('.ic_dashboard_summary_form').show();
			ic_dashboard_summary_form = true;
		}else{
			$('.ic_dashboard_summary_form').hide();
			$('.ic_dashboard_summary_title').show();			
			ic_dashboard_summary_form = false;
		}
	});
	
	$(".quick_date_change").change(function(){
		alert($(this).val());
	});
	
});