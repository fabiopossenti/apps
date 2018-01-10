function hideContentDialog(){
	$('.ui-content').css('padding','1em');
	contentDialogVar.hide();
}

function hideContentDialog2(){
	$('.ui-content').css('padding','1em');
	contentDialog2Var.hide();
}	

function hideContentDialogConfirmacaoExclusao(){
	$('.ui-content').css('padding','1em');
	contentDialogConfirmacaoExclusaoVar.hide();
}		

function hideContentDialogConfirmacao1(){
	$('.ui-content').css('padding','1em');
	contentDialogConfirmacao1Var.hide();
}

function hideContentDialogConfirmacao2(){
	$('.ui-content').css('padding','1em');
	contentDialogConfirmacao2Var.hide();
}

function showContentDialog(){
	$('.ui-content').css('padding','0em');
	contentDialogVar.show();
}

function showContentDialog2(data){
	$('.ui-content').css('padding','0em');
	contentDialog2Var.show();
}

function showContentDialogConfirmacaoExclusao(data){
	$('.ui-content').css('padding','0em');
	contentDialogConfirmacaoExclusaoVar.show();
}

function showContentDialogConfirmacao1(data){
	$('.ui-content').css('padding','0em');
	contentDialogConfirmacao1Var.show();
}

function showContentDialogConfirmacao2(data){
	$('.ui-content').css('padding','0em');
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