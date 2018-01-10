<?php

class ProdutoController extends Controller {
    
    public function cadastrar(){
        
        $midiaController = new MidiaController();
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $id = Input::get('id');
        $flexcluir = Input::get('flexcluir');
        $codigo = Input::get('codigo');
        $descricao = Input::get('descricao_r');
        $qtdEstoqueE = Input::get('qtdEstoqueE');
        $qtdEstoqueD = Input::get('qtdEstoqueD');
        $qtdMinimaAlerta = Input::get('qtdMinimaAlerta');
        $vlrCusto = Input::get('vlrCusto');
        $vlrVenda = Input::get('vlrVenda');
        $file = Input::file('file');
        $idMidia = null;
        $produto = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if($vlrCusto != null){
            $vlrCusto = str_replace(".", "", $vlrCusto);
            $vlrCusto = str_replace(",", ".", $vlrCusto);
        }

        if($vlrVenda != null){
        	$vlrVenda = str_replace(".", "", $vlrVenda);
            $vlrVenda = str_replace(",", ".", $vlrVenda);
        }
        
        if($id != null)
            $produto = Produto::where('id_produto', '=', $id)->first();
        
        if($file != null || $flexcluir != null){
            if($produto != null)
                $idMidia = $midiaController->upload($file, $iduser, $produto->id_midia, $flexcluir == null,$app);
            else
                $idMidia = $midiaController->upload($file, $iduser, null, $flexcluir == null,$app);
            if($idMidia == -1)
                return Response::json(array(utf8_encode('Tamanho máximo de bytes excedido do arquivo. Tamanho máximo: 1MB')), 409);
        }
        
        if($flexcluir != null){
            try{
                $produto->delete();
            }catch(Exception $e) {
                Log::info('Erro ao excluir');
                return Response::json(array('status' => false,'error' => $e->getMessage()),409);
            }
            return Response::json(array(utf8_encode('Registro removido com sucesso!')), 200);
        }
        
		if($id != null){
		
			$produto->codigo = $codigo;
			$produto->descricao = $descricao;
			$produto->qtd_estoque_esquerdo = $qtdEstoqueE;
			$produto->qtd_estoque_direito = $qtdEstoqueD;
			$produto->qtd_minima_alerta = $qtdMinimaAlerta;
			$produto->vlr_custo = $vlrCusto;
			$produto->vlr_venda = $vlrVenda;
			
			if($idMidia)
			    $produto->id_midia = $idMidia;

	        try{	            
	            $produto->update();
	        }catch(Exception $e) {
	            Log::info('Erro ao editar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
			return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);	        		
		
		}else{
		
	        $produto = new Produto();
	        $produto->codigo = $codigo;
	        $produto->descricao = $descricao;
	        $produto->id_usuario = $iduser;
			$produto->qtd_estoque_esquerdo = $qtdEstoqueE;
			$produto->qtd_estoque_direito = $qtdEstoqueD;
			$produto->qtd_minima_alerta = $qtdMinimaAlerta;
			$produto->vlr_custo = $vlrCusto;
			$produto->vlr_venda = $vlrVenda;	        
	        
	        if($idMidia)
	            $produto->id_midia = $idMidia;
			
	        try{
	            $produto->save();
	        }catch(Exception $e) {
	            Log::info('Erro ao salvar');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
	        return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);			
		
		}
 
    }
    
    public function getProdutos($email = '', $iduser = 0, $app = ''){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
	
		try{
		    $produtos = Produto::leftJoin('midia', 'midia.id_midia','=','produto.id_midia')->
		    select('produto.id_produto',
		        'produto.id_usuario',
		        'produto.codigo',
		        'produto.descricao',
		        'produto.qtd_estoque_esquerdo',
		        'produto.qtd_estoque_direito',
		        'produto.qtd_minima_alerta',
		        'produto.vlr_custo',
		        'produto.vlr_venda',
		        'midia.caminho_midia')->get();
			
			return Response::json(array('status' => true,
			    'Produto' => $produtos->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
}