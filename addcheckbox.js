(function( $ ) {
	
	$('body').on('click',"a[id*='cke_Upload']",function(){
		var formobj = $('iframe.cke_dialog_ui_input_file').contents().find('form');
		var inputobj = formobj.find('input#watermark');
		if ( inputobj.prop("tagName") != 'INPUT' ) {
			$('iframe.cke_dialog_ui_input_file').contents().find('form').append('<label><input type="checkbox" id="watermark" name="watermark" checked> Добавить Логотип</label>');
		}
	});
	
}( window.jQuery ));