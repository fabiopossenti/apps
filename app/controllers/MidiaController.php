<?php

class MidiaController extends Controller {
    
    public function upload($file, $iduser, $idmidia, $isEdicao, $app){
        
        $midiaController = new MidiaController();
        
        $prefixo = date("YmdHis").'_';
        $nomeArquivo = null;
        $tamanhoArquivo = null;
        if($file != null)
            $tamanhoArquivo = $file->getSize();
        
        $midiaAntiga = null;
        
        if($idmidia != null)
            $midiaAntiga = Midia::where('id_midia', '=', $idmidia)->where('app', '=', $app)->first();
        
        try {
            if($file != null){
                $midia = Midia::where('caminho_midia', 'like', '%'.$file->getClientOriginalName())->
                where('app', '=', $app)->
                where('tamanho','=',$tamanhoArquivo)->firstOrFail();
                try{
                    if($midiaAntiga != null){
                        if(strcmp(substr($midiaAntiga->caminho_midia, strpos($midiaAntiga->caminho_midia, '_')+1),$file->getClientOriginalName()) != 0){
                            $midia->qtd_refs = $midia->qtd_refs + 1;
                            $midia->update();
                        }
                        $midiaController->controlarMidia($midiaAntiga, $isEdicao, $file->getClientOriginalName(), $app);
                    }else{
                        $midia->qtd_refs = $midia->qtd_refs + 1;
                        $midia->update();
                    }
                    return $midia->id_midia;
                }catch(Exception $e2) {
                    Log::info('Erro ao editar midia');
                    return Response::json(array('status' => false,
                        'error' => $e2->getMessage()),// ->errors()),
                        409);
                }
            }else{
                if($midiaAntiga != null)
                    $midiaController->controlarMidia($midiaAntiga, $isEdicao, null, $app);
            }
        } catch (Exception $e) {
            try{
                if($file != null){
                    $nomeArquivo = $prefixo.$file->getClientOriginalName();
                    $upload_success = $file->move($app.'/uploads', $nomeArquivo);
                    $midia = new Midia();
                    $midia->caminho_midia = $nomeArquivo;
                    $midia->id_usuario = $iduser;
                    $midia->app = $app;
                    if($tamanhoArquivo != null)
                        $midia->tamanho = $tamanhoArquivo;
                        try{
                            $midia->save();
                            if($midiaAntiga != null)
                                $midiaController->controlarMidia($midiaAntiga, $isEdicao, $file->getClientOriginalName(), $app);
                            return $midia->id_midia;
                        }catch(Exception $e2) {
                            Log::info('Erro ao salvar midia');
                            return Response::json(array('status' => false,
                                'error' => $e2->getMessage()),// ->errors()),
                                409);
                        }
                }else{
                    if($midiaAntiga != null)
                        $midiaController->controlarMidia($midiaAntiga, $isEdicao, null, $app);
                }
            }catch(Exception $e) {
                if(strrpos($e->getMessage(), "exceeds the upload limit")){
                    return -1;
                }else{
                    return Response::json(array('status' => false,
                        'error' => $e->getMessage()),// ->errors()),
                        409);
                }
            }
        }
        
        return null;
        
    }
    
    public function controlarMidia($midiaAntiga, $isEdicao, $nomeArquivo, $app){
        
        if(!$isEdicao || ($nomeArquivo != null && strcmp(substr($midiaAntiga->caminho_midia, strpos($midiaAntiga->caminho_midia, '_')+1),$nomeArquivo) != 0)){
        
            if($midiaAntiga->qtd_refs == 1){
                try{
                    unlink($app.'/uploads/'.$midiaAntiga->caminho_midia);
                    try{
                        $midiaAntiga->delete();
                    }catch(Exception $e2) {
                        Log::info('Erro ao deletar midia');
                        return Response::json(array('status' => false,
                            'error' => $e2->getMessage()),// ->errors()),
                            409);
                    }
                }catch(Exception $e) {
                    Log::info('Nao achou arquivo');
                }
            }else{
                $midiaAntiga->qtd_refs = $midiaAntiga->qtd_refs - 1;
                try{
                    $midiaAntiga->update();
                }catch(Exception $e2) {
                    Log::info('Erro ao editar midia');
                    return Response::json(array('status' => false,
                        'error' => $e2->getMessage()),// ->errors()),
                        409);
                }
            }
        }
    }
    
    /* if($upload_success && strrpos($nomeArquivo, ".jpg")){
     $im = imagecreatefromjpeg('uploads/'.$nomeArquivo);
     $size = min(imagesx($im), imagesy($im));
     $im2 = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
     if ($im2 !== FALSE) {
     imagejpeg($im2, 'uploads/'.$nomeArquivo);
     }
     }else if($upload_success && strrpos($nomeArquivo, ".png")){
     $im = imagecreatefrompng('uploads/'.$nomeArquivo);
     $size = min(imagesx($im), imagesy($im));
     $im2 = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
     if ($im2 !== FALSE) {
     imagepng($im2, 'uploads/'.$nomeArquivo);
     }
     } */
    
}