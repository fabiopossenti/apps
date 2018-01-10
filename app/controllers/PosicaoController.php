<?php

class PosicaoController extends Controller {
    
    public function cadastrarSolicitacaoPosicao(){
        
        $iduser = Input::get('iduser');
        $idUsuarioSolicitado = Input::get('idUsuarioSolicitado');
        
        $solicitacao = new SolicitacaoPosicao();
        $solicitacao->id_usuario = $iduser;
        $solicitacao->id_usuario_solicitado = $idUsuarioSolicitado;
        try{
            $solicitacao->save();
        }catch(Exception $e) {
            Log::info('Erro ao salvar');
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
        
        $usuario = Usuario::where('id_usuario','=',$iduser)->first();
        
        try{
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/ondeceta/login.html";
            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." está solicitando sua localização atual. Clique no link abaixo caso deseje compartilhar sua localização.",'link' => $actual_link), function($message)
	        {
	            $usuarioSolicitado = Usuario::where('id_usuario','=',Input::get('idUsuarioSolicitado'))->first();
	            $message->to($usuarioSolicitado->email,$usuarioSolicitado->email)->subject(utf8_encode('Tem alguém querendo saber se você já está chegando...'));
	        });
        }catch(Exception $e) {
            Log::info('Erro ao enviar e-mail');
        }        
        
        return Response::json(array(utf8_encode('Solicitação realizada com sucesso!')), 200);	
        
    }
    
	public function getEventosRecentesByIdUsuario($iduser = 0){
	
		if ($iduser == 0){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{
		
			$solicitacoes = SolicitacaoPosicao::join('usuario', 'usuario.id_usuario','=','solicitacao_posicao.id_usuario')->
						where('id_usuario_solicitado','=',$iduser)->
						whereNull('situacao')->
						select(DB::raw('max(id_solicitacao_posicao) as id_solicitacao_posicao, usuario.id_usuario as id_usuario_solicitante, usuario.email as email, max(solicitacao_posicao.created_at) as created_at'))->
						groupBy('solicitacao_posicao.id_usuario')->
						orderBy('id_solicitacao_posicao','asc')->
						get();
				
			return Response::json(array('status' => true,
					'EventosRecentes' => $solicitacoes->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}
	
	public function confirmarSolicitacaoPosicao(){
        
        $iduser = Input::get('iduser');
        $idSolicitante = Input::get('idSolicitante');
        $email = Input::get('email');
        $idSolicitacaoPosicao = Input::get('idSolicitacaoPosicao');
        $lat = Input::get('lat');
        $lon = Input::get('lon');
        $situacao = Input::get('situacao');
        $tipoTransporte = Input::get('tipoTransporte');
        
        try{
        	if($situacao == 1){ // aceitou
            	SolicitacaoPosicao::where('id_usuario_solicitado','=',$iduser)->where('id_usuario','=',$idSolicitante)->
                    whereNull('situacao')->update(['situacao' => 1, 'lat' => $lat, 'lon' => $lon, 'tipo_transporte' => $tipoTransporte]);
        	}else{
            	SolicitacaoPosicao::where('id_usuario_solicitado','=',$iduser)->where('id_usuario','=',$idSolicitante)->
                    whereNull('situacao')->update(['situacao' => 2]);
        	}
        }catch(Exception $e) {
            Log::info('Erro ao atualizar');
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
        
        if($situacao == 1){
        
        	$usuario = Usuario::where('id_usuario','=',$iduser)->first();
        
	        try{
	            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/ondeceta/login.html";
	            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." compartilhou a localização atual dele. Clique no link abaixo para visualizar.",'link' => $actual_link), function($message)
		        {
		            $usuarioSolicitante = Usuario::where('id_usuario','=',Input::get('idSolicitante'))->first();
		            $message->to($usuarioSolicitante->email,$usuarioSolicitante->email)->subject(utf8_encode('Alguém está compartilhando localização com você.'));
		        });
	        }catch(Exception $e) {
	            Log::info('Erro ao enviar e-mail');
	        }	        
        
        	return Response::json(array(utf8_encode('Localização foi enviada com sucesso!')), 200);
        }else{	
        	return Response::json(array(utf8_encode('Localização não enviada!')), 200);
        }
        
    }
    
}