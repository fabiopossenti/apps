<?php

class FornecedorController extends Controller {
    
    public function cadastrar(){        
        
        $midiaController = new MidiaController();//
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
        $situacao = Input::get('situacao');
        $emailVinculado = Input::get('emailV');
        $file = Input::file('file');
        $idMidia = null;
        $fornecedor = null;
        $usuarioVinculado = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
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
		
			$fornecedor = Fornecedor::where('id_fornecedor', '=', $id)->first();
			
			$fornecedor->id_tipo_fornecedor = $tipoFornecedor;
			$fornecedor->nome = $nome;
			$fornecedor->identificacao = $identificacao;
			$fornecedor->endereco = $endereco;
			$fornecedor->telefone = $telefone;
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
	        
	        
	        if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3)
	            return Response::json(array(utf8_encode('Registro alterado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
	        else
	        	return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);	        		
		
		}else{
		
	        $fornecedor = new Fornecedor();
	        $fornecedor->id_tipo_fornecedor = $tipoFornecedor;
	        $fornecedor->nome = $nome;
			$fornecedor->identificacao = $identificacao;
			$fornecedor->endereco = $endereco;
			$fornecedor->telefone = $telefone;
	        
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
	        
	        if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3)
	            return Response::json(array(utf8_encode('Registro cadastrado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
	        else
	            return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);			
		
		}
 
    }
	
    public function getFornecedores($email = '', $iduser = 0, $app = '', $perfil = 0){
	    
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	
		try{
		    
			     if($perfil == 1){ // ALUNO
			     // where aprovado e faz parte da minha rede
			     
			         $retorno = Fornecedor::leftJoin('midia', 'midia.id_midia','=','fornecedor.id_midia')->
			         leftJoin('tipo_fornecedor', 'tipo_fornecedor.id_tipo_fornecedor','=','fornecedor.id_tipo_fornecedor')->
			         leftJoin('usuario','usuario.id_usuario','=','fornecedor.id_usuario')->
			         select('fornecedor.id_fornecedor',
			             'fornecedor.id_usuario',
			             'fornecedor.nome',
			             'fornecedor.identificacao',
			             'fornecedor.endereco',
			             'fornecedor.telefone',
			             'fornecedor.id_tipo_fornecedor',
			             'fornecedor.situacao',
			             'tipo_fornecedor.nome as nomeTipoFornecedor',
			             'usuario.email',
			             'midia.caminho_midia')->get();
			         
			     }else if($perfil == 2){ // FORNECEDOR
			         
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
			             'fornecedor.id_tipo_fornecedor',
			             'fornecedor.situacao',
			             'tipo_fornecedor.nome as nomeTipoFornecedor',
			             'usuario.email',
			             'midia.caminho_midia')->get();
			         
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
	
}