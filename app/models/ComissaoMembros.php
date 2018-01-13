<?php

use Illuminate\Auth\UserInterface;

class ComissaoMembros extends Eloquent {
    
    protected $table = 'comissao_membros';
    protected $primaryKey = ['id_comissao', 'email'];
    public $incrementing = false;
    
}

