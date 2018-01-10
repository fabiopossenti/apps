<?php

ini_set('max_execution_time', 180);

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

class UsuarioConviteController extends Controller {
    
    public function cadastrar(){
        
        $iduser = Input::get('iduser');
        $emailConvidado = Input::get('emailConvidado');
        $emailConvidado = strtolower(trim($emailConvidado));
        $app = Input::get('app');
        $email = Input::get('email');
        
        $emailUsuarioJaConvidado = false;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if(validarEmail2($emailConvidado)){
            
            $usuario = Usuario::where('id_usuario','=',$iduser)->first();
            
            // verificação dos que eu já convidei...
            $usuarioConvite = UsuarioConvite::where('id_usuario','=',$usuario->id_usuario)->where('email_convidado','=',$emailConvidado)->
            where('app','=',$app)->first();
            if($usuarioConvite != null){ // usuário já convidado
                if($usuarioConvite->situacao == 2){ // recusou
                    return Response::json(array(utf8_encode('Você já convidou o usuário '.$emailConvidado.' mas ele te recusou.')),401);
                }else if($usuarioConvite->situacao == 1){ // aceitou
                    return Response::json(array(utf8_encode('Você já é amigo de '.$emailConvidado)),401);
                }else{
                    return Response::json(array(utf8_encode('Você já convidou o usuário '.$emailConvidado.'. Aguardando confirmação.')),401);
                }
            }
            
            try{
                $usuarioConvidado = Usuario::where('email','=',$emailConvidado)->firstOrFail();
                
                if($usuarioConvidado->id_usuario == $usuario->id_usuario){
                    return Response::json(array(utf8_encode('Não é permitido convidar você mesmo!')),401);
                }
                
                $emailUsuarioJaConvidado = true;
                
                // verificação dos que já me convidaram
                $usuarioConvite = UsuarioConvite::where('id_usuario','=',$usuarioConvidado->id_usuario)->where('email_convidado','=',$usuario->email)->
                where('app','=',$app)->first();
                if($usuarioConvite != null){ // usuário já convidado
                    if($usuarioConvite->situacao == 2){ // recusou
                        $usuarioConvite->situacao = 1;
                        $usuarioConvite->update();
                        return Response::json(array(utf8_encode('Usuário '.$emailConvidado.' já te convidou e você recusou. Amizade confirmada!')),200);
                    }else if($usuarioConvite->situacao == 1){ // aceitou
                        return Response::json(array(utf8_encode('Você já é amigo de '.$emailConvidado)),401);
                    }else{ //já existe um convite sem ser aceito ou recusado
                        $usuarioConvite->situacao = 1;
                        $usuarioConvite->update();
                        return Response::json(array(utf8_encode('Usuário '.$emailConvidado.' já te convidou. Amizade confirmada!')),401);
                    }
                }
            }catch(Exception $e) {
                Log::info('Usuário não cadastrado');
            }
            
            $usuarioConvite = new UsuarioConvite();
            $usuarioConvite->id_usuario = $iduser;
            $usuarioConvite->app = $app;
            $usuarioConvite->email_convidado = $emailConvidado;
            
            try{
                $usuarioConvite->save();
            }catch(Exception $e) {
                Log::info('Erro ao salvar');
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
            
            try{
                //$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                
                if($emailUsuarioJaConvidado){
                    $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
                    Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." está te convidando para ser seu amigo.",'link' => $actual_link), function($message)
                    {
                        $message->to(strtolower(trim(Input::get('emailConvidado'))),strtolower(trim(Input::get('emailConvidado'))))->subject(utf8_encode('Tem alguém querendo saber se você já está chegando...'));
                    });
                }else{
                    $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/cadastro_oct.html?id=".$usuario->id_usuario."&email=".$emailConvidado;
                    Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." está te convidando para entrar no OndeCeTá?.",'link' => $actual_link), function($message)
                    {
                        $message->to(strtolower(trim(Input::get('emailConvidado'))),strtolower(trim(Input::get('emailConvidado'))))->subject(utf8_encode('Tem alguém querendo saber se você já está chegando...'));
                    });
                    
                }
                
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            
            return Response::json(array(utf8_encode('Convite enviado com sucesso!')), 200);
        }else{
            return Response::json(array(utf8_encode('Email Inválido!')),401);
        }
        
    }
    
    public function getUsuariosPorIdUsuario($email = '', $iduser = 0, $app = ''){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
            
            $usuario = Usuario::where('id_usuario','=',$iduser)->first();
            
            $results = DB::select(
                
                DB::raw("(select usuario.id_usuario, usuario.email, usuario_convite.situacao, \"2\" as tipo, solicitacao_posicao.lat, solicitacao_posicao.lon, solicitacao_posicao.updated_at, solicitacao_posicao.tipo_transporte ".
                    "from usuario_convite ".
                    "inner join usuario on usuario.id_usuario = usuario_convite.id_usuario ".
                    "left join solicitacao_posicao on (solicitacao_posicao.id_usuario = :idUser1 and solicitacao_posicao.id_usuario_solicitado = usuario.id_usuario and solicitacao_posicao.situacao = 1) ".
                    "where usuario_convite.email_convidado = :email and usuario_convite.app = :app1 ".
                    "and (solicitacao_posicao.id_solicitacao_posicao = (select max(id_solicitacao_posicao) from solicitacao_posicao where solicitacao_posicao.id_usuario = :idUser2 and solicitacao_posicao.id_usuario_solicitado = usuario.id_usuario and solicitacao_posicao.situacao = 1) or solicitacao_posicao.id_solicitacao_posicao is null) ".
                    ") ".
                    "union ".
                    "(select usuario.id_usuario, usuario_convite.email_convidado, usuario_convite.situacao, \"1\" as tipo, solicitacao_posicao.lat, solicitacao_posicao.lon, solicitacao_posicao.updated_at, solicitacao_posicao.tipo_transporte ".
                    "from usuario_convite ".
                    "left join usuario on usuario.email = usuario_convite.email_convidado ".
                    "left join solicitacao_posicao on (solicitacao_posicao.id_usuario = :idUser3 and solicitacao_posicao.id_usuario_solicitado = usuario.id_usuario and solicitacao_posicao.situacao = 1) ".
                    "where usuario_convite.id_usuario = :idUser4 and usuario_convite.app = :app2 ".
                    "and (solicitacao_posicao.id_solicitacao_posicao = (select max(id_solicitacao_posicao) from solicitacao_posicao where solicitacao_posicao.id_usuario = :idUser5 and solicitacao_posicao.id_usuario_solicitado = usuario.id_usuario and solicitacao_posicao.situacao = 1) or solicitacao_posicao.id_solicitacao_posicao is null) ".
                    ")order by updated_at desc"), array(
                        'idUser1' => $iduser,
                        'idUser2' => $iduser,
                        'idUser3' => $iduser,
                        'idUser4' => $iduser,
                        'idUser5' => $iduser,
                        'email' => $usuario->email,
                        'app1' => $app,
                        'app2' => $app,
                    ));
            
            return Response::json(array('status' => true,
                'Amigos' => $results),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }
    
    public function confirmarAmizade(){
        
        $iduser = Input::get('iduser');
        $confirmacao = Input::get('confirmacao');
        $idUsuarioSolicitado = Input::get('idUsuarioSolicitado');
        $app = Input::get('app');
        $email = Input::get('email');
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        $usuario = Usuario::where('id_usuario','=',$iduser)->first();
        
        try{
            $usuarioConvite = UsuarioConvite::where('email_convidado','=',$usuario->email)->where('id_usuario','=',$idUsuarioSolicitado)->
            where('app','=',$app)->
            whereNull('situacao')->firstOrFail();
            $usuarioConvite->situacao = $confirmacao;
            $usuarioConvite->update();
        }catch(Exception $e) {
            Log::info('Erro ao atualizar confirmacao amizade');
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                409);
        }
        
        if($confirmacao == "1"){
            try{
                $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." aceitou seu pedido de amizade.",'link' => $actual_link), function($message)
                {
                    $usuarioSolicitante = Usuario::where('id_usuario','=',Input::get('idUsuarioSolicitado'))->first();
                    $message->to($usuarioSolicitante->email,$usuarioSolicitante->email)->subject(utf8_encode('Amizade confirmada.'));
                });
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            //DB::getQueryLog()
            return Response::json(array(utf8_encode('Amizade confirmada com sucesso!')), 200);
        }else{
            try{
                $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/".$app."/login.html";
                Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." recusou seu pedido de amizade.",'link' => $actual_link), function($message)
                {
                    $usuarioSolicitante = Usuario::where('id_usuario','=',Input::get('idUsuarioSolicitado'))->first();
                    $message->to($usuarioSolicitante->email,$usuarioSolicitante->email)->subject(utf8_encode('Amizade não confirmada.'));
                });
            }catch(Exception $e) {
                Log::info('Erro ao enviar e-mail');
            }
            return Response::json(array(utf8_encode('Amizade recusada!')), 200);
        }
        
    }
    
    public function reenviarConvite(){
        
        $iduser = Input::get('iduser');
        $email = Input::get('email');
        $emailLogado = Input::get('emailLogado');
        $app = Input::get('app');
        
        if((new UsuarioController())->validarAcessoTela($iduser, $emailLogado, $app) == -1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        $usuario = Usuario::where('id_usuario','=',$iduser)->first();
        
        try{
            $usuarioConvidado = Usuario::where('email','=',$email)->firstOrFail();
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/ondeceta/login.html";
            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." está te convidando para ser seu amigo.",'link' => $actual_link), function($message)
            {
                $message->to(strtolower(trim(Input::get('email'))),strtolower(trim(Input::get('email'))))->subject(utf8_encode('Tem alguém querendo saber se você já está chegando...'));
            });
        }catch(Exception $e) {
            Log::info('Usuário não cadastrado');
            $actual_link = "https" . "://$_SERVER[HTTP_HOST]"."/apps/public/ondeceta/cadastro_oct.html?id=".$usuario->id_usuario."&email=".$email;
            Mail::send('alertmailondeceta', array('key' => 'value','texto' => "O usuário ".$usuario->email." está te convidando para entrar no OndeCeTá?.",'link' => $actual_link), function($message)
            {
                $message->to(strtolower(trim(Input::get('email'))),strtolower(trim(Input::get('email'))))->subject(utf8_encode('Tem alguém querendo saber se você já está chegando...'));
            });
        }
        
        return Response::json(array(utf8_encode('Convite reenviado com sucesso!')), 200);
    }
    
}