<?php
use Illuminate\Auth\UserInterface;
class Aluno extends Eloquent {
	
	protected $table = 'aluno';
	protected $primaryKey = 'id_aluno';
}