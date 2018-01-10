//var host = 'herbiee.pe.hu';
//var host = 'flanelinhaonline.16mb.com';
//var host = 'flanelinhaonline.com';
var host = 'localhost';
//var host = '192.168.1.101';


$(document).ready(
		function() {
		    
/*		    $('#btnLimpar').hide();
		    
			$('#btnLimpar').click(
					function() {
						$('#placa_ltr').val("");
						$('#placa_num').val("");
						$('#btnLimpar').hide();
						$('#placa_ltr').focus();
			});

				$('#placa_ltr').on( "keyup", function(e) {
					
					if($('#placa_ltr').val().length > 0){
						$('#btnLimpar').show();
						$('#placa_ltr').val($('#placa_ltr').val().toUpperCase());
					}else
						$('#btnLimpar').hide();
					
					if($('#placa_ltr').val().length == 1 && $.isNumeric($('#placa_ltr').val().substr(0,1)))
						$('#placa_ltr').val("")

					if($('#placa_ltr').val().length == 2 && $.isNumeric($('#placa_ltr').val().substr(1,2)))
						$('#placa_ltr').val($('#placa_ltr').val().substr(0,1)) 						
						
					if($('#placa_ltr').val().length == 3 && $.isNumeric($('#placa_ltr').val().substr(2,1)))
						$('#placa_ltr').val($('#placa_ltr').val().substr(0,2))
						
					if($('#placa_ltr').val().length >= 3){
						$('#placa_ltr').val($('#placa_ltr').val().substr(0,3));
						$('#placa_num').focus();
					} 						

			} );
				
			$('#placa_num').on( "keyup", function(e) {
					
					if($('#placa_num').val().length == 1 && !$.isNumeric($('#placa_num').val().substr(0,1)))
						$('#placa_num').val("");
						
					if($('#placa_num').val().length == 2 && !$.isNumeric($('#placa_num').val().substr(1,2)))
						$('#placa_num').val($('#placa_num').val().substr(0,1)); 						
					
					if($('#placa_num').val().length == 3 && !$.isNumeric($('#placa_num').val().substr(2,3)))
						$('#placa_num').val($('#placa_num').val().substr(0,2)); 					
					
					if($('#placa_num').val().length == 4 && !$.isNumeric($('#placa_num').val().substr(3,4)))
						$('#placa_num').val($('#placa_num').val().substr(0,3));
						
					if($('#placa_num').val().length >= 4){
						$('#placa_num').val($('#placa_num').val().substr(0,4));
						$('#linkLimpar').focus();
					}
					 
			} );*/ 

		});

function maskPlaca(placa){
	try{
		ltr = placa.substr(0,3);
		num = placa.substr(3,7);
		
		return ltr + '-' + num;
		
	}catch(e){
		return placa;
	}
}

function formatarData(data){
	
	try{
	
		var formattedDate = new Date(data);
		var d = formattedDate.getDate();
		d += 1;
		var m =  formattedDate.getMonth();
		m += 1;  // JavaScript months are 0-11
		var y = formattedDate.getFullYear();

		//$("#txtDate").val(d + "/" + m + "/" + y);
		
		return d + "/" + m + "/" + y
	
	}catch(e){
		return data;
	}
	
}

function formatarDataHora(data){
	
	try{
		
		dt = formatarData(data.split(' ')[0]);
		
		return dt + " " + data.split(' ')[1];
	
	}catch(e){
		return data;
	}
	
}

function formatarMsgErro(msg){
	return msg.replace('[','').replace('"','').replace(']','').replace('"','');
}

function milhar(n){
    var n = ''+n, t = n.length -1, novo = '';

    for( var i = t, a = 1; i >=0; i--, a++ ){
        var ponto = a % 3 == 0 && i > 0 ? '.' : '';
        novo = ponto + n.charAt(i) + novo;
    }
    return novo;
}