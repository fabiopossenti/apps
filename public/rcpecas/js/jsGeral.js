// Global declarations - assignments made in $(document).ready() below
var hdrMainvar = null;
var contentMainVar = null;
var ftrMainVar = null;
var contentTransitionVar = null;
var form1var = null;
var confirmationVar = null;
var contentDialogVar = null;
var contentDialog2Var = null;
var contentDialogConfirmacaoExclusaoVar = null
var contentDialogConfirmacao1Var = null
var contentDialogConfirmacao2Var = null
var hdrConfirmationVar = null;
var contentConfirmationVar = null;
var ftrConfirmationVar = null;
var inputMapVar = null;
// Constants
var MISSING = "missing";
var EMPTY = "";
var NO_STATE = "ZZ";

var iduser_;
var email_;
var list;
var isArquivoGrande = false;

var qtdEventosRecentes = 0;
var idSolicitacaoPosicao_;

function hideMain(){
	hdrMainVar.hide();
	contentMainVar.hide();
	ftrMainVar.hide();
}

function showMain(){
	hdrMainVar.show();
	contentMainVar.show();
	ftrMainVar.show();
}

function hideContentTransition(){
	contentTransitionVar.hide();
}

function showContentTransition(){
	contentTransitionVar.show();
}

function hideConfirmation(){
	hdrConfirmationVar.hide();
	contentConfirmationVar.hide();
	ftrConfirmationVar.hide();
}

function showConfirmation(){
	hdrConfirmationVar.show();
	contentConfirmationVar.show();
	ftrConfirmationVar.show();
}

$('#btnVoltar').click(function() {
	$(window).attr('location', 'ondeceta.html');
});

$('#btnVoltarTop').click(function() {
	$(window).attr('location', 'ondeceta.html');
});

$('#btnCancelar').click(function() {
	resetar();
});

$('#buttonOK').click(function() {
	hideContentDialog();
	showMain();
	return false;
});

$('#buttonOK2').click(function() {
	hideContentDialog2();
	showMain();
	return false;
});

$('#buttonNao').click(function() {
	hideContentDialogConfirmacaoExclusao();
	showMain();
	return false;
});

$('#buttonCancelar').click(function() {
	hideContentDialogConfirmacao1();
	showMain();
	return false;
});

$('#buttonCancelar2').click(function() {
	hideContentDialogConfirmacao2();
	showMain();
	return false;
});

function redirecionarTela(tela){
	if(tela == 1){
		//$(window).attr('location','cadastroTipoFornecedor.html');
	}else if (tela == 2){
		$(window).attr('location','login.html?frommenu=1');
	}else if (tela == 3){
		//$(window).attr('location','cadastroFornecedor.html');
	}else if (tela == 4){
		//$(window).attr('location','ondeceta.html');
	}
}

$(document).ready(
		function() {

			var navigation = window.localStorage.getItem("herbiee_navigation");
			if(navigation != null){
			    var s = navigation;
			    iduser_ = s.substr(0,s.indexOf(';;;;'));
			    email_ = s.substr(s.indexOf(';;;;')+4,s.lentgh);
			    $('#emailUsuario').append('<font color="white">' + email_ + '</font>');
			    $('#lbUsuario').append('Bem vindo ' + email_ + '!');
			    //verificarEventosRecentes();
			}

			// Assign global variables
			hdrMainVar = $('#hdrMain');
			contentMainVar = $('#contentMain');
			ftrMainVar = $('#ftrMain');
			contentTransitionVar = $('#contentTransition');
			form1Var = $('#form1');
			confirmationVar = $('#confirmation');
			contentDialogVar = $('#contentDialog');
			contentDialog2Var = $('#contentDialog2');
			contentDialogConfirmacaoExclusaoVar = $('#contentDialogConfirmacaoExclusao');
			contentDialogConfirmacao1Var = $('#contentDialogConfirmacao1');
			contentDialogConfirmacao2Var = $('#contentDialogConfirmacao2');
			hdrConfirmationVar = $('#hdrConfirmation');
			contentConfirmationVar = $('#contentConfirmation');
			ftrConfirmationVar = $('#ftrConfirmation');
			inputMapVar = $('input[name*="_r"]');
			hideContentTransition();
			hideConfirmation();

			$('#btnConfirmar').hide();
			$('#btnExcluir').hide();
			$('#btnCancelar').hide();
			
			$('#iduser').val(iduser_);
			$('#email').val(email_);
			
});