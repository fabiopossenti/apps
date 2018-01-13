<?php

function validarEmail2($email){
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

class ComissaoController extends Controller {
    
    public function cadastrar(){
        
        $midiaController = new MidiaController();
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $id = Input::get('id');
        $flexcluir = Input::get('flexcluir');
        $nome = Input::get('nome_r');
        $instituicao = Input::get('instituicao_r');
        $curso = Input::get('curso_r');
        $telefone = Input::get('telefone_r');
        $tipoMembros = Input::get('tipoMembros');
        $membros = Input::get('membros');
        $situacoes = Input::get('situacoes');
        $emailVinculado = Input::get('emailV');
        $file = Input::file('file');
        $idMidia = null;
        $comissao = null;
        $usuarioVinculado = null;
        
        $tipoMembros = explode(",", $tipoMembros);
        $membros = explode(",", $membros);
        $situacoes = explode(",", $situacoes);
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        foreach ($membros as $m){
        	if(!validarEmail2($m)){
        		return Response::json(array(utf8_encode('E-mail '.$m.' Inválido!')),401);
        	}
        }
        
        if($id != null)
            $comissao = Comissao::where('id_comissao', '=', $id)->first();
            
            if($file != null || $flexcluir != null){
                if($comissao != null)
                    $idMidia = $midiaController->upload($file, $iduser, $comissao->id_midia, $flexcluir == null,$app);
                    else
                        $idMidia = $midiaController->upload($file, $iduser, null, $flexcluir == null,$app);
                        if($idMidia == -1)
                            return Response::json(array(utf8_encode('Tamanho máximo de bytes excedido do arquivo. Tamanho máximo: 1MB')), 409);
            }
            
            if($flexcluir != null){
                try{
                    $comissao->delete();
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
                        $comissao = Comissao::where('id_comissao', '=', $id)->first();
                        if($comissao->id_usuario != $usuarioVinculado->id_usuario){
                            $forn = Comissao::where('id_usuario','=',$usuarioVinculado->id_usuario)->first();
                            if($forn != null){
                                return Response::json(array(utf8_encode('O e-mail vinculado informado já possui uma Comissão!')), 409);
                            }
                        }
                    }else{
                        $forn = Comissao::where('id_usuario','=',$usuarioVinculado->id_usuario)->first();
                        if($forn != null){
                            return Response::json(array(utf8_encode('O e-mail vinculado informado já possui uma Comissão!')), 409);
                        }
                    }
                }
            }
            
            if($id != null){
                
                $novoMembro = false;
                
                $comissao = Comissao::where('id_comissao', '=', $id)->first();
                
                $comissao->nome = $nome;
                $comissao->instituicao = $instituicao;
                $comissao->curso = $curso;
                $comissao->telefone = $telefone;
                if($perfil == 3){
                    if($usuarioVinculado != null)
                        $comissao->id_usuario = $usuarioVinculado->id_usuario;
                }
                
                if($idMidia)
                    $comissao->id_midia = $idMidia;
                    
                    try{
                        $comissao->update();
                        
                        $count = 0;
                        
                        $membrosCadastrados = ComissaoMembros::where('id_comissao','=',$comissao->id_comissao)->get();
                        
                        ComissaoMembros::where('id_comissao','=',$comissao->id_comissao)->delete();
                        
                        foreach ($membros as $m)
                        {
                            
                            $membro = new ComissaoMembros();
                            
                            try{
                                $membro->id_comissao = $comissao->id_comissao;
                                $membro->email = $m;
                                $membro->tipo = $tipoMembros[$count];
                                $membro->situacao = $situacoes[$count];
                                $membro->save();
                            }catch(Exception $e) {
                                
                                if(strrpos($e->getMessage(), "1062 Duplicate entry")){
                                    return Response::json(array(utf8_encode('O participante ' . $m . ' já está cadastrado nesta comissão.')), 409);
                                }else{
                                    return Response::json(array('status' => false,
                                        'error' => $e->getMessage()),// ->errors()),
                                        409);
                                }
                                
                            }
                            
                            $count++;
                            
                            $achou = false;
                            
                            foreach ($membrosCadastrados as $mc){
                                if($mc->email == $m){
                                    $achou = true;
                                    break;
                                }
                            }
                            
                            if(!$achou){
                                
                                $usuario = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
                                where('usuario.email','=',$m)->
                                where('usuario_app.app','=',$app)->first();
                                
                                // enviar e-mail caso não esteja cadastrado no sistema
                                try{
                                    if($usuario == null){
                                        
                                        $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/cadastro.html?email=".$m."&perfil=1&tipo=".$membro->tipo;
                                        Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." está te convidando para entrar no Projeto Formar.",'link' => $actual_link), function($message) use ($m)
                                        {
                                            $message->to($m,$m)->subject(utf8_encode('Convite Projeto Formar'));
                                        });
                                        
                                    }else{
                                        
                                        $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
                                        Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." incluiu você na Comissão \''".$comissao->nome."'\'.",'link' => $actual_link), function($message) use ($m)
                                        {
                                            $message->to($m,$m)->subject(utf8_encode('Projeto Formar - Comissão adicionada'));
                                        });
                                        
                                    }
                                    
                                    $novoMembro = true;
                                    
                                }catch(Exception $e) {
                                    Log::info('Erro ao enviar e-mail');
                                }
                                
                            }
                            
                        }
                        
                    }catch(Exception $e) {
                        Log::info('Erro ao editar');
                        return Response::json(array('status' => false,
                            'error' => $e->getMessage()),// ->errors()),
                            409);
                    }
                    
                    
                    if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3)
                        return Response::json(array(utf8_encode('Registro alterado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
                        else if($novoMembro)
                            return Response::json(array(utf8_encode('Registro alterado com sucesso! <br/><br/>Foi enviado um e-mail para os novos participantes.')), 200);
                            else
                                return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);
                                
            }else{
                
                $comissao = new Comissao();
                $comissao->nome = $nome;
                $comissao->instituicao = $instituicao;
                $comissao->curso = $curso;
                $comissao->telefone = $telefone;
                
                if($perfil == 4){
                    $comissao->id_usuario = $iduser;
                }else if($perfil == 3){
                    if($usuarioVinculado != null)
                        $comissao->id_usuario = $usuarioVinculado->id_usuario;
                }
                
                if($idMidia)
                    $comissao->id_midia = $idMidia;
                    
                    try{
                        $comissao->save();
                        
                        $count = 0;
                        
                        foreach ($membros as $m)
                        {
                            
                            $membro = new ComissaoMembros();
                            
                            try{
                                $membro->id_comissao = $comissao->id_comissao;
                                $membro->email = $m;
                                $membro->tipo = $tipoMembros[$count];
                                $membro->situacao = $situacoes[$count];
                                $membro->save();
                            }catch(Exception $e) {
                                if(strrpos($e->getMessage(), "1062 Duplicate entry")){
                                    return Response::json(array(utf8_encode('O participante ' . $m . ' já está cadastrado nesta comissão.')), 409);
                                }else{
                                    return Response::json(array('status' => false,
                                        'error' => $e->getMessage()),// ->errors()),
                                        409);
                                }
                            }
                            
                            $count++;
                            
                            $usuario = Usuario::join('usuario_app','usuario_app.id_usuario','=','usuario.id_usuario')->
                            where('usuario.email','=',$m)->
                            where('usuario_app.app','=',$app)->first();
                            
                            // enviar e-mail caso não esteja cadastrado no sistema
                            try{
                                if($usuario == null){
                                    
                                    $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/cadastro.html?email=".$m."&perfil=1&tipo=".$membro->tipo;
                                    Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." está te convidando para entrar no Projeto Formar.",'link' => $actual_link), function($message) use ($m)
                                    {
                                        $message->to($m,$m)->subject(utf8_encode('Convite Projeto Formar'));
                                    });
                                    
                                }else{
                                    
                                    $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
                                    Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." incluiu você na Comissão \''".$comissao->nome."'\'.",'link' => $actual_link), function($message) use ($m)
                                    {
                                        $message->to($m,$m)->subject(utf8_encode('Projeto Formar - Comissão adicionada'));
                                    });
                                    
                                }
                            }catch(Exception $e) {
                                Log::info('Erro ao enviar e-mail');
                            }
                            
                        }
                        
                    }catch(Exception $e) {
                        Log::info('Erro ao salvar');
                        return Response::json(array('status' => false,
                            'error' => $e->getMessage()),// ->errors()),
                            409);
                    }
                    
                    if($emailVinculado != null && $usuarioVinculado == null && $perfil == 3)
                        return Response::json(array(utf8_encode('Registro cadastrado com sucesso! <br/><br/>O e-mail vinculado informado não possui usuário cadastrado. Foi enviado um convite para o e-mail '.$emailVinculado.'. ')), 200);
                        else
                            return Response::json(array(utf8_encode('Registro cadastrado com sucesso! <br/><br/>Foi enviado um e-mail para cada participante.')), 200);
                            
            }
            
    }
    
    public function getComissoes($email = '', $iduser = 0, $app = '', $perfil = 0, $busca = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
            
            if($perfil == 1){ // ALUNO
                // where faz parte da minha rede
                
                if($busca == 0){
                    $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                    join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                    leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                    where('comissao_membros.email','=',$email)->
                    select('comissao.id_comissao',
                        'comissao.id_usuario',
                        'comissao.nome',
                        'comissao.instituicao',
                        'comissao.curso',
                        'comissao.telefone',
                        'usuario.email',
                        'midia.caminho_midia')->get();
                }else if($busca == 1){
                    $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                    leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                    select('comissao.id_comissao',
                        'comissao.id_usuario',
                        'comissao.nome',
                        'comissao.instituicao',
                        'comissao.curso',
                        'comissao.telefone',
                        'usuario.email',
                        'midia.caminho_midia')->get();
                }
                
            }else if($perfil == 2){ // FORNECEDOR
                
                if($busca == 0){
                    $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                    join('comissao_fornecedor', 'comissao_fornecedor.id_comissao','=','comissao.id_comissao')->
                    join('fornecedor', 'fornecedor.id_fornecedor','=','comissao_fornecedor.id_fornecedor')->
                    join('usuario as usuarioFornecedor','usuarioFornecedor.id_usuario','=','fornecedor.id_usuario')->
                    leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                    where('usuarioFornecedor.id_usuario','=',$iduser)->
                    select('comissao.id_comissao',
                        'comissao.id_usuario',
                        'comissao.nome',
                        'comissao.instituicao',
                        'comissao.curso',
                        'comissao.telefone',
                        'usuario.email',
                        'comissao_fornecedor.id_fornecedor',
                        'comissao_fornecedor.situacao',
                        'comissao_fornecedor.atuar_como_presidente',
                        'midia.caminho_midia')->get();
                }else if($busca == 1){
                    $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                    leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                    select('comissao.id_comissao',
                        'comissao.id_usuario',
                        'comissao.nome',
                        'comissao.instituicao',
                        'comissao.curso',
                        'comissao.telefone',
                        'usuario.email',
                        'midia.caminho_midia')->get();
                }
                
            }else if($perfil == 3){ // ADMINISTRADOR
                
                $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'usuario.email',
                    'midia.caminho_midia')->get();
                
            }else if($perfil == 3){ // ADMINISTRADOR
                
                $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'usuario.email',
                    'midia.caminho_midia')->get();
                
            }else if($perfil == 4){ // COMISSAO
                
                $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                leftJoin('usuario as usuarioAluno','usuarioAluno.email','=','comissao_membros.email')->
                leftJoin('aluno','aluno.id_usuario','=','usuarioAluno.id_usuario')->
                leftJoin('midia as midiaAluno', 'midiaAluno.id_midia','=','aluno.id_midia')->
                where('comissao.id_usuario','=',$iduser)->
                where('comissao_membros.email','=',$email)->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'comissao_membros.situacao',
                    'aluno.nome as nomeAluno',
                    'midiaAluno.caminho_midia as midiaAluno',
                    'usuario.email',
                    'midia.caminho_midia')->orderBy('comissao_membros.tipo', 'asc')->orderBy('comissao_membros.updated_at', 'asc')->get();
                
                $retorno2 = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                leftJoin('usuario as usuarioAluno','usuarioAluno.email','=','comissao_membros.email')->
                leftJoin('aluno','aluno.id_usuario','=','usuarioAluno.id_usuario')->
                leftJoin('midia as midiaAluno', 'midiaAluno.id_midia','=','aluno.id_midia')->
                where('comissao.id_usuario','=',$iduser)->
                where('comissao_membros.email','<>',$email)->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'comissao_membros.situacao',
                    'aluno.nome as nomeAluno',
                    'midiaAluno.caminho_midia as midiaAluno',
                    'usuario.email',
                    'midia.caminho_midia')->orderBy('comissao_membros.tipo', 'asc')->orderBy('comissao_membros.updated_at', 'asc')->get();
                
                return Response::json(array('status' => true,
                    'Comissao' => array_merge($retorno->toArray(), $retorno2->toArray())),
                    200);
                
            }else {
                return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
            }
            
            return Response::json(array('status' => true,
                'Comissao' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }
    
    public function getComissao($email = '', $iduser = 0, $app = '', $perfil = 0,  $id = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0 || $id == 0){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
            
            $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
            join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
            leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
            leftJoin('usuario as usuarioAluno','usuarioAluno.email','=','comissao_membros.email')->
            leftJoin('aluno','aluno.id_usuario','=','usuarioAluno.id_usuario')->
            leftJoin('midia as midiaAluno', 'midiaAluno.id_midia','=','aluno.id_midia')->
            where('comissao.id_comissao','=',$id)->
            //where('comissao_membros.situacao','=',1)->
            select('comissao.id_comissao',
                'comissao.id_usuario',
                'comissao.nome',
                'comissao.instituicao',
                'comissao.curso',
                'comissao.telefone',
                'comissao_membros.email as emailMembro',
                'comissao_membros.tipo',
                'comissao_membros.situacao',
                'aluno.nome as nomeAluno',
                'midiaAluno.caminho_midia as midiaAluno',
                'usuario.email',
                'midia.caminho_midia')->get();
            
            return Response::json(array('status' => true,
                'Comissao' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }
    
    public function ingressarComissao(){
        
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $app = Input::get('app');
        $perfil = Input::get('perfil');
        $id = Input::get('id');
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        $comissao = Comissao::where('id_comissao','=',$id)->first();
        if($comissao == null)
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        
        $membro = new ComissaoMembros();
        
        try{
            $membro->id_comissao = $id;
            $membro->email = $email;
            $membro->tipo = 0;
            $membro->situacao = 0;
            $membro->save();
            
            $membros = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
            join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
            leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
            where('comissao.id_comissao','=',$id)->
            where('comissao_membros.situacao','=',1)->
            where('comissao_membros.tipo','=',1)->
            select('comissao.id_comissao',
                'comissao.id_usuario',
                'comissao.nome',
                'comissao.instituicao',
                'comissao.curso',
                'comissao.telefone',
                'comissao_membros.email as emailMembro',
                'comissao_membros.tipo',
                'usuario.email',
                'midia.caminho_midia')->get();
            
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
            foreach ($membros as $m)
            {
                try{
                    Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$email." está solicitando o ingresso na comissão '".$comissao->nome."'",'link' => $actual_link), function($message) use ($m)
                    {
                        $message->to($m->emailMembro,$m->emailMembro)->subject(utf8_encode('Projeto Formar - Aluno querendo ingressar na Comissão'));
                    });
                }catch(Exception $e) {
                    Log::info('Erro ao enviar e-mail');
                }
            }
            
            return Response::json(array(utf8_encode('Solicitação de ingresso na comissão \''.$comissao->nome.'\' realizada com sucesso.')), 200);
            
        }catch(Exception $e) {
            
            if(strrpos($e->getMessage(), "1062 Duplicate entry")){
                return Response::json(array(utf8_encode('O participante ' . $m . ' já está cadastrado nesta comissão.')), 409);
            }else{
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
            
        }
        
    }
    
    public function getComissaoPresidente($email = '', $iduser = 0, $app = '', $perfil = 0, $emailCriador = '', $id = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0 || $emailCriador == 'undefined' || $emailCriador == null || $emailCriador == '' || $id == 0){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if(!validarEmail2($emailCriador)){
            return Response::json(array(utf8_encode('E-mail '.$m.' Inválido!')),401);
        }
        
        $membro = ComissaoMembros::where('id_comissao','=',$id)->where('email','=',$email)->where('tipo','=',1)->where('situacao','=',1)->first();
        
        $userCriador = Usuario::where('email','=',$emailCriador)->first();
        
        if($membro == null || $userCriador == null)
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        
        
        try{
            
                $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                where('comissao.id_usuario','=',$userCriador->id_usuario)->
                where('comissao_membros.email','=',$emailCriador)->
                where('comissao.id_comissao','=',$id)->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'comissao_membros.situacao',
                    'usuario.email',
                    'midia.caminho_midia')->orderBy('comissao_membros.tipo', 'asc')->orderBy('comissao_membros.updated_at', 'asc')->get();
                
                $retorno2 = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
                join('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
                leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
                where('comissao.id_usuario','=',$userCriador->id_usuario)->
                where('comissao_membros.email','<>',$emailCriador)->
                where('comissao.id_comissao','=',$id)->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'comissao_membros.situacao',
                    'usuario.email',
                    'midia.caminho_midia')->orderBy('comissao_membros.tipo', 'asc')->orderBy('comissao_membros.updated_at', 'asc')->get();
                
                return Response::json(array('status' => true,
                    'Comissao' => array_merge($retorno->toArray(), $retorno2->toArray())),
                    200);
            
            return Response::json(array('status' => true,
                'Comissao' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }
    
    public function concederPermissaoPresidenteCerimonial(){
    
    	try{
    	
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
	        $fornecedor = Fornecedor::where('id_fornecedor','=',$idFornecedor)->where('cerimonial','=',1)->first();
	        $comissaoFornecedor = ComissaoFornecedor::where('id_comissao','=',$idComissao)->where('id_fornecedor','=',$idFornecedor)->first();
	        $membro = ComissaoMembros::where('id_comissao','=',$idComissao)->where('email','=',$email)->where('tipo','=',1)->where('situacao','=',1)->first();
	        
	        if($comissaoFornecedor == null || $comissao == null || $fornecedor == null || ($membro == null && $perfil != 2))
	        	return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	        else{
	        	if($comissaoFornecedor->atuar_como_presidente == 1)
	        		return Response::json(array(utf8_encode('O cerimonial \'' . $fornecedor->nome . '\' já possui acesso de presidente na comissão \'' . $comissao->nome . '\'.')), 409);
	        }      
	        
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
	        	
			DB::table('comissao_fornecedor')
            ->where('id_comissao', $idComissao)
            ->where('id_fornecedor', $idFornecedor)
            ->update(array('atuar_como_presidente' => 1));
			
	        $usuarioFornecedor = Usuario::where('id_usuario','=',$fornecedor->id_usuario)->first();
	        
	        $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
	        try{
	            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "A comissão '".$comissao->nome."' concedeu a você acesso de presidente",'link' => $actual_link), function($message) use ($usuarioFornecedor)
	            {
	                $message->to($usuarioFornecedor->email,$usuarioFornecedor->email)->subject(utf8_encode('Projeto Formar - Comissão concedeu permissão de Presidente'));
	            });
	        }catch(Exception $e) {
	            Log::info('Erro ao enviar e-mail');
	        }
	
	        return Response::json(array(utf8_encode('Concessão de acesso de Presidente realizada com sucesso para o cerimonial \''.$fornecedor->nome.'\'')), 200);
        
		}catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }        
        
    }    
    
    public function removerPermissaoPresidenteCerimonial(){
    
    	try{
    	
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
	        $fornecedor = Fornecedor::where('id_fornecedor','=',$idFornecedor)->where('cerimonial','=',1)->first();
	        $comissaoFornecedor = ComissaoFornecedor::where('id_comissao','=',$idComissao)->where('id_fornecedor','=',$idFornecedor)->first();
	        $membro = ComissaoMembros::where('id_comissao','=',$idComissao)->where('email','=',$email)->where('tipo','=',1)->where('situacao','=',1)->first();
	        
	        if($comissaoFornecedor == null || $comissao == null || $fornecedor == null || ($membro == null && $perfil != 2))
	        	return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
	        else{
	        	if($comissaoFornecedor->atuar_como_presidente == 0)
	        		return Response::json(array(utf8_encode('O cerimonial \'' . $fornecedor->nome . '\' não possui acesso de presidente na comissão \'' . $comissao->nome . '\'. Algo estranho está ocorrendo...')), 409);
	        }
	        
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
	        	
			DB::table('comissao_fornecedor')
            ->where('id_comissao', $idComissao)
            ->where('id_fornecedor', $idFornecedor)
            ->update(array('atuar_como_presidente' => 0));
			
	        $usuarioFornecedor = Usuario::where('id_usuario','=',$fornecedor->id_usuario)->first();
	        
	        $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
	        try{
	            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "A comissão '".$comissao->nome."' removeu seu acesso de presidente.",'link' => $actual_link), function($message) use ($usuarioFornecedor)
	            {
	                $message->to($usuarioFornecedor->email,$usuarioFornecedor->email)->subject(utf8_encode('Projeto Formar - Comissão removeu permissão de Presidente'));
	            });
	        }catch(Exception $e) {
	            Log::info('Erro ao enviar e-mail');
	        }
	
	        return Response::json(array(utf8_encode('Remoção de acesso de Presidente realizada com sucesso para o cerimonial \''.$fornecedor->nome.'\'')), 200);
        
		}catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }        
        
    }    

    public function incluirFornecedorNaLista(){
        
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
        
        try{

            DB::table('comissao_fornecedor')
            ->where('id_comissao', $idComissao)
            ->where('id_fornecedor', $idFornecedor)
            ->update(array('situacao' => 1));
            
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
            try{
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "A comissão '".$comissao->nome."' adicionou você na lista de fornecedores dela.",'link' => $actual_link), function($message) use ($usuarioFornecedor)
                {
                    $message->to($usuarioFornecedor->email,$usuarioFornecedor->email)->subject(utf8_encode('Projeto Formar - Comissão adicionou você na lista'));
                });
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            
            return Response::json(array(utf8_encode('Inclusão na lista do fornecedor \''.$fornecedor->nome.'\' realizada com sucesso.')), 200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
            
    }
    
    public function removerFornecedorDaLista(){
        
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
            where('comissao_fornecedor.id_comissao','=',$idComissao)->
            where('comissao_fornecedor.situacao','=',1)->
            where('comissao_fornecedor.atuar_como_presidente','=',1)->first();
            if($cerimonial == null)
                return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
            
        $usuarioFornecedor = Usuario::where('id_usuario','=',$fornecedor->id_usuario)->first();
        
        try{
            
            DB::table('comissao_fornecedor')
            ->where('id_comissao', $idComissao)
            ->where('id_fornecedor', $idFornecedor)
            ->delete();
            
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
            try{
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "A comissão '".$comissao->nome."' removeu você da lista de fornecedores dela.",'link' => $actual_link), function($message) use ($usuarioFornecedor)
                {
                    $message->to($usuarioFornecedor->email,$usuarioFornecedor->email)->subject(utf8_encode('Projeto Formar - Comissão removeu você da lista'));
                });
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            
            return Response::json(array(utf8_encode('Fornecedor \''.$fornecedor->nome.'\' removido com sucesso.')), 200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
            
    }
    
    public function getComissaoDoFornecedor($email = '', $iduser = 0, $app = '', $perfil = 0,  $idComissao = 0, $idFornecedor = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil != 2 || $idComissao == 0 || $idFornecedor == 0){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
            
            $retorno = Comissao::leftJoin('midia', 'midia.id_midia','=','comissao.id_midia')->
            leftJoin('comissao_membros', 'comissao_membros.id_comissao','=','comissao.id_comissao')->
            leftJoin('usuario','usuario.id_usuario','=','comissao.id_usuario')->
            leftJoin('usuario as usuarioAluno','usuarioAluno.email','=','comissao_membros.email')->
            leftJoin('aluno','aluno.id_usuario','=','usuarioAluno.id_usuario')->
            leftJoin('midia as midiaAluno', 'midiaAluno.id_midia','=','aluno.id_midia')->
            leftJoin('comissao_fornecedor', function($join) use($idFornecedor){
            	$join->on('comissao_fornecedor.id_comissao', '=', 'comissao.id_comissao')
                	 ->where('comissao_fornecedor.id_fornecedor', '=', $idFornecedor);
        	})->            
            where('comissao.id_comissao','=',$idComissao)->
            select('comissao.id_comissao',
                'comissao.id_usuario',
                'comissao.nome',
                'comissao.instituicao',
                'comissao.curso',
                'comissao.telefone',
                'comissao_membros.email as emailMembro',
                'comissao_membros.tipo',
                'comissao_membros.situacao',
                'comissao_fornecedor.situacao as situacaoFornecedorNaComissao',
                'comissao_fornecedor.atuar_como_presidente',
                'aluno.nome as nomeAluno',
                'midiaAluno.caminho_midia as midiaAluno',
                'usuario.email',
                'midia.caminho_midia')->get();
            
            return Response::json(array('status' => true,
                'Comissao' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }    
    
}