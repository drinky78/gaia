<?php

/*
 * ©2012 Croce Rossa Italiana
 */

class Volontario extends Utente {

    var $qualifiche = array();
    var $ruoli = array();

    public function qualifiche(){
        return implode(",", $this->qualifiche);
    }
    
    public function aggiungiQualifica($nome){
        if (!in_array($nome, $this->qualifiche)){
            array_push($this->qualifiche, $nome);
        }
    }
    
    public function ruoli(){
        return implode(",", $this->ruoli);
    }
    
    public function aggiungiRuolo($nome){
        global $conf;
        
        $ruolo = $conf['corso_ruolo'][$nome];
        if (!in_array($ruolo, $this->ruoli)){
            array_push($this->ruoli, $ruolo);
        }
    }
    
}