<?php

class FornecedorController extends Controller {
    
    public function cadastrar(){        
        
        $midiaController = new MidiaController();
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $id = Input::get('id');
        $flexcluir = Input::get('flexcluir');
        $tipoFornecedor = Input::get('tipoFornecedor');
        $nome = Input::get('nome_r');
        $identificacao = Input::get('identificacao_r');
        $endereco = Input::get('endereco_r');
        $telefone = Input::get('telefone_r');
        $cerimonial = Input::get('cerimonial');
        $situacao = Input::get('situacao');
        $emailVinculado = Input::get('emailV');
        $file = Input::file('file');
        $idMidia = null;
        $fornecedor = null;
        $usuarioVinculado = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if($cerimonial == null)
            $cerimonial = 0;
        
        if($id != null)
            $fornecedor = Fornecedor::where('id_fornecedor', '=', $id)->first();
        
        if($file != null || $flexcluir != null){
            if($fornecedor != null)
                $idMidia = $midiaController->upload($file, $iduser, $fornecedor->id_midia, $flexcluir == null,$app);
            else
                $idMidia = $midiaController->upload($file, $iduser, null, $flexcluir == null,$app);
            if($idMidia == -1)
                return Response::json(array(utf8_encode('Tamanho máximo de bytes excedido do arquivo. Tamanho máximo: 1MB')), 409);
        }
        
        if($flexcluir != null){
            try{
                $fornecedor->delete();
            }catch(Exception $e) {
                Log::info('Erro ao excluir');
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
            return Response::json(array(utf8_encode('Registro removido com sucesso!')), 200);
        }
        
        if($emailVinculado != null && $perfil == 3){
            $usuarioVinculado = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->where('usuario.email', '=', $emailVinculado)->
            where('usuario_app.app', '=', $app)->first();
            
            if($usuarioVinculado != null){
                
                if($id != null){                    
                    $fornecedor = Fornecedor::where('id_fornecedor', '=', $id)->first();
                    if($fornecedor->id_usuario != $usuarioVinculado->id_usuario){
                        $forn = Fornecedor::where('id_usuario','=',$usuarioVinculado->id_usuario)->first();
                        if($forn != null){
                            return Response::json(array(utf8_encode('O e-mail vinculado informado já possui um Fornecedor!')), 409);
                        }
                    }
                }else{
                    $forn = Fornecedor::where('id_usuario','=',$usuarioVinculado->id_usuario)->first();
                    if($forn != null){
                        return Response::json(array(utf8_encode('O e-mail vinculado informado já possui um Fornecedor!')), 409);
                    }
                }
            }
        }

		if($id != null){
		
			$fornecedorSemUsuario = false;
		
			$fornecedor = Fornecedor::where('id_fornecedor', '=', $id)->first();
			
			if($fornecedor->id_usuario == null)
				$fornecedorSemUsuario = true;
			
			$fornecedor->id_tipo_fornecedor = $tipoFornecedor;
			$fornecedor->nome = $nome;
			$fornecedor->identificacao = $identificacao;
			$fornecedor->endereco = $endereco;
			$fornecedor->telefone = $telefone;
			$fornecedor->cerimonial = $cerimonial;
			if($perfil == 2){
	            		$fornecedor->situacao = 0;
			}
			if($perfil == 3){
			    $fornecedor->situacao = $situacao;
			    if($usuarioVinculado != null)
			         $fornecedor->id_usuario = $usuarioVinculado->id_usuario;
			}
			
			if($idMidia)
			    $fornecedor->id_midia = $idMidia;
			
	        try{
	            $fornecedor->update();
	        }catch(Exception $e) {
	            Log::info('Erro ao editar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }	        
	        
	        if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3){
                $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/cadastro.html?id_=".$fornecedor->id_fornecedor."&email=".$emailVinculado."&perfil=2";
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." está te convidando para entrar no Projeto Formar como Fornecedor.",'link' => $actual_link), function($message)
                {
                    $message->to(strtolower(trim(Input::get('emailV'))),strtolower(trim(Input::get('emailV'))))->subject(utf8_encode('Convite Projeto Formar - Fornecedor'));
                });	        
	            return Response::json(array(utf8_encode('Registro alterado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
	        }else if($emailVinculado != null && $usuarioVinculado != null && $perfil == 3 && $fornecedorSemUsuario){
	            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
	            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." adicionou o Fornecedor '".$fornecedor->nome."' na sua conta.",'link' => $actual_link), function($message)
	            {
	                $message->to(strtolower(trim(Input::get('emailV'))),strtolower(trim(Input::get('emailV'))))->subject(utf8_encode('Convite Projeto Formar - Fornecedor'));
	            });	 
	            return Response::json(array(utf8_encode('Registro alterado com sucesso! <br/><br/>Foi enviado um e-mail para '.$emailVinculado.'. ')), 200);
	        }else{
	        	return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);
	        }	        		
		
		}else{
		
	        $fornecedor = new Fornecedor();
	        $fornecedor->id_tipo_fornecedor = $tipoFornecedor;
	        $fornecedor->nome = $nome;
			$fornecedor->identificacao = $identificacao;
			$fornecedor->endereco = $endereco;
			$fornecedor->telefone = $telefone;
			$fornecedor->cerimonial = $cerimonial;
	        
	        if($perfil == 2){
	            $fornecedor->situacao = 0;
	            $fornecedor->id_usuario = $iduser;
	        }else if($perfil == 3){
	            $fornecedor->situacao = $situacao;
	            if($usuarioVinculado != null)
	                $fornecedor->id_usuario = $usuarioVinculado->id_usuario;
	        }
	        
	        if($idMidia)
	            $fornecedor->id_midia = $idMidia;
			
	        try{
	            $fornecedor->save();
	        }catch(Exception $e) {
	            Log::info('Erro ao salvar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
	        if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3){
                $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/cadastro.html?id_=".$fornecedor->id_fornecedor."&email=".$emailVinculado."&perfil=2";
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." está te convidando para entrar no Projeto Formar como Fornecedor.",'link' => $actual_link), function($message)
                {
                    $message->to(strtolower(trim(Input::get('emailV'))),strtolower(trim(Input::get('emailV'))))->subject(utf8_encode('Convite Projeto Formar - Fornecedor'));
                });	        
	            return Response::json(array(utf8_encode('Registro cadastrado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
	        }else if($emailVinculado != null && $usuarioVinculado != null && $perfil == 3){
	            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
	            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." adicionou o Fornecedor '".$fornecedor->nome."' na sua conta.",'link' => $actual_link), function($message)
	            {
	                $message->to(strtolower(trim(Input::get('emailV'))),strtolower(trim(Input::get('emailV'))))->subject(utf8_encode('Convite Projeto Formar - Fornecedor'));
	            });	 
	            return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);
	        }else{
	            return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);
	        }			
		
		}
 
    }
	
    public function getFornecedores($email = '', $iduser = 0, $app = '', $perfil = 0, $idComissao = 0, $idFornecedor = 0){
	    
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	
		try{
		    
		    if($perfil == 1 || $perfil == 4){ // ALUNO ou COMISSAO
			     
			        if($idComissao == 0){
				        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
				    }
			     
			     	$membro = ComissaoMembros::where('id_comissao','=',$idComissao)->where('email','=',$email)->where('situacao','=',1)->first();
			     	
        			if($membro == null)
            			return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
			     
			     	// where aprovado e faz parte da minha rede
			     	
            		if($idFornecedor != 0){
            		    
            		    $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
            		    leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
            		    leftJoin('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
            		    where('fornecedor.situacao','=',1)->
            		    where('fornecedor.id_fornecedor','=',$idFornecedor)->
            		    select('fornecedor.id_fornecedor',
            		        'fornecedor.id_usuario',
            		        'fornecedor.nome',
            		        'fornecedor.identificacao',
            		        'fornecedor.endereco',
            		        'fornecedor.telefone',
            		        'fornecedor.cerimonial',
            		        'fornecedor.id_tipo_fornecedor',
            		        'fornecedor.situacao',
            		        'tipo_fornecedor.nome as nomeTipoFornecedor',
            		        'usuario.email',
            		        'midia.caminho_midia')->get();
            		    
            		}else{
			     
    			        $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
    			        leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
    			        join('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
    			        where('fornecedor.situacao','=',1)->
    			        select('fornecedor.id_fornecedor',
    			             'fornecedor.id_usuario',
    			             'fornecedor.nome',
    			             'fornecedor.identificacao',
    			             'fornecedor.endereco',
    			             'fornecedor.telefone',
    			             'fornecedor.cerimonial',
    			             'fornecedor.id_tipo_fornecedor',
    			             'fornecedor.situacao',
    			             'tipo_fornecedor.nome as nomeTipoFornecedor',
    			             'usuario.email',
    			             'midia.caminho_midia')->get();
			        
            		}
			         
			     }else if($perfil == 2){ // FORNECEDOR
			         
			         if($idFornecedor != 0){
			             
			             $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
			             leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
			             join('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
			             where('fornecedor.id_fornecedor','=',$idFornecedor)->
			             select('fornecedor.id_fornecedor',
			                 'fornecedor.id_usuario',
			                 'fornecedor.nome',
			                 'fornecedor.identificacao',
			                 'fornecedor.endereco',
			                 'fornecedor.telefone',
			                 'fornecedor.cerimonial',
			                 'fornecedor.id_tipo_fornecedor',
			                 'fornecedor.situacao',
			                 'tipo_fornecedor.nome as nomeTipoFornecedor',
			                 'usuario.email',
			                 'midia.caminho_midia')->get();
			             
			         }else{
			             
			             if($idComissao != 0){ // quer dizer que esta atuando como cerimonial para buscar os fornecedores
			                 
			                 $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
			                 leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
			                 join('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
			                 where('fornecedor.situacao','=',1)->
			                 select('fornecedor.id_fornecedor',
			                     'fornecedor.id_usuario',
			                     'fornecedor.nome',
			                     'fornecedor.identificacao',
			                     'fornecedor.endereco',
			                     'fornecedor.telefone',
			                     'fornecedor.cerimonial',
			                     'fornecedor.id_tipo_fornecedor',
			                     'fornecedor.situacao',
			                     'tipo_fornecedor.nome as nomeTipoFornecedor',
			                     'usuario.email',
			                     'midia.caminho_midia')->get();
			                 
			             }else{			             
    			             $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
    			             leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
    			             leftJoin('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
    			             where('fornecedor.id_usuario','=',$iduser)->
    			             select('fornecedor.id_fornecedor',
    			                 'fornecedor.id_usuario',
    			                 'fornecedor.nome',
    			                 'fornecedor.identificacao',
    			                 'fornecedor.endereco',
    			                 'fornecedor.telefone',
    			                 'fornecedor.cerimonial',
    			                 'fornecedor.id_tipo_fornecedor',
    			                 'fornecedor.situacao',
    			                 'tipo_fornecedor.nome as nomeTipoFornecedor',
    			                 'usuario.email',
    			                 'midia.caminho_midia')->get();
			             }
			             
			         }
			         

			         
			     }else if($perfil == 3){ // ADMINISTRADOR
			         
			         $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
			         leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
			         leftJoin('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
			         select('fornecedor.id_fornecedor',
			             'fornecedor.id_usuario',
			             'fornecedor.nome',
			             'fornecedor.identificacao',
			             'fornecedor.endereco',
			             'fornecedor.telefone',
			             'fornecedor.cerimonial',
			             'fornecedor.id_tipo_fornecedor',
			             'fornecedor.situacao',
			             'tipo_fornecedor.nome as nomeTipoFornecedor',
			             'usuario.email',
			             'midia.caminho_midia')->orderBy('situacao', 'asc')->orderBy('fornecedor.nome', 'asc')->get();
			     
			     }
							
			return Response::json(array('status' => true,
			    'Fornecedor' => $retorno->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}
	
	public function getFornecedoresDaComissao($email = '', $iduser = 0, $app = '', $perfil = 0, $idComissao = 0){
	    
	    if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	    
	    if($idComissao == 0){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	    
	    $membro = ComissaoMembros::where('id_comissao','=',$idComissao)->where('email','=',$email)->where('situacao','=',1)->first();
	    
	    if($membro == null && $perfil == 1)
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
            
        $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
        join('comissao_fornecedor', 'comissao_fornecedor.id_fornecedor','=','fornecedor.id_fornecedor')->
        leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
        join('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
        //where('fornecedor.situacao','=',1)->
        where('comissao_fornecedor.id_comissao','=',$idComissao)->
        select('fornecedor.id_fornecedor',
            'fornecedor.id_usuario',
            'fornecedor.nome',
            'fornecedor.identificacao',
            'fornecedor.endereco',
            'fornecedor.telefone',
            'fornecedor.cerimonial',
            'fornecedor.id_tipo_fornecedor',
            'fornecedor.situacao',
            'comissao_fornecedor.situacao as situacaoCF',
            'comissao_fornecedor.atuar_como_presidente',
            'tipo_fornecedor.nome as nomeTipoFornecedor',
            'usuario.email',
            'midia.caminho_midia')->get();
        
        return Response::json(array('status' => true,
            'Fornecedor' => $retorno->toArray()),
            200);
	    
	}
	
    public function solicitarOrcamentoFornecedor(){
        
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $idComissao = Input::get('idComissao');
        $idFornecedor = Input::get('idFornecedor');
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null || $idComissao == null || $idFornecedor == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
		$membro = ComissaoMembros::where('id_comissao','=',$idComissao)->where('email','=',$email)->where('tipo','=',1)->where('situacao','=',1)->first();
        $comissao = Comissao::where('id_comissao','=',$idComissao)->first();
        $fornecedor = Fornecedor::where('id_fornecedor','=',$idFornecedor)->first();
        
        if($comissao == null || ($membro == null && $perfil != 2) || $fornecedor == null)
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        
        if($perfil == 2){
            $cerimonial = Fornecedor::join('comissao_fornecedor','comissao_fornecedor.id_fornecedor','=','fornecedor.id_fornecedor')->
            where('fornecedor.id_usuario','=',$iduser)->
            where('fornecedor.cerimonial','=',1)->
            where('comissao_fornecedor.id_comissao','=',$idComissao)->
            where('comissao_fornecedor.situacao','=',1)->
            where('comissao_fornecedor.atuar_como_presidente','=',1)->first();
            if($cerimonial == null)
                return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
            
		$usuarioFornecedor = Usuario::where('id_usuario','=',$fornecedor->id_usuario)->first();            
        
        $comissaoFornecedor = new ComissaoFornecedor();
        
        try{
            $comissaoFornecedor->id_comissao = $idComissao;
            $comissaoFornecedor->id_fornecedor = $idFornecedor;
            $comissaoFornecedor->situacao = 1;
            $comissaoFornecedor->save();
            
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
            try{
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "A comissão '".$comissao->nome."' está solicitando um orçamento.",'link' => $actual_link), function($message) use ($usuarioFornecedor)
                {
                    $message->to($usuarioFornecedor->email,$usuarioFornecedor->email)->subject(utf8_encode('Projeto Formar - Comissão solicitando Orçamento'));
                });
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            
            return Response::json(array(utf8_encode('Solicitação de orçamento do fornecedor \''.$fornecedor->nome.'\' realizada com sucesso.')), 200);
            
        }catch(Exception $e) {
            
            if(strrpos($e->getMessage(), "1062 Duplicate entry")){
                return Response::json(array(utf8_encode('Já foi solicitado um orçamento para o fornecedor \''.$fornecedor->nome.'\'.')), 409);
            }else{
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
            
        }
        
    }		
    
    public function oferecerServicos(){
        
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $idComissao = Input::get('idComissao');
        $idFornecedor = Input::get('idFornecedor');
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null || $idComissao == null || $idFornecedor == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        $comissao = Comissao::where('id_comissao','=',$idComissao)->first();
        $fornecedor = Fornecedor::where('id_fornecedor','=',$idFornecedor)->first();
        $membro = ComissaoFornecedor::where('id_comissao','=',$idComissao)->where('id_fornecedor','=',$idFornecedor)->first();
        
        if($comissao == null || $fornecedor == null)
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
            
        if($membro != null){
            
            if($membro->situacao == 0){
                //enviar email de novo
                return Response::json(array(utf8_encode('Você já realizou esta ação.')), 409);
            }else if($membro->situacao == 1){
                return Response::json(array(utf8_encode('Você já está incluído na lista da Comissão \''.$comissao->nome.'\'.')), 409);
            }else{
                return Response::json(array(utf8_encode('A comissão \''.$comissao->nome.'\' reprovou sua oferta.')), 409);
            }
                
        }
        
        try{
            $comFor = new ComissaoFornecedor();
            $comFor->id_comissao = $idComissao;
            $comFor->id_fornecedor = $idFornecedor;
            $comFor->situacao = 0;
            $comFor->atuar_como_presidente = 0;
            $comFor->save();
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
        
        try{
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
            $membrosCadastrados = ComissaoMembros::where('id_comissao','=',$comissao->id_comissao)->where('tipo','=',1)->where('situacao','=',1)->get();
            foreach ($membrosCadastrados as $mc){
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O fornecedor '".$fornecedor->nome."' gostaria de oferecer seus serviços.",'link' => $actual_link), function($message) use ($mc)
                {
                    $message->to($mc->email,$mc->email)->subject(utf8_encode('Projeto Formar - Fornecedor oferecendo serviços'));
                });
            }
        }catch(Exception $e) {
            Log::info('Erro ao enviar e-mail');
        }
        
        return Response::json(array(utf8_encode('Seu pedido foi enviado com sucesso.')), 200);
            
    }
    
    public function getFornecedorPorIdUsuario($email = '', $iduser = 0, $app = '', $perfil = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0 || $perfil != 2){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
            
            $retorno = Fornecedor::join('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
            //where('fornecedor.situacao','=',1)->
            where('usuario.id_usuario','=',$iduser)->
            select('fornecedor.id_fornecedor',
                'fornecedor.id_usuario',
                'fornecedor.nome',
                'fornecedor.identificacao',
                'fornecedor.endereco',
                'fornecedor.telefone',
                'fornecedor.cerimonial',
                'fornecedor.id_tipo_fornecedor',
                'fornecedor.situacao',
                'usuario.email')->get();
            
            return Response::json(array('status' => true,
                'Fornecedor' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
    }
	
}