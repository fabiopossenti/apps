<?php

/*
 |--------------------------------------------------------------------------
 | Application Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register all of the routes for an application.
 | It's a breeze. Simply tell Laravel the URIs it should respond to
 | and give it the Closure to execute when that URI is requested.
 |
 */

//COMUM A TODOS
Route::post("isLogged",'UsuarioController@postIsLogged');
Route::delete("login",'UsuarioController@deleteLogout');
Route::post("login",'UsuarioController@postLogin');
Route::post("cadastrarUsuario", 'UsuarioController@cadastrarUsuario');
Route::post("esqueciMinhaSenha", 'UsuarioController@esqueciMinhaSenha');
Route::post("esqueciMinhaSenhaConfirmacao", 'UsuarioController@esqueciMinhaSenhaConfirmacao');
Route::post("esqueciMinhaSenhaAtualizacaoSenha", 'UsuarioController@esqueciMinhaSenhaAtualizacaoSenha');
Route::post("isUsuarioExistente", 'UsuarioController@isUsuarioExistente');
Route::post("cadastrarUsuarioFacebook", 'UsuarioController@cadastrarUsuarioFacebook');
Route::post("upload", 'HomeController@upload');
Route::get('obterImagens', 'HomeController@obterImagens');
Route::post("cadastrarUsuarioConvite", 'UsuarioConviteController@cadastrar');
Route::get('amigos/{email}/{iduser}/{app}', 'UsuarioConviteController@getUsuariosPorIdUsuario');
Route::post("confirmarAmizade", 'UsuarioConviteController@confirmarAmizade');
Route::post("reenviarConvite", 'UsuarioConviteController@reenviarConvite');
Route::post("validarAcessoTela", 'UsuarioController@validarAcessoTela');

//APP FLANELINHA ONLINE
// Route::get('flanelinhaonline', function(){Redirect::to('login.html');});
Route::get('alert.mail', function(){return View::make('alertmail');});
Route::post("cadastrarVeiculo", 'VeiculoController@cadastrarVeiculo');
Route::get('veiculo/{iduser}/{email}', 'VeiculoController@getVeiculoByIdUsuario');
Route::post("excluirVeiculo", 'VeiculoController@excluirVeiculo');
Route::get('eventos', 'EventoController@getEventos');
Route::post("informar", 'EventoVeiculoController@cadastrarEventoVeiculo');
Route::get('eventosRecentes/{iduser}/{email}', 'EventoVeiculoController@getEventosRecentesByIdUsuario');
Route::get('eventoRecente/{iduser}/{email}/{idEventoVeiculo}', 'EventoVeiculoController@getEventoRecenteById');
Route::post("confirmarLeitura", 'EventoVeiculoController@confirmarLeitura');
Route::get('carregarMeusEventos/{iduser}/{email}', 'EventoVeiculoController@getMeusEventosByIdUsuario');
Route::get('carregarMeusEventosEnviados/{iduser}/{email}', 'EventoVeiculoController@getMeusEventosEnviadosByIdUsuario');

// APP PROJETO FORMAR
// Route::get('projetoformar', function(){Redirect::to('login.html');});
Route::get('obterTipoFornecedor/{email}/{iduser}/{app}', 'TipoFornecedorController@getTipos');
Route::post("cadastrarTipoFornecedor", 'TipoFornecedorController@cadastrar');
Route::get('obterFornecedor/{email}/{iduser}/{app}/{perfil}/{idComissao}/{idFornecedor}', 'FornecedorController@getFornecedores');
Route::get('obterFornecedorDaComissao/{email}/{iduser}/{app}/{perfil}/{idComissao}', 'FornecedorController@getFornecedoresDaComissao');
Route::get('obterFornecedorPorId/{email}/{iduser}/{app}/{perfil}', 'FornecedorController@getFornecedorPorIdUsuario');
Route::post("cadastrarFornecedor", 'FornecedorController@cadastrar');
Route::get('obterPerfis/{email}/{iduser}/{app}', 'UsuarioController@carregarPerfis');
Route::post("incluirPerfil", 'UsuarioController@incluirPerfil');
Route::get('obterComissoes/{email}/{iduser}/{app}/{perfil}/{busca}', 'ComissaoController@getComissoes');
Route::post("cadastrarComissao", 'ComissaoController@cadastrar');
Route::get('obterAluno/{email}/{iduser}/{app}/{perfil}', 'AlunoController@getAlunos');
Route::post("cadastrarAluno", 'AlunoController@cadastrar');
Route::get('obterComissao/{email}/{iduser}/{app}/{perfil}/{id}', 'ComissaoController@getComissao');
Route::get('obterComissaoDoFornecedor/{email}/{iduser}/{app}/{perfil}/{idComissao}/{idFornecedor}', 'ComissaoController@getComissaoDoFornecedor');
Route::post("ingressarComissao", 'ComissaoController@ingressarComissao');
Route::get('obterComissaoPresidente/{email}/{iduser}/{app}/{perfil}/{emailCriador}/{id}', 'ComissaoController@getComissaoPresidente');
Route::post("solicitarOrcamentoFornecedor", 'FornecedorController@solicitarOrcamentoFornecedor');
Route::post("concederPermissaoPresidente", 'ComissaoController@concederPermissaoPresidenteCerimonial');
Route::post("removerPermissaoPresidente", 'ComissaoController@removerPermissaoPresidenteCerimonial');
Route::post("oferecerServicos", 'FornecedorController@oferecerServicos');
Route::post("incluirFornecedorNaLista", 'ComissaoController@incluirFornecedorNaLista');
Route::post("removerFornecedorDaLista", 'ComissaoController@removerFornecedorDaLista');

//APP ONCECETA
// Route::get('ondeceta', function(){Redirect::to('login.html');});
Route::post("solicitarPosicao", 'PosicaoController@cadastrarSolicitacaoPosicao');
Route::get('eventosRecentes/{iduser}', 'PosicaoController@getEventosRecentesByIdUsuario');
Route::post("confirmarSolicitacaoPosicao", 'PosicaoController@confirmarSolicitacaoPosicao');

//APP RCPECAS
// Route::get('rcpecas', function(){return Redirect::to('rcpecas/login.html');});
Route::get('obterProduto/{email}/{iduser}/{app}', 'ProdutoController@getProdutos');
Route::post("cadastrarProduto", 'ProdutoController@cadastrar');
Route::post("cadastrarNovoPedido", 'PedidoController@cadastrar');
Route::get('obterPedido/{email}/{iduser}/{app}', 'PedidoController@getPedidos');

//OUTROS/EM TESTE
Route::get('/', function()
{
    return View::make('hello');
});

//Route::get('loginFacebook', 'UsuarioController@getLoginFacebook');

Route::get('login/fb', function() {
    $facebook = new Facebook(Config::get('facebook'));
    $params = array(
        'redirect_uri' => url('/login/fb/callback'),
        'scope' => 'email',
    );
    
    return Response::json(array('url' => $facebook->getLoginUrl($params)),200);
    
    //return Redirect::to($facebook->getLoginUrl($params));
});
    
    Route::get('login/fb/callback', function() {
        $code = Input::get('code');
        if (strlen($code) == 0) return Redirect::to('/')->with('message', 'There was an error communicating with Facebook');
        
        $facebook = new Facebook(Config::get('facebook'));
        $uid = $facebook->getUser();
        
        if ($uid == 0) return Redirect::to('/')->with('message', 'There was an error');
        
        $me = $facebook->api('/me');
        
        $usuarioController = new UsuarioController();
        $usuarioExistente = $usuarioController->isUsuarioExternoExistente($me['email']);
        
        if($usuarioExistente){
            return Redirect::to('inicio.html?email='.$me['email'].'&fb=true&iduser='.$usuarioExistente);
        }else{
            $userFace = $usuarioController->cadastrarUsuarioExterno($me['email']);
            if($userFace){
                return Redirect::to('inicio.html?email='.$me['email'].'&fb=true&iduser='.$userFace);
            }else{
                return Redirect::to('login.html?email='.$me['email'].'&fb=true&msg=usuarioExistente');
            }
        }
        
        //echo $usuarioExistente;
        
        //return Redirect::to('inicio.html?email='.$me['email'].'&fb=true');
        
        //return Response::json(array('usuario' => $me),200);
        
        //dd($me);
    });
        
        Route::post("loginGoogle",'UsuarioController@postLoginGoogle');