<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>ALERTA</h2>

		<div>
			Voc� recebeu uma notifica��o de alerta.
		</div>
		
		<div>
			Data: <?php echo $data; ?>
		</div>		
		
		<div>
			Placa: <?php echo $placa; ?>
		</div>		
		
		<div>
			Evento: <?php echo $evento; ?>
		</div>		
		
		<div>
			<?php if($avaliacao == 'N/A'){ ?>
				Avalia��o do informante: Nenhuma avalia��o ainda.
			<?php }else{ ?>
				Avalia��o do informante: <?php echo $avaliacao; ?>% confi�vel. <?php echo $totalAvaliacao; ?> avalia��o(�es).
			<?php } ?>
		</div>		
		
	</body>
</html>
