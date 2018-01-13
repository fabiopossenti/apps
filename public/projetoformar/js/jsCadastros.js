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

function obterSituacaoAlunoNaComissao(id){
	
	if(id == 1)
		return "Aprovado";
	else if(id == 0)
		return "Em Analise";
	else if(id == 2)
		return "Reprovado";
	else
		return "N/A";
	
}

function obterTipoAlunoNaComissao(id){
	
	if(id == 1)
		return "Presidente";
	else if(id == 0)
		return "N/A";
	else if(id == 2)
		return "Vice-Presidente";
	else if(id == 3)
		return "Participante";	
	else
		return "N/A";
	
}