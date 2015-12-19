<?php

/*
 * ©2012 Croce Rossa Italiana
 */

class PartecipazioneCorso extends Entita {

    protected static
        $_t  = 'crs_partecipazioni_corsi',
        $_dt = null;

    use EntitaCache;

    public function ruolo(){
        global $conf;
        $ruolo = $conf['corso_ruolo'][intval($this->ruolo)];
        return $ruolo;
    }
    
    public function corso(){
        return Corso::id($this->corso);
    }
    
    public function volontario() {
        return Volontario::id($this->volontario);
    }
    
    public static function md5($id){
        return md5(MODULO_FORMAZIONE_CORSI_SECUREKEY."_".$id);
    }
    
    public function modificabile() {
        if (!$this->inizio || !$this->corso) {
            return false;
        }

        try {
            $c =$this->corso();
        } catch(Exception $e) {
            return false;
        }
        
        $inizio = $c->inizio;
        $oggi = (new DT())->getTimestamp();
        $buffer = GIORNI_PARTECIPAZIONE_NON_MODIFICABILE * 86400;
        
        return (($oggi-$inizio) > $buffer);
    }
    
    
    public function aggiungi(Corso $c, Volontario $v, $ruolo=0) {
        global $sessione;

        /*
        $comitato = Comitato::daOid($c->organizzatore);
        if (!$sessione->utente()->presiede($comitato)) { // N.B.: da estendere anche al delegato del presidente
            return false;
        }
        */

        $this->corso = $c->id;
        $this->volontario = $v->id;
        $this->stato = PARTECIPAZIONE_RICHIESTA;
        $this->ruolo = $ruolo;
        $this->md5 = PartecipazioneCorso::md5($this->id);
        $this->timestamp = (new DT())->getTimestamp();
        
        $this->inviaInvito();
        return true;
    }
    
    /**
     * 
     */
    public function getDestinatariInvito(){
        $list = array();
        array_push($list, $this->volontario());
        return $list;
    }
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function inviaInvito() {
        $m = new Email('crs/inviti/invito', "Invito ".$this->id);

        $m->a = $this->getDestinatariInvito();
        $m->_NOME = $this->volontario()->nomeCompleto();
        $m->_HOSTNAME = filter_input(INPUT_SERVER, "SERVER_NAME");
        $m->_CORSO = $this->corso()->nome();
        $m->_RUOLO = $this->ruolo();
        $m->_DATA = $this->corso()->data();
        $m->_ID = $this->id;
        $m->_MD5 = $this->md5;
        $m->invia();
        
        return $m;
    }
    
    public function richiedi() {
        /*
        $m = new Email('richiestaPartecipazioneCorso', 'È richiesta la tua partecipazione ad un corso');
        $m->a = $this->attivita()->referente();
        $m->_NOME           = $this->attivita()->referente()->nome;
        $m->_VOLONTARIO     = $v->nomeCompleto();
        $m->_ATTIVITA       = $this->attivita()->nome;
        $m->_TURNO          = $this->turno()->nome;
        $m->_DATA           = $this->turno()->inizio()->inTesto();
        if (!$m->invia())
            return false;
        */
        return true;
    }

    
    /*
     * Ritira la prenotazione ( da parte dell'organizzatore)
     * @return bool false se la prenotazione non e ritirabile, altrimenti true
     */
    public function ritira(String $motivo) {
        global $sessione;

        // DA CHIEDERE: 
        // se la partecipazione di un istruttore è ritirata
        // prima che partano i giorni "buffer", c'è ancora tempo per cercare altri istruttori
        // diversamente il corso viene annullato d'ufficio
        
        $comitato = Comitato::daOid($c->organizzatore);
        if (!$sessione->utente()->presiede($comitato)) { // N.B.: da estendere anche al delegato del presidente
            return false;
        }
        
        if (!$this->modificabile() &&
            $this->stato == PARTECIPAZIONE_ACCETTATA &&
            $this->ruolo == CORSO_RUOLO_DOCENTE
           ) {
            // cancellazione del corso e collegati, 
            // compreso il record db di questo oggetto PartecipazioneCorso
            
            // notifiche a chi di dovere
            return true;
        }
        
        // mettere corso in stato pending
        if (!empty($motivo)) {
            $this->note = $this->note .' Partecipazione ritirata dal presidente/delegato: "'.$motivo.'".';
        }
        $this->stato = PARTECIPAZIONE_RITIRATA;
        
        return true;
    }
    
    
    /**
     * Nega la presenza al corso
     * @return bool false se la prenotazione non è negata, altrimenti true
     */
    public function nega(String $motivo) {
        global $sessione;

        
        if ($this->stato != PARTECIPAZIONE_RICHIESTA) {
            return false;
        }
        
        if (!$this->modificabile()) {
            return false;
        }
            
        if ($sessione->utente()->id != $this->volontario) {
            return false;
        }
        
        if ( !empty($motivo) ) {
            $this->note = $this->note .' Partecipazione negata dal volontario: "'.$motivo.'".';
        }
        
        $this->inviaRifiuto();
        
        $this->stato = PARTECIPAZIONE_NEGATA;
        return true;
    }
    
    /**
     * 
     */
    public function getDestinatariRifiuto(){
        $list = array();
        
        array_push($list, $this->corso()->responsabile());
        array_push($list, $this->corso()->organizzatore());
        
        return $list;
    }
    
    /**
     * Invia la notifica di accettazione 
     */
    public function inviaRifiuto(){
        
         $m = new Email('crs/inviti/rifiuta', "Invito ".$this->id);

        $m->a = $this->getDestinatariRifiuto();

        $m->_NOME = $this->volontario()->nomeCompleto();
        $m->_HOSTNAME = filter_input(INPUT_SERVER, "SERVER_NAME");
        $m->_CORSO = $this->corso()->nome();
        $m->_RUOLO = $this->ruolo();
        $m->_DATA = $this->corso()->data();
        $m->_ID = $this->id;
        $m->_MD5 = $this->md5;
        
        return $m->invia();
    }
    
    
    /**
     * Accetta di essere presente al corso
     * @return bool false se la prenotazione non è negata, altrimenti true
     */
    public function accetta($md5 = null) {
        global $sessione;

        if ($this->stato != PARTECIPAZIONE_RICHIESTA) {
            return false;
        }
        
        if (!$this->modificabile() && false) {
            return false;
        }
            
        if ($sessione->utente()->id != $this->volontario && $this->md5 != $md5) {
            return false;
        }
   
        $this->inviaAccettazione();
        
        $this->tConferma = time();
        $this->stato = PARTECIPAZIONE_ACCETTATA;
        return true;
    }
    
    /**
     * 
     */
    public function getDestinatariAccettazione(){
        $list = array();
        
        array_push($list, $this->corso()->responsabile());
        array_push($list, $this->corso()->organizzatore());
        
        return $list;
    }
    
    /**
     * Invia la notifica di accettazione 
     */
    public function inviaAccettazione(){
        $m = new Email('crs/inviti/accetta', "Invito ".$this->id);

        $m->a = $this->getDestinatariAccettazione();
        $m->_NOME = $this->volontario()->nomeCompleto();
        $m->_HOSTNAME = filter_input(INPUT_SERVER, "SERVER_NAME");
        $m->_CORSO = $this->corso()->nome();
        $m->_RUOLO = $this->ruolo();
        $m->_DATA = $this->corso()->data();
        $m->_ID = $this->id;
        $m->_MD5 = $this->md5;
        
        return $m->invia();
    }
    
    /**
     * Annulla la conferma di presenza, generalmente per motivi imprevisti
     * @return bool false se la prenotazione non e ritirabile, altrimenti true
     */
    public function annulla($motivo) {
        global $sessione;

        if ($this->stato != PARTECIPAZIONE_ACCETTATA) {
            return false;
        }
        
        if (!$this->modificabile()) {
            return false;
        }
        
        if ($sessione->utente()->id != $this->volontario) {
            return false;
        }
        
        if ( !empty($motivo) ) {
            $this->note = $this->note .' Partecipazione attullata dal volontario: "'.$motivo.'".';
        }
        
        return true;
    }
    
    
    /**
     * Conferma la presenza al corso
     * @return bool false se la prenotazione non è confermata, altrimenti true
     */
    public function conferma() {
        global $sessione;

        if ($this->stato != PARTECIPAZIONE_ACCETTATA) {
            return false;
        }

        if (!$this->modificabile()) {
            return false;
        }
        
        try {
            $c = Corso::id($this->corso);
        } catch(Exception $e) {
            return false;
        }
        
        if (!$c->finito()) {
            return false;
        }
            
        if ($sessione->utente()->id != $c->direttore) {
            return false;
        }
        
        $this->stato = PARTECIPAZIONE_CONFERMATA;
        return true;
    }
    
    /**
     * 
     * 
     * @global type $db
     * @global type $conf
     * @param type $ruoli
     * @param type $limiteGiorni
     * @return \Volontario
     */
    public static function inattiviPerRuolo($ruoli, $limiteGiorni) {
        global $db, $conf;
 
        $lista = array();
        for($i = 0; $i < sizeof($ruoli); $i++){
            array_push($lista, ":ruolo_{$i}");
        }
        $inQuery = implode(",", $lista);
        
        /*
         * QUERY CON QUALIFICHE E RUOLI
         */
        
        $sql  = "SELECT p.volontario AS id, group_concat(q.nome) AS qualifiche, group_concat(p.ruolo) AS ruoli ";
        $sql .= " FROM " . static::$_t . " p, crs_corsi c, crs_tipoCorsi t, crs_qualifiche q ";
        $sql .= "   WHERE t.qualifica = q.id AND t.id = c.tipo AND p.corso = c.id AND p.ruolo IN ({$inQuery})";
        $sql .= "       AND DATEDIFF(now(), FROM_UNIXTIME(c.inizio)) > :giorni";
        $sql .= "       AND p.volontario NOT IN (";
        $sql .= "           SELECT p.volontario AS id ";
        $sql .= "               FROM " . static::$_t . " p, crs_corsi c, crs_tipoCorsi t, crs_qualifiche q ";
        $sql .= "               WHERE t.qualifica = q.id AND t.id = c.tipo AND p.corso = c.id AND p.ruolo IN ({$inQuery})";
        $sql .= "                   AND DATEDIFF(now(), FROM_UNIXTIME(c.inizio)) <= :giorni )";
        $sql .= " GROUP BY p.volontario";
        
        $query = $db->prepare($sql);
        $query->bindParam(':giorni', $limiteGiorni);
        for($i = 0; $i < sizeof($ruoli); $i++){
            $query->bindParam(":ruolo_{$i}", $ruoli[$i]);
        }
        
        $query->execute();
       
        
        $risultati = array();
        while ( $riga = $query->fetch(PDO::FETCH_ASSOC) ) {
            $id = $riga['id'];
            
            
            $qualifiche = $riga['qualifiche'];
            $ruoli = $riga['ruoli'];
            $tmp =  new Volontario($id);
            foreach(explode(",", $qualifiche) as $q){
                $tmp->aggiungiQualifica($q);
            }
            foreach(explode(",", $ruoli) as $r){
                $tmp->aggiungiRuolo($r);
            }
            
            array_push($risultati, $tmp);
        }
        
        return $risultati;
    }
    
    
    /**
     * 
     * 
     * @global type $db
     * @global type $conf
     * @param type $ruoli
     * @param type $limiteGiorni
     * @return \Volontario
     */
    public static function istruttoriPotenziali() {
        global $db, $conf;
 
        $ruoli = array(CORSO_RUOLO_AFFIANCAMENTO);
        $limiteGiorni = 365;
        
        
        $lista = array();
        for($i = 0; $i < sizeof($ruoli); $i++){
            array_push($lista, ":ruolo_{$i}");
        }
        $inQuery = implode(",", $lista);
        
        /*
         * QUERY CON QUALIFICHE E RUOLI
         */
        
        $sql  = "SELECT 
            p.volontario
        FROM
            crs_partecipazioni_corsi p,
            crs_corsi c,
            crs_tipoCorsi t,
            crs_ruoli r,
            crs_qualifiche q
        WHERE
			r.id = t.ruoloAttestato AND 
            t.qualifica = q.id AND t.id = c.tipo
                AND p.corso = c.id
                AND r.id = 6
                AND DATEDIFF(NOW(), FROM_UNIXTIME(c.inizio)) <= 365
			AND p.volontario NOT IN (
				SELECT volontario FROM crs_titoliCorsi tc, crs_tipoCorsi t 
					WHERE tc.titolo = t.id AND t.ruoloAttestato = 4
            )";
        
        $query = $db->prepare($sql);
        $query->bindParam(':giorni', $limiteGiorni);
        for($i = 0; $i < sizeof($ruoli); $i++){
            $query->bindParam(":ruolo_{$i}", $ruoli[$i]);
        }
        
        $query->execute();
       
        
        $risultati = array();
        while ( $riga = $query->fetch(PDO::FETCH_ASSOC) ) {
            $id = $riga['id'];
            
            
            $qualifiche = $riga['qualifiche'];
            $ruoli = $riga['ruoli'];
            $tmp =  new Volontario($id);
            foreach(explode(",", $qualifiche) as $q){
                $tmp->aggiungiQualifica($q);
            }
            foreach(explode(",", $ruoli) as $r){
                $tmp->aggiungiRuolo($r);
            }
            
            array_push($risultati, $tmp);
        }
        
        return $risultati;
    }

}
