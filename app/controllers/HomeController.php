<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showWelcome()
	{
		return View::make('hello');
	}
	
	public function upload(){
	    
	    $files = Input::file('file');
	    $titulo = Input::get('titulo');
	    $descricao = Input::get('descricao');	    
	    
	    $upload_success = true;
	    
        foreach($files as $file) {
	        
	        // public/uploads	   
	        $upload_success = $file->move('uploads', date("YmdHis").'_'.$file->getClientOriginalName());
	        
	        $nomeOriginal = $file->getClientOriginalName();
	        
	    }
	    
	    if( $upload_success ) {
	        return Response::json('success', 200);
	    } else {
	        return Response::json('error', 400);
	    }
	    
	}
	
	public function obterImagens(){
	    
	    $pasta = 'uploads/';
	    $arquivos = glob("$pasta{*.jpg,*.JPG,*.png,*.gif,*.bmp}", GLOB_BRACE);
	    foreach($arquivos as $img){
	        echo $img;
	    }
	    
	}

}
