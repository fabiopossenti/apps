<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2><?php echo utf8_encode("Olá!"); ?></h2>

		<div>
			<?php echo utf8_encode($texto); ?>
		</div>
		
		<br/>
		
		<div>
			<a href="<?php echo utf8_encode($link); ?>"><?php echo utf8_encode($link); ?></a>
		</div>
		
	</body>
</html>
