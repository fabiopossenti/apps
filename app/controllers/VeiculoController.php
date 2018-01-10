<?php

class VeiculoController extends Controller {
	
	public function cadastrarVeiculo(){
		
		$placa = Input::get('placa');
		$iduser = Input::get('iduser');
		
		$placa = strtoupper(str_replace("-", "", $placa));
		
		$veiculos = Veiculo::where('id_usuario','=',$iduser)->get();
		if(count($veiculos) == 3)
			return Response::json(array('Somente e permitido cadastrar no maximo 3 veiculos por usuario!'), 409);
		
		$veiculo = new Veiculo();
		
		$veiculo->placa = $placa;
		$veiculo->id_usuario = $iduser;
		
		try{		
			$veiculo->save();
		}catch(Exception $e) {
			Log::info('Erro ao salvar');
			
			if(strrpos($e->getMessage(), "1062 Duplicate entry")){
				return Response::json(array('Ja existe um usuario utilizando a placa '.strtoupper(Input::get('placa'))), 409);				
			}else{
				return Response::json(array('status' => false,
						'error' => $e->getMessage()),// ->errors()),
						409);				
			}

		}
		
	}
	
	public function getVeiculoByIdUsuario($iduser = 0, $email = ''){
	
		if ($iduser == 0 || $email == ''){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
	
		try{
			$usuario = Usuario::where('id_usuario','=',$iduser)->where('email','=',$email)->firstOrFail();
			$veiculos = Veiculo::where('id_usuario','=',$usuario->id_usuario)->get();
			
			return Response::json(array('status' => true,
				'Veiculos' => $veiculos->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
	public function excluirVeiculo(){
		
		$idVeiculo = Input::get('idVeiculo');
		$idUser = Input::get('idUser');
	
		if ($idVeiculo == null || $idUser == null){
			// 406 	Not Acceptable
			return Response::json(array('status' => false),
					406);
		}
	
	
		try{
			$veiculo = Veiculo::where('id_veiculo','=',$idVeiculo)->where('id_usuario','=',$idUser)->firstOrFail();
			$veiculo->delete();
				
			return Response::json(array('status' => true),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
	
}