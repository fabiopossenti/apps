<?php

class EventoController extends Controller {
	
	public function getEventos(){
	
		try{
			$eventos = Evento::get();
			
			return Response::json(array('status' => true,
				'Eventos' => $eventos->toArray()),
					200);
	
		}catch(Exception $e) {
			return Response::json(array('status' => false,
					'error' => $e->getMessage()),// ->errors()),
					406);
		}
	
	}	
	
}