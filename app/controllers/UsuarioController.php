<?php

use Facebook\FacebookSession;
use Facebook\FacebookCanvasLoginHelper;

function validarEmail($email){
	//verifica se e-mail esta no formato correto de escrita
	if (!preg_match("/^([a-zA-Z0-9.-])*([@])([a-z0-9]).([a-z]{2,3})/", $email)){
		//$mensagem='E-mail Inv&aacute;lido!';
		//return $mensagem;
		return false;
	}
	else{
		//Valida o dominio
		$dominio=explode('@',$email);
		if(!checkdnsrr($dominio[1],'A')){
			//$mensagem='E-mail Inv&aacute;lido!';
			//return $mensagem;
			return false;
		}
		else{return true;} // Retorno true para indicar que o e-mail é valido
	}
}

class UsuarioController extends Controller {
    
    public function incluirPerfil(){
        
        $email = Input::get('email');
        $iduser = Input::get('iduser');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $perfis = Input::get('perfis');
        
        $user = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
        join('usuario_app_perfil','usuario_app_perfil.id_usuario_app','=','usuario_app.id_usuario_app')->where('usuario.email', '=', $email)
        ->where('usuario_app.app', '=', $app)->
        where('usuario.id_usuario', '=', $iduser)->
        //where('usuario_app_perfil.id_perfil', '=', $perfil)->
        first();
        if($user == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')),401);
        }
        
        foreach ($perfis as $p)
        {
            if($p == 3)
                return Response::json(array(utf8_encode('Cadastro não autorizado!')), 401);
            
                $user__ = Usuario::join('usuario_app', 'usuario_app.id_usuario', '=', 'usuario.id_usuario')->join('usuario_app_perfil', 'usuario_app_perfil.id_usuario_app', '=', 'usuario_app.id_usuario_app')
                ->where('usuario.email', '=', $email)
                ->where('usuario_app_perfil.id_perfil', '=', $p)
                ->where('usuario.id_usuario', '=', $iduser)
                ->where('usuario_app.app', '=', $app)
                ->first();
                
            if ($user__ == null) {
                try{
                    $usuarioAppPerfil = new UsuarioAppPerfil();
                    $usuarioAppPerfil->id_usuario_app = $user->id_usuario_app;
                    $usuarioAppPerfil->app = $app;
                    $usuarioAppPerfil->id_perfil = $p;
                    $usuarioAppPerfil->save();
                }catch(Exception $e) {
                    return Response::json(array('status' => false,
                        'error' => $e->getMessage()),// ->errors()),
                        409);
                    
                }
            }
                
        }
        
        return Response::json(array('_token' => csrf_token(),
            'usuario' => $user->toArray()
        ),200);
        
        
    }
	
	public function cadastrarUsuario(){
		
		$email = Input::get('email');
		$email_ = Input::get('email_');
		$senha = Input::get('password');
		$confirmation = Input::get('confirmation');
		$app = Input::get('app');
		$perfis = Input::get('perfil');
		
		$iduser = Input::get('iduser');
		
		if($app == 'rcpecas'){
		    if((new UsuarioController())->validarAcessoTela($iduser, $email_, $app) == -1){
		        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
		    }
		}
		
		if($app == 'projetoformar'){
		    
		    if($perfis == null)
		        return Response::json(array(utf8_encode('Informe pelo menos um perfil!')), 409);
		    
		    foreach ($perfis as $perfil)
		    {
		        if($perfil == 3)
		            return Response::json(array(utf8_encode('Cadastro não autorizado!')), 401);
		    }
		    
		}
		
		$email = strtolower(trim($email));
		
		if(validarEmail($email)){
			
			if($senha != $confirmation){
				return Response::json(array(utf8_encode('As senhas não conferem!')),401);
			}
			
			if (Hash::needsRehash($senha))
			{
				$senha = Hash::make($senha);
			}
			
			$user = Usuario::where('email', '=', $email)->first();
			if($user != null){
			
				$user_ = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->where('usuario.email', '=', $email)->
				where('usuario_app.app', '=', $app)->where('usuario.id_usuario', '=', $user->id_usuario)->first();
				
				if($user_ != null){			
					return Response::json(array(utf8_encode('Já existe um usuário com o e-mail '.$email)), 409);
				}else{
					try{
						$usuarioapp = new UsuarioApp();
						$usuarioapp->app = $app;
						$usuarioapp->id_usuario = $user->id_usuario;
						$usuarioapp->password = $senha;
						$usuarioapp->save();
						
						if($perfis != null){
    						foreach ($perfis as $perfil)
    						{
    						    
    						    $user__ = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
    						    join('usuario_app_perfil','usuario_app_perfil.id_usuario_app','=','usuario_app.id_usuario_app')->
    						    where('usuario.email', '=', $email)->
    						    where('usuario.id_usuario', '=', $user->id_usuario)->
    						    where('usuario_app_perfil.id_perfil', '=', $perfil)->
    						    where('usuario_app.app', '=', $app)->first();
    						    
    						    if($user__ == null){
    						        $usuarioAppPerfil = new UsuarioAppPerfil();
    						        $usuarioAppPerfil->id_usuario_app = $usuarioapp->id_usuario_app;
    						        $usuarioAppPerfil->app = $app;
    						        $usuarioAppPerfil->id_perfil = $perfil;
    						        $usuarioAppPerfil->save();
    						        
    						    }
    						    
    						}
						}
						
					    if($iduser != null){
					        $usuarioConvite = UsuarioConvite::where('id_usuario','=',$iduser)->where('email_convidado','=',$email)->
						                              where('app','=',$app)->first();
					        if($usuarioConvite != null){
					            $usuarioConvite->situacao = 1;
					            $usuarioConvite->update();
				            
						        try{
						            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
						            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." aceitou seu convite. Clique no link abaixo para visualizar.",'link' => $actual_link), function($message)
							        {
							            $usuarioSolicitante = Usuario::where('id_usuario','=',Input::get('iduser'))->first();
							            $message->to($usuarioSolicitante->email,$usuarioSolicitante->email)->subject(utf8_encode('Seu convite de amizade foi aceito.'));
							        });
						        }catch(Exception $e) {
						            Log::info('Erro ao enviar e-mail');
						        }			            
				            
					        }/* else{
					            return Response::json(array(utf8_encode('Convite inexistente!')), 409);
					        } */
					    }
					    
						if(Input::get('placa') != ''){
							
							$veiculo = Veiculo::where('placa', '=', Input::get('placa'))->first();
							if($veiculo != null){					
								$usuarioapp->delete();
								return Response::json(array('Ja existe um usuario utilizando a placa '.strtoupper(Input::get('placa'))), 409);
							}				
							
							$veiculo = new Veiculo();
							$veiculo->placa = strtoupper(str_replace("-", "", Input::get('placa')));
							$veiculo->id_usuario = $user->id_usuario;
							
							try{
								$veiculo->save();
							}catch(Exception $e) {
								Log::info('Erro ao salvar');
								$usuarioapp->delete();
								return Response::json(array('status' => false,
											'error' => $e->getMessage()),// ->errors()),
											409);
							}				
							
						}					    
					    
		    			return Response::json(array('_token' => csrf_token(),
		    			    'usuario' => $user->toArray()
							),200);
					    
					}catch(Exception $e) {
						Log::info('Erro ao salvar usuario app');
						return Response::json(array('status' => false,
							'error' => $e->getMessage()),// ->errors()),
							409);
					}
				}
			}
			
			DB::beginTransaction();
			
			$usuario = new Usuario();
			$usuario->email = $email;
			
			try{
				$usuario->save();
				$usuarioapp = new UsuarioApp();
				$usuarioapp->app = $app;
				$usuarioapp->id_usuario = $usuario->id_usuario;
				$usuarioapp->password = $senha;
				$usuarioapp->save();
				
				if($perfis != null){
    				foreach ($perfis as $perfil)
    				{
    				    
    				    $user__ = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
    				    join('usuario_app_perfil','usuario_app_perfil.id_usuario_app','=','usuario_app.id_usuario_app')->
    				    where('usuario.email', '=', $email)->
    				    where('usuario.id_usuario', '=', $usuario->id_usuario)->
    				    where('usuario_app_perfil.id_perfil', '=', $perfil)->
    				    where('usuario_app.app', '=', $app)->first();
    				    
    				    if($user__ == null){
    				        $usuarioAppPerfil = new UsuarioAppPerfil();
    				        $usuarioAppPerfil->id_usuario_app = $usuarioapp->id_usuario_app;
    				        $usuarioAppPerfil->app = $app;
    				        $usuarioAppPerfil->id_perfil = $perfil;
    				        $usuarioAppPerfil->save();
    				    }
    				    
    				}
				}
				
				
				    if($iduser != null){
				        $usuarioConvite = UsuarioConvite::where('id_usuario','=',$iduser)->where('email_convidado','=',$email)->
					                       where('app','=',$app)->first();
				        if($usuarioConvite != null){
				            $usuarioConvite->situacao = 1;
				            $usuarioConvite->update();
			            
					        try{
					            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
					            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." aceitou seu convite. Clique no link abaixo para visualizar.",'link' => $actual_link), function($message)
						        {
						            $usuarioSolicitante = Usuario::where('id_usuario','=',Input::get('iduser'))->first();
						            $message->to($usuarioSolicitante->email,$usuarioSolicitante->email)->subject(utf8_encode('Seu convite de amizade foi aceito.'));
						        });
					        }catch(Exception $e) {
					            Log::info('Erro ao enviar e-mail');
					        }			            
			            
				        }/* else{
				            return Response::json(array(utf8_encode('Convite inexistente!')), 409);
				        } */
				    }
			}catch(Exception $e) {
				Log::info('Erro ao salvar');
			
// 				if(strrpos($e->getMessage(), "1062 Duplicate entry")){
// 					return Response::json(array('Ja existe um usuario com o e-mail '.Input::get('email')), 409);
// 				}else{
					return Response::json(array('status' => false,
							'error' => $e->getMessage()),// ->errors()),
							409);
// 				}
			
			}			
			
			//$veiculoctrl = new VeiculoController();
			//$veiculo = $veiculoctrl->cadastrarVeiculo();
			if(Input::get('placa') != ''){
				
				$veiculo = Veiculo::where('placa', '=', Input::get('placa'))->first();
				if($veiculo != null){					
// 					DB::rollback();
					$usuario->delete();
					return Response::json(array('Ja existe um usuario utilizando a placa '.strtoupper(Input::get('placa'))), 409);
				}				
				
				$veiculo = new Veiculo();
				$veiculo->placa = strtoupper(str_replace("-", "", Input::get('placa')));
				$veiculo->id_usuario = $usuario->id_usuario;
				
				try{
					$veiculo->save();
				}catch(Exception $e) {
					Log::info('Erro ao salvar');
					
// 					DB::rollback();
					
					$usuario->delete();
						
// 					if(strrpos($e->getMessage(), "1062 Duplicate entry")){
// 						return Response::json(array('Ja existe um usuario utilizando a placa '.strtoupper(Input::get('placa'))), 409);
// 					}else{
						return Response::json(array('status' => false,
								'error' => $e->getMessage()),// ->errors()),
								409);
// 					}
				
				}				
				
			}
			
			DB::commit();
			
			return Response::json(array('_token' => csrf_token(),
			    'usuario' => $usuario->toArray()
										),200);
		
		}else{
			
			return Response::json(array(utf8_encode('Email Inválido!')),401);
			
		}
		
	}
	
	public function cadastrarUsuarioFacebook(){
	
		$email = Input::get('email');
		
		$email = strtolower(trim($email));
	
		$usuario = new Usuario();
		$usuario->email = $email;
		$usuario->save();
	
		return Response::json(array('_token' => csrf_token(),
				'usuario' => $usuario->toArray()
		),200);
	
	}	
	
	public function cadastrarUsuarioExterno($email){
	
		$usuario = new Usuario();
		
		$email = strtolower(trim($email));
		
		$usuario->email = $email;
		
		$user = Usuario::where('email', '=', $email)->first();
		if($user != null)
			return false;		
		
		$usuario->save();
		
		return $usuario->id_usuario;		
	
	}

	public function postLogin(){
		
		$regras = ['email' => 'required',
		'password' => 'required'];
		
		$validacao = Validator::make(Input::only('email','password'), $regras);
		
		if ($validacao->fails()){
			return Response::json($validacao->errors(), 406);
		}		
		
		$email = Input::get('email');
		
		$email = strtolower(trim($email));
		
		$app = Input::get('app');
		
		if(validarEmail($email)){
		    
		    $usuario = Usuario::where('email','=',$email)->first();
		    
		    if($usuario == null)
		    	return Response::json(array(utf8_encode('Usuário não cadastrado!')),401);

			$credentials = array(
			        'id_usuario' => $usuario->id_usuario,
					'password' => Input::get('password'),
					'app' => Input::get('app')
			);
			
			$autenticado = Auth::attempt($credentials);
			
			if($autenticado) {
			    
				$user = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->where('usuario.email', '=', $email)
				->where('usuario_app.app', '=', $app)->where('usuario.id_usuario', '=', $usuario->id_usuario)->first();
				if($user == null){
				    return Response::json(array(utf8_encode('Acesso não autorizado!')),401);
				}
				
				if($app == 'projetoformar'){
				    $user = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
				    join('usuario_app_perfil','usuario_app_perfil.id_usuario_app','=','usuario_app.id_usuario_app')->where('usuario.email', '=', $email)
				    ->where('usuario_app.app', '=', $app)->where('usuario.id_usuario', '=', $usuario->id_usuario)->first();
				    if($user == null){
				        return Response::json(array(utf8_encode('Acesso não autorizado!')),401);
				    }
				}				
				
				return Response::json(array('_token' => csrf_token(),
											 'usuario' => $user->toArray()
											),200);
			}else {
				return Response::json(array(utf8_encode('Email ou senha inválidos!')),401);
			}
		
		}else{

			return Response::json(array(utf8_encode('Email Inválido!')),401);
			
		}
		
	}
	
	public function isUsuarioExistente(){
		
		$email = Input::get('email');
		
		$email = strtolower(trim($email));
		
		$user = Usuario::where('email', '=', $email)->first();
		
		if($user != null){
			return Response::json(array('_token' => csrf_token(),'usuario' => $user->toArray()),200);			
		}else{
			return Response::json(array('Email ou senha invalidos!'),401);
		}
		
		
	}
	
	public function isUsuarioExternoExistente($email){
	
		$user = Usuario::where('email', '=', $email)->whereNull('password')->first();
	
		if($user != null){
			return $user->id_usuario;
		}else{
			return false;
		}
	
	
	}	
	
// 	public function getLoginFacebook(){
		
// 		FacebookSession::setDefaultApplication('324331947773033', '26a78b2116828321cf85c06a0c9f14e6');
		
// // 		$helper = new FacebookCanvasLoginHelper();
// // 		try {
// // 			$session = $helper->getSession();
// // 			return Response::json(array('OK' => $session),200);
// // 		} catch(FacebookRequestException $ex) {
// // 			// When Facebook returns an error
// // 			return Response::json(array('Erro face'),401);
// // 		} catch(\Exception $ex) {
// // 			// When validation fails or other local issues
// // 			return Response::json(array('Erro fatal' => $ex->getMessage()),401);
// // 		}
		
// // 		if ($session) {
// // 			// Logged in
// // 			//return Response::json(array('OK' => 'Ratinho'),200);
// // 			//echo "OK";
// // 		}		
		
		
// 		$helper = new FacebookRedirectLoginHelper('/public/loginFacebook.php');
// 		//$loginUrl = $helper->getLoginUrl();
		
// 		//return Response::json(array('OK' => $loginUrl),200);
		
// // 		try {
// // 			$session = $helper->getSessionFromRedirect();
// // 		} catch(FacebookRequestException $ex) {
// // 			// When Facebook returns an error
// // 			return Response::json(array('Erro face!'),401);
// // 		} catch(\Exception $ex) {
// // 			// When validation fails or other local issues
// // 			return Response::json(array('Erro fatal!' => $ex->getMessage()),401);
// // 		}
// // 		if ($session) {
// // 			// Logged in
// // 			return Response::json(array('OK' => 'Ratinho!'),200);
// // 		}		
		
// 	}

	public function postLoginGoogle(){
	
		$email = Input::get('email');
		$idGoogle = Input::get('idGoogle');
		
		if ($idGoogle == null){
			return Response::json(array('Operacao nao autorizada!'),401);
		}		
	
		if(validarEmail($email)){
			
			$usuarioController = new UsuarioController();
			$usuarioExistente = $usuarioController->isUsuarioExternoExistente($email);

			if($usuarioExistente){
				return Response::json(array('email' => $email,'iduser' => $usuarioExistente),200);
				//return Redirect::to('inicio.html?email='.$email.'&go=true&iduser='.$usuarioExistente);
			}else{
				$user = $usuarioController->cadastrarUsuarioExterno($email);
				if($user){
					return Response::json(array('email' => $email,'iduser' => $user),200);
					//return Redirect::to('inicio.html?email='.$email.'&go=true&iduser='.$user);
				}else{
					return Response::json(array('Ja existe um usuario com o e-mail '.$email), 401);
					//return Redirect::to('login.html?email='.$email.'&go=true&msg=usuarioExistente');
				}
			}			
	
		}else{
	
			return Response::json(array('Email Invalido!'),401);
				
		}
	
	}	
	
	public function esqueciMinhaSenha(){
	
		$email = Input::get('email');
		$app = Input::get('app');
	
		$email = strtolower(trim($email));
		
		if(validarEmail($email)){
			
			try{
			
				$user = Usuario::where('email', '=', $email)->first();
				
				if($user != null){
				    
				    $usuarioApp = UsuarioApp::where('id_usuario','=',$user->id_usuario)->
				    where('app','=',$app)->first();
				    
				    if($usuarioApp != null){
				    
				        if($usuarioApp->password != null){
				            $usuarioApp->esqueciMinhaSenha = $usuarioApp->password;
    					}else{
    					    $usuarioApp->esqueciMinhaSenha = Hash::make(strtolower($user->email));
    					}
    					
    					$usuarioApp->update();
    					
    					Mail::send('alertesqueciminhasenha', array('key' => 'value','app' => strtoupper($app),'link' => 'http://www.randowfactory.com/apps/public/'.$app.'/esqueciMinhaSenhaConfirmacao.html?key='.$usuarioApp->esqueciMinhaSenha.'&email='.strtolower($user->email)), function($message)
    					{
    						$email = Input::get('email');
    						$email = strtolower(trim($email));
    						
    						$message->to($email,$email)->subject(strtoupper(Input::get('app')).' - Esqueci Minha Senha');
    					
    					});					
    					
    					return Response::json(array('Foi enviado um e-mail para novo cadastro de senha.'),200);
					
				    }else{
				        return Response::json(array('Usuario nao cadastrado!'),401);
				    }
				}else{
					return Response::json(array('Usuario nao cadastrado!'),401);
				}

			}catch(Exception $e) {
				return Response::json(array('status' => false,
						'error' => $e->getMessage()),// ->errors()),
						406);
			}
		
		}else{
				
			return Response::json(array('Email Invalido!'),401);
				
		}
		
	}	
	
	public function esqueciMinhaSenhaConfirmacao(){
	
		$key = Input::get('key');
		$email = Input::get('email');
		$app = Input::get('app');
		
		$email = strtolower(trim($email));
			
		try{
			
			if($key == null || $key == ""){
				return Response::json(array('Chave Incorreta!'),401);
			}			
				
			$user = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
			where('usuario.email', '=', $email)->where('usuario_app.esqueciMinhaSenha', '=', $key)->
			where('usuario_app.app', '=', $app)->first();

			if($user != null){		
				return Response::json(array('OK'),200);
			}else{
				return Response::json(array('Erro ao validar link!'),401);
			}

		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	

	}	
	
	public function esqueciMinhaSenhaAtualizacaoSenha(){
	
		$key = Input::get('key');
		$email = Input::get('email');
		$senha = Input::get('password');
		$app = Input::get('app');
		$confirmation = Input::get('confirmation');	

		$email = strtolower(trim($email));
			
		try{
			
			if($key == null || $key == ""){
				return Response::json(array('Chave Incorreta!'),401);
			}
	
			$user = Usuario::where('email', '=', $email)->first();
	
			if($user != null){
			    
			    $usuarioApp = UsuarioApp::where('id_usuario','=',$user->id_usuario)->where('esqueciMinhaSenha', '=', $key)->
			    where('app','=',$app)->first();
			    
			    if($usuarioApp != null){
			        if($senha != $confirmation){
			            return Response::json(array('As senhas nao conferem!'),401);
			        }
			        
			        if (Hash::needsRehash($senha))
			        {
			            $senha = Hash::make($senha);
			        }
			        
			        $usuarioApp->password = $senha;
			        $usuarioApp->esqueciMinhaSenha = null;
			        
			        $usuarioApp->update();
			        
			        return Response::json(array('Senha alterada com sucesso!'),200);
			    }else{
			        return Response::json(array('Erro ao atualizar senha!'),401);
			    }
				

			}else{
				return Response::json(array('Erro ao atualizar senha!'),401);
			}
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	
	}
	
	public function validarAcessoTela($iduser, $email, $app){
	
		//$email = Input::get('email');
		//$iduser = Input::get('iduser');
		//$app = Input::get('app');
		
	    if($email == 'undefined' || $email == null || $email == '' || $iduser == 'undefined' || $iduser == 0 || $iduser == null || $iduser == '' || $app == null || $app == ''){
	        return -1;
		}
		
		try{
			
			$usuario = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->where('usuario.id_usuario','=',$iduser)->
			where('usuario.email','=',$email)->
			where('usuario_app.app','=',$app)->firstOrFail();
			
			return 0;
	
		}catch(Exception $e) {
			return -1;
		}		
	
	}
	
	public function carregarPerfis($email = '', $iduser = 0, $app = ''){
	    
    	try{
    	    
    	    if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
    	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
    	    }
    	    
    	    $perfis = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
    	    join('usuario_app_perfil','usuario_app_perfil.id_usuario_app','=','usuario_app.id_usuario_app')->
    	    join('perfil','perfil.id_perfil','=','usuario_app_perfil.id_perfil')->where('usuario.email', '=', $email)
    	    ->where('usuario_app.app', '=', $app)->select('perfil.id_perfil','perfil.descricao')->get();
    	    
    	    return Response::json(array('status' => true,
    	        'Perfil' => $perfis->toArray()),
    	        200);
    	    
    	}catch(Exception $e) {
    	    return Response::json(array('status' => false,
    	        'error' => $e->getMessage()),// ->errors()),
    	        406);
    	}
	    
	}
	
}