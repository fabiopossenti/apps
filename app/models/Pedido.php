<?php

use Illuminate\Auth\UserInterface;

class Pedido extends Eloquent {
	
	protected $table = 'pedido';
	protected $primaryKey = 'id_pedido';
	
	protected $itens = "itens";
	protected $qtdItens = "qtdItens";
	protected $vlrTotal = "vlrTotal";

}

