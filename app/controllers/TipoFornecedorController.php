<?php

class TipoFornecedorController extends Controller {
    
    public function cadastrar(){
        
        $midiaController = new MidiaController();
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $id = Input::get('id');
        $flexcluir = Input::get('flexcluir');
        $nome = Input::get('nome_r');
        $file = Input::file('file');
        $idMidia = null;
        $tipoFornecedor = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null || $perfil != 3){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if($id != null)
            $tipoFornecedor = TipoFornecedor::where('id_tipo_fornecedor', '=', $id)->first();
        
        if($file != null || $flexcluir != null){
            if($tipoFornecedor != null)
                $idMidia = $midiaController->upload($file, $iduser, $tipoFornecedor->id_midia, $flexcluir == null,$app);
            else
                $idMidia = $midiaController->upload($file, $iduser, null, $flexcluir == null,$app);
            if($idMidia == -1)
                return Response::json(array(utf8_encode('Tamanho máximo de bytes excedido do arquivo. Tamanho máximo: 1MB')), 409);
        }
        
        if($flexcluir != null){
            try{
                $tipoFornecedor->delete();
            }catch(Exception $e) {
                Log::info('Erro ao excluir');
                return Response::json(array('status' => false,'error' => $e->getMessage()),409);
            }
            return Response::json(array(utf8_encode('Registro removido com sucesso!')), 200);
        }
        
		if($id != null){
		
			$tipoFornecedor->nome = $nome;
			
			if($idMidia)
			    $tipoFornecedor->id_midia = $idMidia;

	        try{	            
	            $tipoFornecedor->update();
	        }catch(Exception $e) {
	            Log::info('Erro ao editar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
			return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);	        		
		
		}else{
		
	        $tipoFornecedor = new TipoFornecedor();
	        $tipoFornecedor->nome = $nome;
	        $tipoFornecedor->id_usuario = $iduser;
	        
	        if($idMidia)
	            $tipoFornecedor->id_midia = $idMidia;
			
	        try{
	            $tipoFornecedor->save();
	        }catch(Exception $e) {
	            Log::info('Erro ao salvar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
	        return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);			
		
		}
 
    }
    
    public function getTipos($email = '', $iduser = 0, $app = ''){
	    
	    if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	
		try{
		    $tipos = TipoFornecedor::leftJoin('midia', 'midia.id_midia','=','tipo_fornecedor.id_midia')->
		    select('tipo_fornecedor.id_tipo_fornecedor',
		        'tipo_fornecedor.id_usuario',
		        'tipo_fornecedor.nome',
		        'midia.caminho_midia')->get();
			
			return Response::json(array('status' => true,
			    'TipoFornecedor' => $tipos->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
}