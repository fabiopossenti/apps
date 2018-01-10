<?php

use Symfony\Component\Console\Input\ArrayInput;
class EventoVeiculoController extends Controller {

	public function cadastrarEventoVeiculo(){
		
		$placa = Input::get('placa');
		$iduser = Input::get('iduser');
		$eventos = Input::get('eventos');
		
		if ($iduser == 0 || $placa == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
		
		$veiculos = Veiculo::where('id_usuario','=',$iduser)->get();
		foreach ($veiculos as $veiculo){
			if($veiculo->placa == $placa)
				return Response::json(array('Nao e permitido informar para seu proprio carro! '.Input::get('email')), 409);
		}
		
		
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->firstOrFail();
			
			$placa = strtoupper(str_replace("-", "", $placa));
			
			if($eventos == null)
				return Response::json(array('Informe pelo menos um evento! '), 409);
			
			$eventosStr = "";
			
			$data;
			
			foreach ($eventos as $evento)
			{
				$eventoVeiculo = new EventoVeiculo();
			
				$eventoVeiculo->placa = $placa;			
				$eventoVeiculo->id_usuario_informante = $iduser;
				$eventoVeiculo->id_evento = $evento;
				
				$ev = Evento::where('id_evento','=',$evento)->select('descricao')->get();
				
				$eventosStr = $eventosStr . $ev->first()->descricao . ', ';
			
				$eventoVeiculo->save();
				
				$data = $eventoVeiculo->created_at;
			}
			
 			try{
 				
 				$countAvaliacaoPositiva = UsuarioAvaliacao::join('evento_veiculo', 'evento_veiculo.id_evento_veiculo','=','usuario_avaliacao.id_evento_veiculo')->where('usuario_avaliacao.avaliacao','=',1)->where('evento_veiculo.id_usuario_informante','=',$iduser)->count('id_usuario_avaliacao');
 					
 				$countAvaliacaoNegativa = UsuarioAvaliacao::join('evento_veiculo', 'evento_veiculo.id_evento_veiculo','=','usuario_avaliacao.id_evento_veiculo')->where('usuario_avaliacao.avaliacao','=',2)->where('evento_veiculo.id_usuario_informante','=',$iduser)->count('id_usuario_avaliacao');
 				
 				$usuario2 = Usuario::join('veiculo','veiculo.id_usuario','=','usuario.id_usuario')->where('veiculo.placa','=',Input::get('placa'))->select('email')->get();
 				
 				if($usuario2->first() != null){
	 				if($countAvaliacaoPositiva+$countAvaliacaoNegativa == 0){
	 					Mail::send('alertmail', array('key' => 'value','placa' => $placa,'data' => $data,'evento' => $eventosStr, 'avaliacao' => 'N/A','totalAvaliacao' => '0'), function($message)
	 					{
	 						$usuario2 = Usuario::join('veiculo','veiculo.id_usuario','=','usuario.id_usuario')->where('veiculo.placa','=',Input::get('placa'))->select('email')->get();
							$message->to($usuario2->first()->email,$usuario2->first()->email)->subject('Flanelinha Online - Alerta');
	 						
	 					}); 					
	 				}else{
	 					Mail::send('alertmail', array('key' => 'value','placa' => $placa,'data' => $data,'evento' => $eventosStr, 'avaliacao' => number_format((($countAvaliacaoPositiva/($countAvaliacaoPositiva+$countAvaliacaoNegativa))*100), 0),'totalAvaliacao' => number_format($countAvaliacaoPositiva+$countAvaliacaoNegativa,0,',','.')), function($message)
	 					{
	 						$usuario2 = Usuario::join('veiculo','veiculo.id_usuario','=','usuario.id_usuario')->where('veiculo.placa','=',Input::get('placa'))->select('email')->get();
							$message->to($usuario2->first()->email,$usuario2->first()->email)->subject('Flanelinha Online - Alerta');
	 					});
	 				} 				
 				}
 				

 			}catch(Exception $e1) {
 				return Response::json(array('status' => false,
 						'error' => $e1->getMessage()),// ->errors()),
 						406);
 			}
			
			return Response::json(array('status' => true), 200);
		
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}		
		
	}
	
	public function getEventosRecentesByIdUsuario($iduser = 0, $email = ''){
	
		if ($iduser == 0 || $email == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
			//$veiculos = Veiculo::where('id_usuario','=',$usuario->id)->get();
			
			//$placas = array();	
			
			//$cont = 0;
			
			//foreach ($veiculos as $veiculo)
			//{				
				//$placas[$count] = $veiculo->placa;
				//$count = $count + 1;
			//}
			
			$eventosRecentes = EventoVeiculo::join('veiculo', 'veiculo.placa','=','evento_veiculo.placa')->
				join('evento', 'evento.id_evento','=','evento_veiculo.id_evento')->
				where('veiculo.id_usuario','=',$usuario->id_usuario)->
				whereNull('data_leitura')->
				select('evento_veiculo.id_evento_veiculo',
						'evento_veiculo.id_usuario_informante',
						'evento_veiculo.id_evento',
						'evento_veiculo.placa',
						'evento_veiculo.observacao',
						'evento_veiculo.caminho_anexo',
						'evento_veiculo.data_leitura',
						'evento_veiculo.updated_at',
						'evento_veiculo.created_at',
						'evento.descricao')->
				orderBy('evento_veiculo.created_at', 'asc')->get();
				
			return Response::json(array('status' => true,
					'EventosRecentes' => $eventosRecentes->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
	public function getEventoRecenteById($iduser = 0, $email = '', $idEventoVeiculo = 0){
	
		if ($iduser == 0 || $idEventoVeiculo == 0 || $email == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
			//$veiculos = Veiculo::where('id_usuario','=',$usuario->id)->get();
				
			//$placas = array();
				
			//$cont = 0;
				
			//foreach ($veiculos as $veiculo)
			//{
			//$placas[$count] = $veiculo->placa;
			//$count = $count + 1;
			//}
				
			$eventoRecente = EventoVeiculo::join('veiculo', 'veiculo.placa','=','evento_veiculo.placa')->
			join('evento', 'evento.id_evento','=','evento_veiculo.id_evento')->
			where('evento_veiculo.id_evento_veiculo','=',$idEventoVeiculo)->
			select('evento_veiculo.id_evento_veiculo',
					'evento_veiculo.id_usuario_informante',
					'evento_veiculo.id_evento',
					'evento_veiculo.placa',
					'evento_veiculo.observacao',
					'evento_veiculo.caminho_anexo',
					'evento_veiculo.data_leitura',
					'evento_veiculo.updated_at',
					'evento_veiculo.created_at',
					'evento.descricao')->get();
			
 			$countAvaliacaoPositiva = UsuarioAvaliacao::join('evento_veiculo', 'evento_veiculo.id_evento_veiculo','=','usuario_avaliacao.id_evento_veiculo')->where('usuario_avaliacao.avaliacao','=',1)->where('evento_veiculo.id_usuario_informante','=',$eventoRecente->first()->id_usuario_informante)->count('id_usuario_avaliacao');
			
 			$countAvaliacaoNegativa = UsuarioAvaliacao::join('evento_veiculo', 'evento_veiculo.id_evento_veiculo','=','usuario_avaliacao.id_evento_veiculo')->where('usuario_avaliacao.avaliacao','=',2)->where('evento_veiculo.id_usuario_informante','=',$eventoRecente->first()->id_usuario_informante)->count('id_usuario_avaliacao');
	
 			if($countAvaliacaoPositiva+$countAvaliacaoNegativa == 0){
 				return Response::json(array('status' => true,
 						'EventosRecentes' => $eventoRecente->toArray(),
 						'Avaliacao' => 'N/A',
 						'TotalAvaliacao' => 'Nenhum'),
 						200); 				
 			}else{
 				return Response::json(array('status' => true,
 						'EventosRecentes' => $eventoRecente->toArray(),
 						'Avaliacao' => number_format((($countAvaliacaoPositiva/($countAvaliacaoPositiva+$countAvaliacaoNegativa))*100), 0)  ,
 						'TotalAvaliacao' => number_format($countAvaliacaoPositiva+$countAvaliacaoNegativa,0,',','.')),
 						200); 				
 			}
 			

	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}

	public function confirmarLeitura(){
	
		$email = Input::get('email');
		$iduser = Input::get('iduser');
		$idEventoVeiculo = Input::get('idEventoVeiculo');
		$avaliacao = Input::get('avaliacao');		
	
		if ($iduser == 0 || $email == '' || $idEventoVeiculo == 0 || $avaliacao == 0){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{

			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
			
			$eventoVeiculo = EventoVeiculo::where('id_evento_veiculo','=',$idEventoVeiculo)->firstOrFail();
			
			$eventoVeiculo->data_leitura = new DateTime();
			
			$eventoVeiculo->update();
			
			$usuarioAvaliacao = new UsuarioAvaliacao();
			$usuarioAvaliacao->id_evento_veiculo = $idEventoVeiculo;
			$usuarioAvaliacao->avaliacao = $avaliacao;
			$usuarioAvaliacao->save();
				
			return Response::json(array('status' => true), 200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}

	public function getMeusEventosByIdUsuario($iduser = 0, $email = ''){
	
		if ($iduser == 0 || $email == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
				
			$eventosRecentes = EventoVeiculo::join('veiculo', 'veiculo.placa','=','evento_veiculo.placa')->
			join('evento', 'evento.id_evento','=','evento_veiculo.id_evento')->
			where('veiculo.id_usuario','=',$usuario->id_usuario)->
			select('evento_veiculo.id_evento_veiculo',
					'evento_veiculo.id_usuario_informante',
					'evento_veiculo.id_evento',
					'evento_veiculo.placa',
					'evento_veiculo.observacao',
					'evento_veiculo.caminho_anexo',
					'evento_veiculo.data_leitura',
					'evento_veiculo.updated_at',
					'evento_veiculo.created_at',
					'evento.descricao')->
			orderBy('evento_veiculo.created_at', 'desc')->get();
	
			return Response::json(array('status' => true,
					'EventoVeiculo' => $eventosRecentes->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
	public function getMeusEventosEnviadosByIdUsuario($iduser = 0, $email = ''){
	
		if ($iduser == 0 || $email == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
	
			$eventosRecentes = EventoVeiculo::join('evento', 'evento.id_evento','=','evento_veiculo.id_evento')->
			where('evento_veiculo.id_usuario_informante','=',$usuario->id_usuario)->
			select('evento_veiculo.id_evento_veiculo',
					'evento_veiculo.id_usuario_informante',
					'evento_veiculo.id_evento',
					'evento_veiculo.placa',
					'evento_veiculo.observacao',
					'evento_veiculo.caminho_anexo',
					'evento_veiculo.data_leitura',
					'evento_veiculo.updated_at',
					'evento_veiculo.created_at',
					'evento.descricao')->
					orderBy('evento_veiculo.created_at', 'desc')->get();
	
			return Response::json(array('status' => true,
					'EventoVeiculo' => $eventosRecentes->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
}