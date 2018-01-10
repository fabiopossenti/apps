<?php

class PedidoController extends Controller {
    
    public function cadastrar(){
        
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $id = Input::get('id');
        $flexcluir = Input::get('flexcluir');
        $produtos = Input::get('itens');
        $pedido = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if($id != null)
            $pedido = Pedido::where('id_pedido', '=', $id)->first();
        
        if($flexcluir != null){
            try{
                $pedido->delete();
            }catch(Exception $e) {
                Log::info('Erro ao excluir');
                return Response::json(array('status' => false,'error' => $e->getMessage()),409);
            }
            return Response::json(array(utf8_encode('Registro removido com sucesso!')), 200);
        }
        
		if($id != null){
		
	        		
		
		}else{
	        $pedido = new Pedido();
	        try{
	            $pedido->save();
	         	foreach ($produtos as $p){
	         	    
	            	$pedidoProduto = new PedidoProduto();
	            	$pedidoProduto->id_pedido = $pedido->id_pedido;
	            	$pedidoProduto->id_produto = $p["id_produto"];
	            	$pedidoProduto->qtd_esquerdo = $p["qtd_esquerdo_"];
	            	$pedidoProduto->qtd_direito = $p["qtd_direito_"];
	            	$pedidoProduto->vlr_venda = str_replace(".", "", $p["vlr_venda_"]);
	            	$pedidoProduto->vlr_venda = str_replace(",", ".", $pedidoProduto->vlr_venda);
			        try{
			            $pedidoProduto->save();
				        try{
				            $produto = Produto::where('id_produto','=',$pedidoProduto->id_produto)->first();
				            $produto->qtd_estoque_esquerdo = $produto->qtd_estoque_esquerdo - $p["qtd_esquerdo_"];
				            $produto->qtd_estoque_direito = $produto->qtd_estoque_direito - $p["qtd_direito_"];
				            $produto->update();
				        }catch(Exception $e) {
				            Log::info('Erro ao atualizar produto');
				            return Response::json(array('status' => false,
				                'error' => $e->getMessage()),// ->errors()),
				                409);
				        }
			        }catch(Exception $e) {
			            Log::info('Erro ao salvar pedido_produto');
			            return Response::json(array('status' => false,
			                'error' => $e->getMessage()),// ->errors()),
			                409);
			        }
	         	}
	        }catch(Exception $e) {
	            Log::info('Erro ao salvar pedido');
	            return Response::json(array('status' => false,
	                'error' => $e->getMessage()),// ->errors()),
	                409);
	        }
	        
	        return Response::json(array(utf8_encode('Pedido cadastrado com sucesso!')), 200);			
		
		}
 
    }
    
    public function getPedidos($email = '', $iduser = 0, $app = ''){
	    
	    if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
	        return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	    }
	
		try{
		    $pedidos = Pedido::select('id_pedido',
		        'updated_at',
		        'created_at')->orderBy('updated_at', 'desc')->get();
		    
		    foreach ($pedidos as $pedido){
		        $itens = PedidoProduto::Join('produto','produto.id_produto','=','pedido_produto.id_produto')->
		        where('pedido_produto.id_pedido','=',$pedido->id_pedido)->
		        select('produto.descricao',
		            'produto.id_produto',
		            'produto.codigo',
		            'pedido_produto.qtd_esquerdo',
		            'pedido_produto.qtd_direito',
		            'pedido_produto.vlr_venda')->get();
		        
		        $itensStr = '<table width="100%" bordercolor="#cccccc" style="font-size: 12px;border: 1px; border-color: #cccccc; border-style: dashed" cellpadding="0px" cellspacing="0px"><tr><td style="border-right: 1px; border-right-color: #cccccc; border-right-style: dashed"></td><td style="width: 100px;border-right: 1px; border-right-color: #cccccc; border-right-style: dashed">Qtd. E</td><td style="width: 100px;border-right: 1px; border-right-color: #cccccc; border-right-style: dashed">Qtd. D</td><td style="width: 150px">Valor</td></tr>';
		        $qtdItens = 0;
		        $vlrTotal = 0;
		        
		        foreach ($itens as $item){
		            
		            $itensStr .= '<tr><td style="border-right: 1px; border-right-color: #cccccc; border-right-style: dashed">'.$item->descricao.'</td><td style="border-right: 1px; border-right-color: #cccccc; border-right-style: dashed">'.$item->qtd_esquerdo.'</td><td style="border-right: 1px; border-right-color: #cccccc; border-right-style: dashed">'.$item->qtd_direito.'</td><td>'.'R$ ' . number_format($item->vlr_venda, 2, ',', '.').'</td></tr>';
		            
		            $qtdItens = $qtdItens + $item->qtd_esquerdo + $item->qtd_direito;
		            $vlrTotal = $vlrTotal + ($item->vlr_venda * ($item->qtd_esquerdo + $item->qtd_direito));
		            
		        }

		        $itensStr .= '</table>';
		        
		        $pedido->itens = $itensStr;
		        $pedido->qtdItens = $qtdItens;
		        $pedido->vlrTotal = number_format($vlrTotal, 2, '.', '');
		        $pedido->itens_ = $itens;
		        
		    }
			
			return Response::json(array('status' => true,
			    'Pedido' => $pedidos->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
}