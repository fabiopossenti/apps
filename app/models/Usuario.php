<?php

use Illuminate\Auth\UserInterface;

class Usuario extends Eloquent implements UserInterface {
	
	protected $table = 'usuario';
	protected $primaryKey = 'id_usuario';
	
	protected $hidden = array('password');
	
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	public function getRememberToken(){
	
	}
	
	public function setRememberToken($value){
	
	}
	
	public function getRememberTokenName(){
	
	}
	

}

