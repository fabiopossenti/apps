function hideContentDialog(){
	contentDialogVar.hide();
}

function hideContentDialog2(){
	contentDialog2Var.hide();
}	

function hideContentDialogConfirmacaoExclusao(){
	contentDialogConfirmacaoExclusaoVar.hide();
}				

function hideContentDialogConfirmacao1(){
	contentDialogConfirmacao1Var.hide();
}				

function hideContentDialogConfirmacao2(){
	contentDialogConfirmacao2Var.hide();
}				

function showContentDialog(){
	contentDialogVar.show();
}

function showContentDialog2(data){
	contentDialog2Var.show();
}

function showContentDialogConfirmacaoExclusao(data){
	contentDialogConfirmacaoExclusaoVar.show();
}

function showContentDialogConfirmacao1(data){
	contentDialogConfirmacao1Var.show();
}

function showContentDialogConfirmacao2(data){
	contentDialogConfirmacao2Var.show();
}

$(document).ready(
		function() {
			hideContentDialog();
			hideContentDialog2();
			hideContentDialogConfirmacaoExclusao();
			hideContentDialogConfirmacao1();
			hideContentDialogConfirmacao2();
});