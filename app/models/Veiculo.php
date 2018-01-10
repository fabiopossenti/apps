<?php

use Illuminate\Auth\UserInterface;

class Veiculo extends Eloquent {
	
	protected $table = 'veiculo';
	protected $primaryKey = 'id_veiculo';

 	public function usuario(){
 		return $this->hasOne('Usuario','id_usuario','id_usuario');
 	}

}

