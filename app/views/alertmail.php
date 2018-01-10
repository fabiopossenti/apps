<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>ALERTA</h2>

		<div>
			Você recebeu uma notificação de alerta.
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
				Avaliação do informante: Nenhuma avaliação ainda.
			<?php }else{ ?>
				Avaliação do informante: <?php echo $avaliacao; ?>% confiável. <?php echo $totalAvaliacao; ?> avaliação(ões).
			<?php } ?>
		</div>		
		
	</body>
</html>
