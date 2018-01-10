<?php

use Illuminate\Auth\UserInterface;

class UsuarioApp extends Eloquent implements UserInterface {
	
	protected $table = 'usuario_app';
	protected $primaryKey = 'id_usuario_app';
	
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

