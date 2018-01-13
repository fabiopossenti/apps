<?php

use Illuminate\Auth\UserInterface;

class ComissaoFornecedor extends Eloquent {
    
    protected $table = 'comissao_fornecedor';
    protected $primaryKey = ['id_comissao', 'id_fornecedor'];
    public $incrementing = false;
    
}

