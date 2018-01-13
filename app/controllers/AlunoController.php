<?php

class AlunoController extends Controller {
    
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
        $file = Input::file('file');
        $idMidia = null;
        $aluno = null;
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == null || $perfil != 1){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        if($id != null)
            $aluno = Aluno::where('id_aluno', '=', $id)->first();
            
        if($file != null || $flexcluir != null){
            if($aluno != null)
                $idMidia = $midiaController->upload($file, $iduser, $aluno->id_midia, $flexcluir == null,$app);
                else
                    $idMidia = $midiaController->upload($file, $iduser, null, $flexcluir == null,$app);
                    if($idMidia == -1)
                        return Response::json(array(utf8_encode('Tamanho máximo de bytes excedido do arquivo. Tamanho máximo: 1MB')), 409);
        }
        
        if($flexcluir != null){
            try{
                $aluno->delete();
            }catch(Exception $e) {
                Log::info('Erro ao excluir');
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
            return Response::json(array(utf8_encode('Registro removido com sucesso!')), 200);
        }
            
        if($id != null){
            
            $aluno->nome = $nome;
            $aluno->instituicao = $instituicao;
            $aluno->curso = $curso;
            $aluno->telefone = $telefone;
            $aluno->id_usuario = $iduser;
            
            if($idMidia)
                $aluno->id_midia = $idMidia;
                
            try{
                $aluno->update();
            }catch(Exception $e) {
                Log::info('Erro ao editar');
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
                
            return Response::json(array(utf8_encode('Registro alterado com sucesso!')), 200);
                        
        }else{
            
            $aluno = new Aluno();
            $aluno->nome = $nome;
            $aluno->instituicao = $instituicao;
            $aluno->curso = $curso;
            $aluno->telefone = $telefone;
            $aluno->id_usuario = $iduser;
            
            if($idMidia)
                $aluno->id_midia = $idMidia;
                
            try{
                $aluno->save();
            }catch(Exception $e) {
                Log::info('Erro ao salvar');
                return Response::json(array('status' => false,
                    'error' => $e->getMessage()),// ->errors()),
                    409);
            }
                
            return Response::json(array(utf8_encode('Registro cadastrado com sucesso!')), 200);
                        
        }
            
    }
    
    public function getAlunos($email = '', $iduser = 0, $app = '', $perfil = 0){
        
        if((new UsuarioController())->validarAcessoTela($iduser, $email, $app) == -1 || $perfil == 0){
            return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
        }
        
        try{
        
		     if($perfil == 1){ // ALUNO
		     
                $retorno = Aluno::leftJoin('midia', 'midia.id_midia','=','aluno.id_midia')->
                where('aluno.id_usuario','=',$iduser)->
                select('aluno.id_aluno',
                    'aluno.id_usuario',
                    'aluno.nome',
                    'aluno.instituicao',
                    'aluno.curso',
                    'aluno.telefone',
                    'midia.caminho_midia')->get();
		         
		     }else if($perfil == 2){ // FORNECEDOR
		         
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
                where('comissao.id_usuario','=',$iduser)->
                select('comissao.id_comissao',
                    'comissao.id_usuario',
                    'comissao.nome',
                    'comissao.instituicao',
                    'comissao.curso',
                    'comissao.telefone',
                    'comissao_membros.email as emailMembro',
                    'comissao_membros.tipo',
                    'usuario.email',
                    'midia.caminho_midia')->orderBy('comissao_membros.tipo', 'asc')->orderBy('comissao_membros.updated_at', 'asc')->get();
                
            }else {
                return Response::json(array(utf8_encode('Acesso não autorizado!')), 401);
            }
            
            return Response::json(array('status' => true,
                'Aluno' => $retorno->toArray()),
                200);
            
        }catch(Exception $e) {
            return Response::json(array('status' => false,
                'error' => $e->getMessage()),// ->errors()),
                406);
        }
        
    }
    
}