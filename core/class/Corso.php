<?php
/*
 * ©2013 Croce Rossa Italiana
 */
/**
 * Rappresenta un Corso.
 */
class Corso extends GeoEntita {

    protected static
        $_t  = "crs_corsi",
        $_dt = "crs_dettagliCorsi",
        $_jt_lezioni = "crs_giornataCorso", 
        $_jt_iscrizioni = "crs_partecipazioni_corsi";

    use EntitaCache;

    /*
    public function __construct($tmp) 
    {
        $this->tmp = $tmp;
        
        $this->titolo = 'Corso BLSD FULL MOCK' . ' - Formazione CRI su Gaia';
        $this->descrizione = 'Ravenna' . ' || Aperto a: ' . 'BLABLABLA'.' || Organizzato da ' . 'Marco Radossi';
        $this->luogo = 'Ravenna';
        $this->timestamp = date("t");
        $this->comitato = 'Comitato:'.$tmp;
    }
     * 
     */
    
    public function verificato($correggi=false) {
        
        if (!is_numeric($this->partecipanti)) {
            if ($correggi) {
                $this->partecipanti = 0;
            }
            return false;
        }
    }
    
    /**
     * Genera il codice numerico progressivo del corso sulla base dell'anno attuale
     *
     * @return int|false $progressivo     Il codice progressivo, false altrimenti 
     */
    public function assegnaProgressivo() {
        /*
        if ($this->progressivo) {
            return false;
        }
        $anno = $this->inizio()->format('Y');
        $progressivo = $this->generaProgressivo('progressivo', [["anno", $anno]]);
        $this->progressivo = $progressivo;
        return $progressivo;
        */
        $this->generaSeriale(intval($this->anno), $this->tipo);
    }
    
    /**
     * Aggiorna lo stato interno in base ai dati posseduti
     */
    public function aggiornaStato() {
        $err = 0;
        switch ($this->stato) {
            case CORSO_S_ANNULLATO:
            case CORSO_S_CONCLUSO:
                break;
            case CORSO_S_ATTIVO:
                if ($this->finito()) {
                    $this->stato = CORSO_S_CONCLUSO;
                    break;
                }
                
                $tipo = $this->tipo();
                
                $var = $this->organizzatore;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_ORGANIZZATORE_MANCANTE;
                }
                $var = $this->responsabile;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_RESPONSABILE_MANCANTE;
                }
                $var = $this->direttore;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_DIRETTORE_MANCANTE;
                }
                $var = $this->partecipanti;
                if (intval($var)<=0) {
                    $err |= CORSO_VALIDAZIONE_NESSUN_PARTECIPANTE;
                }
                $var = $this->partecipanti;
                if ($var > $this->numeroDocenti() * $tipo->proporzioneIstruttori) {
                    $err |= CORSO_VALIDAZIONE_TROPPI_PARTECIPANTI;
                }
                if ($this->numeroDocentiNecessari() != $this->numeroDocenti()) {
                    $err |= CORSO_VALIDAZIONE_ERRATO_NUMERO_DOCENTI;
                }
                
                if ($this->numeroDocenti() < $this->numeroAffiancamenti()) {
                    $err |= CORSO_VALIDAZIONE_TROPPI_AFFIANCAMENTI;
                }

                if ($err!=0) {
                    $this->stato = CORSO_S_DACOMPLETARE;
                }
                break;
            case CORSO_S_DACOMPLETARE:
                $tipo = $this->tipo ();
                
                $var = $this->organizzatore;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_ORGANIZZATORE_MANCANTE;
                }
                $var = $this->responsabile;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_RESPONSABILE_MANCANTE;
                }
                $var = $this->direttore;
                if (empty($var)) {
                    $err |= CORSO_VALIDAZIONE_DIRETTORE_MANCANTE;
                }
                $var = $this->partecipanti;
                if (intval($var)<=0) {
                    $err |= CORSO_VALIDAZIONE_NESSUN_PARTECIPANTE;
                }
                $var = $this->partecipanti;
                if ($var > $this->numeroDocenti() * $tipo->proporzioneIstruttori) {
                    $err |= CORSO_VALIDAZIONE_TROPPI_PARTECIPANTI;
                }
                if ($this->numeroDocentiNecessari() != $this->numeroDocenti()) {
                    $err |= CORSO_VALIDAZIONE_ERRATO_NUMERO_DOCENTI;
                }
                
                if ($this->numeroDocenti() < $this->numeroAffiancamenti()) {
                    $err |= CORSO_VALIDAZIONE_TROPPI_AFFIANCAMENTI;
                }

                if ($err==0) {
                    $this->stato = CORSO_S_ATTIVO;
                }
                break;
        }
        return $err;
    }

    /*
    public function area()
    {
        return Area::id(5);
    }
    
    public function postiLiberi()
    {
        return 20;
    }
    
    public function filtraPerDati($tipologie, $province)
    {
        return "http://".$_SERVER['SERVER_NAME'].'?'.$_SERVER['QUERY_STRING'];
    }
    
    public function trovaVicini($latitude, $longitudine, $raggio = 50)
    {
        return "http://".$_SERVER['SERVER_NAME'].'?'.$_SERVER['QUERY_STRING'];
    }
    
    public function linkMappa()
    {
        return "http://".$_SERVER['SERVER_NAME'].'?'.$_SERVER['QUERY_STRING'];
    }
    */
    
    /**
     * Ritorna l'organizzatore del corso base
     * @return GeoPolitica
     */
    public function organizzatore() {
    	return GeoPolitica::daOid($this->organizzatore);
    }

    /**
     * Ritorna l'organizzatore del corso base
     * @return GeoPolitica
     */
    public function presidente() {
    	$comitato = GeoPolitica::daOid($this->organizzatore);
        return $comitato->primoPresidente();
    }
    
    /**
     * Ritorna il responsabile del corso
     * @return GeoPolitica
     */
    public function responsabile() {
    	return Volontario::id($this->responsabile);
    }

    /**
     * Ritorna la data di inizio del corso base
     * @return DT
     */
    public function inizio() {
    	return DT::daTimestamp($this->inizio);
    }

    /**
     * Ritorna la data dell'esame
     * @return DT
     */
    public function fine() {
        return DT::daTimestamp($this->tEsame);
    }

    /**
     * Ritorna la data dell'esame in epoch time
     * @return int
     */
    public function fineDate() {
        return $this->tEsame;
    }

    /**
     * Controlla se il corso e' futuro (non iniziato)
     * @return bool
     */
    public function modificabile() {
        if (!$this->inizio) {
            return false;
        }

        $inizio = intval($this->inizio);
        $oggi = (new DT())->getTimestamp();
        $buffer = GIORNI_CORSO_NON_MODIFICABILE * 86400;
        return (($inizio-$oggi) > $buffer);
    }

    /**
     * Controlla se il corso e' futuro (non iniziato)
     * @return bool
     */
    public function modificabileFinoAl() {
        if (!$this->inizio) {
            return false;
        }

        $inizio = $this->inizio;
        $buffer = GIORNI_CORSO_NON_MODIFICABILE * 86400;
        
        return new DT('@'.($inizio - $buffer));
    }

    /**
     * True se il corso è attivo e non iniziato
     * @return bool     stato del cors
     */
    public function accettaIscrizioni() {
        if (!$this->inizio) {
            return false;
        }

        $inizio = $this->inizio;
        $oggi = (new DT())->getTimestamp();
        $buffer = GIORNI_CORSO_ISCRIZIONI_CHIUSE * 86400;
        
        return (($oggi-$inizio) > $buffer);
    }
    
    /**
     * Controlla se il corso e' futuro (non iniziato)
     * @return bool
     */
    public function futuro() {
    	return $this->inizio() > new DT;
    }

    /**
     * Controlla se il corso e' iniziato
     * @return bool
     */
    public function iniziato() {
    	return !$this->futuro();
    }

    /**
     * Controlla se il corso e' finito
     * @return bool
     */
    public function finito() {
        return $this->fine() < new DT; 
    }

    /**
     * Controlla se il corso e' concluso (finito e fatto esame)
     * @return bool
     */
    public function concluso() {
        //return $this->finito() && 
        return $this->stato == CORSO_S_CONCLUSO; 
    }

    /**
     * Controlla se il corso e' da completare (email non mandata)
     * @return bool
     */
    public function daCompletare() {
        return (bool) ($this->stato == CORSO_S_DACOMPLETARE); 
    }

    /**
     * Localizza nella sede del comitato organizzatore
     *
    public function localizzaInSede() {
    	$sede = $this->organizzatore()->coordinate();
    	$this->localizzaCoordinate($sede[0], $sede[1]);
    }//*/
    
    /**
     * Restituisce il nome del corso
     * @return string     il nome del corso
     */
    public function nome() {
        $tipocorso = TipoCorso::id($this->tipo);
        return "Corso di ".$tipocorso->nome;
    }

    /**
     * Informa se un corso è non concluso
     * @return bool     false se concluso, true altrimenti
     */
    public function attuale() {
        if($this->stato > CORSO_S_CONCLUSO)
            return true;
        return false;
    }

    /**
     * Informa se un corso è modificabile da un determianto utente
     * @return bool 
     */
    public function modificabileDa(Utente $u) {
        if($u->admin()) return true;
        return (bool) (
                $u->id == $this->direttore
            ||  contiene($this->id, 
                    array_map(function($x) {
                        return $x->id;
                    }, $u->corsiInGestione())
                )
        );

    }

    /**
     * Informa se un corso è cancellabile da un determianto utente
     * @return bool 
     */
    public function cancellabileDa(Utente $u) {
        return (bool) contiene($this, $u->corsiInGestione());
    }

    /**
     * Restituisce il direttore di un corso
     * @return Volontario 
     */
    public function direttore() {
        if ($this->direttore) {
            return Volontario::id($this->direttore);    
        }
        return null;
    }

    /**
     * Restituisce l'area di un corso
     * @return Area 
     */
    public function area() {
        return Area::id($this->area);
    }
    
    /**
     * Restituisce il progressivo del corso in questione, se
     * mancante lo genera
     * @return string|false 
     */
    public function progressivo() {
        if ( !$this->progressivo )
            $this->assegnaProgressivo();
        if($this->progressivo) {
            return 'CORSO-'.$this->anno.'/'.$this->progressivo;
        }
        return null;
    }


    /**
     * Verfica se un utente è iscritto o no al corso
     * @return bool
     */
    public function iscritto(Utente $u) {
        $p = PartecipazioneCorso::filtra([
            ['volontario', $u->id],
            ['corso', $this->id]
            ]);
        foreach($p as $_p) {
            if($_p->attiva()) {
                return true;
            }
        }
        return false;
    }

    
    /**
     * Elenco delle partecipazioni (con qualsiasi ruolo)
     * @return PartecipazioneCorso elenco delle partecipazioni dei discenti 
     */
    public function partecipazioni() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    /**
     * Elenco delle partecipazioni (con qualsiasi ruolo)
     * @return PartecipazioneCorso elenco delle partecipazioni dei discenti 
     */
    public function partecipazioniPotenziali() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['stato', PARTECIPAZIONE_RICHIESTA]
        ]);
    }

    
    /**
     * Elenco dei discenti ad un corso 
     * @return Utente elenco dei discenti 
     */
    public function discenti() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DISCENTE],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    /**
     * Elenco dei discenti ad un corso 
     * @return Utente elenco dei discenti 
     */
    public function discentiPotenziali() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DISCENTE],
            ['stato', PARTECIPAZIONE_RICHIESTA]
        ]);
    }

    
    /**
     * Numero dei discenti ad un corso 
     * @return int numero dei discenti 
     */
    public function numeroDiscenti() {
        return PartecipazioneCorso::conta([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DISCENTE],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    /**
     * Numero dei discenti ad un corso 
     * @return int numero dei discenti 
     */
    public function postiLiberi() {
        return $this->partecipanti - PartecipazioneCorso::conta([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DISCENTE],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    public function numeroDocenti() {
        return PartecipazioneCorso::conta([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DOCENTE],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    /**
     * Numero dei discenti ad un corso 
     * @return int numero dei discenti 
     */
    public function numeroDocentiMancanti() {
        return $this->numeroDocentiNecessari() - $this->numeroDocenti();
    }

    
    public function numeroDocentiNecessari() {
        return ceil( $this->partecipanti / max(1,$this->tipo()->proporzioneIstruttori) );
    }
    
    
    /*
     * Funzione repository per recuperare docenti di un corso
     */
    public function docenti() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DOCENTE],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }
    
    
    /*
     * Funzione repository per recuperare docenti di un corso
     */
    public function docentiPotenziali() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_DOCENTE],
            ['stato', PARTECIPAZIONE_RICHIESTA]
        ]);
    }
    
    
    /*
     * Funzione repository per recuperare docenti di un corso
     */
    public function numeroDocentiPotenziali() {
        return 
            PartecipazioneCorso::conta([
                ['corso', $this->id],
                ['ruolo', CORSO_RUOLO_DOCENTE],
                ['stato', PARTECIPAZIONE_RICHIESTA]
            ])
            +
            PartecipazioneCorso::conta([
                ['corso', $this->id],
                ['ruolo', CORSO_RUOLO_DOCENTE],
                ['stato', PARTECIPAZIONE_ACCETTATA]
            ])
            ;
    }
    
    
    /*
     * Funzione repository per recuperare docenti di un corso
     */
    public function affiancamenti() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_AFFIANCAMENTO],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }
       
    
    /*
     * Funzione repository per recuperare docenti di un corso
     */
    public function affiancamentiPotenziali() {
        return PartecipazioneCorso::filtra([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_AFFIANCAMENTO],
            ['stato', PARTECIPAZIONE_RICHIESTA]
        ]);
    }

    
    public function numeroAffiancamenti() {
        return PartecipazioneCorso::conta([
            ['corso', $this->id],
            ['ruolo', CORSO_RUOLO_AFFIANCAMENTO],
            ['stato', PARTECIPAZIONE_ACCETTATA, OP_GTE]
        ]);
    }

    
    /**
     * Cancella il corso e tutto ciò che c'è di associato
     */
    public function cancella() {
        PartecipazioneCorso::cancellaTutti([['corso', $this->id]]);
        Lezione::cancellaTutti([['corso', $this->id]]);
        
        parent::cancella();
    }

    /**
     * Se il corso è attivo e non ci sono partecipanti
     * allora è cancellabile
     * @return bool
     */
    public function cancellabile() {
        if ($this->stato == CORSO_S_DACOMPLETARE) {
            return true;
        }
        return (bool) ($this->stato == CORSO_S_ATTIVO && $this->numDiscenti() == 0);
    }
    
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function generaVerbale($risultati) {

        //$file  = $risultato->timestamp;
        $nomefile = $this->seriale.".pdf";
  
     
        $comitato = $this->organizzatore();
        $tipo = TipoCorso::id($this->tipo);
      
        if( $comitato->principale ) {
            $comitato = $comitato->locale()->nome;
        }else{
            $comitato = $comitato->nomeCompleto();
        }
 
        $p = new PDF('crs_verbale', $nomefile);
        $p->_COMITATO     = maiuscolo($comitato);
        $p->_CORSO        = $tipo->nome;
        $p->_PROGRESSIVO  = $this->seriale;
        $p->_DIRETTORE    = $this->direttore()->nomeCompleto();
        $p->_PRESIDENTE   = $this->presidente()->nomeCompleto();
        //$p->_VOLONTARIO   = $iscritto->nomeCompleto();
        $p->_DATAESAME    = date('d/m/Y', $this->inizio);
        $p->_DATA         = date('d/m/Y', time());
        $p->_LUOGO        = $this->organizzatore()->comune;
        
        $text = "";
        foreach($risultati as $r){
            $text .= "".$r->volontario()->nomeCompleto().", idoneo:".$r->idoneita."<br/>";
        }
        $p->_TESTO = $text;
        
        $file = $p->salvaFile(null, true);
        
        
        return $file;
    }
    
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function inviaVerbale($f) {
        $comitato = $this->organizzatore();
        if( $comitato->principale ) {
            $comitato = $comitato->locale()->nome;
        }else{
            $comitato = $comitato->nomeCompleto();
        }
        
        $tipo = TipoCorso::id($this->tipo);
       
        $m = new Email('crs/inviaVerbale', "Verbale".$this->seriale);
        //$m->a = $aut->partecipazione()->volontario();
        //$m->da = "pizar79@gmail.com";
        $m->a = $this->direttore();
        $m->allega($f, true);
        $m->invia(true);
        
        return $f;
    }

    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function generaAttestato($risultato, $iscritto, $verbale) {
        // leggo i settaggi per il corso specifico
        $settings = Utility::parse_ini(CORSI_INI, true);
        
        // verifico il sesso del volontario
        $sesso = null;
        if ( $iscritto->sesso == UOMO ){
            $sesso = "Volontario";
        }else{
            $sesso = "Volontaria";
        }

        $nomefile = $iscritto->nomeCompleto().".pdf";
  
        $comitato = $this->organizzatore();
        $regione = $this->organizzatore()->regione();
        $provincia = $this->organizzatore()->provincia();
        if( $comitato->principale ) {
            $comitato = $comitato->locale()->nome;
        }else{
            $comitato = $comitato->nomeCompleto();
        }
        $tipo = TipoCorso::id($this->tipo);
 
        // verifico il template da usare
        $logoCustom = "";
        $templateAttestato = $settings["TIPOCORSO_".$this->tipo]["TEMPLATE_FILE"];
        $orientamento = $settings["TIPOCORSO_".$this->tipo]["TEMPLATE_ORIENTAMENTO"];
        if (!empty($settings["TIPOCORSO_".$this->tipo][$regione])){
            $templateAttestato = $settings["TIPOCORSO_".$this->tipo]["TEMPLATE_FILE_V2"];
            $logoCustom = $settings["TIPOCORSO_".$this->tipo][$regione];
        }
        if (!empty($settings["TIPOCORSO_".$this->tipo][$provincia])){
            $templateAttestato = $settings["TIPOCORSO_".$this->tipo]["TEMPLATE_FILE_V2"];
            $logoCustom = $settings["TIPOCORSO_".$this->tipo][$provincia];
        }
        if (is_object($logoCustom) || is_array($logoCustom)){
            $logoCustomWidth = $logoCustom["w"];
            $logoCustomHeight = $logoCustom["h"];
            $logoCustom = $logoCustom["url"];
        }
         
        $logoCustom = $regione."_".$provincia;
        $p = new PDF($templateAttestato, $nomefile);
        $p->_VERBALEPROGRESSIVO = $verbale["progressivo"];
        $p->_VERBALEDATA     = $verbale["data"];
        $p->_COMITATO     = maiuscolo($comitato);
        $p->_CORSO        = $tipo->nome;
        $p->_PROGRESSIVO  = substr($this->seriale, 5) ;
        $p->_DIRETTORE    = $this->direttore()->nomeCompleto();
        $p->_RAPPRESENTANTE = $this->direttore()->nomeCompleto();
        $p->_PRESIDENTE   = $this->presidente()->nomeCompleto();
        $p->_SERIALE      = $risultato->seriale;
        $p->_ANNO         = date('Y', time());
        $p->_CF           = $iscritto->codiceFiscale;
        $p->_VOLONTARIO   = $iscritto->nomeCompleto();
        $p->_DATAESAME    = date('d/m/Y', $this->inizio);
        $p->_DATA         = date('d/m/Y', time());
        $p->_LUOGO        = $this->organizzatore()->comune;
        $p->_VOLON        = $sesso;
        $p->_LOGOCUSTOMWIDTH  = empty($logoCustomWidth) ? 300 : $logoCustomWidth;
        $p->_LOGOCUSTOMHEIGHT = empty($logoCustomHeight) ? 270 : $logoCustomHeight;
        $p->_LOGOCUSTOM   = $logoCustom;
         
        $file = $p->salvaFile(null, true, $orientamento);
       
        
        return $file;
    }
    
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function inviaAttestato($risultato, $iscritto, $f) {
        //$iscritto = Volontario::id("2");
        
        $sesso = null;
        if ( $iscritto->sesso == UOMO ){
            $sesso = "Volontario";
        }else{
            $sesso = "Volontaria";
        }
       
        $comitato = $this->organizzatore();
        if( $comitato->principale ) {
            $comitato = $comitato->locale()->nome;
        }else{
            $comitato = $comitato->nomeCompleto();
        }
        
        $tipo = TipoCorso::id($this->tipo);
       
        $m = new Email('crs/invioAttestato', "Invio Certificato" );
        //$m->a = $aut->partecipazione()->volontario();
        //$m->da = "pizar79@gmail.com";
        $m->a = $iscritto;
        $m->_COMITATO     = maiuscolo($comitato);
        $m->_CF           = $iscritto->codiceFiscale;
        $m->_CORSO        = $tipo->nome;
        $m->_SERIALE      = $risultato->seriale;
        $m->_VOLONTARIO   = $iscritto->nomeCompleto();
        $m->_DATAESAME    = date('d/m/Y', $this->tEsame);
        $m->_DATA         = date('d/m/Y', time());
        $m->_LUOGO        = $this->organizzatore()->comune;
        $m->_VOLON        = $sesso;
        $m->allega($f, true);
        $m->invia(true);
        
        return $f;
    }

    
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function inviaCreazioneCorso() {
        //$iscritto = Volontario::id("2");
        
        $sesso = null;
        /*
        if ( $iscritto->sesso == UOMO ){
            $sesso = "Volontario";
        }else{
            $sesso = "Volontaria";
        }
        */
        $comitato = $this->organizzatore();
        //$tipo = TipoCorso::id($this->tipo);
       
        $m = new Email("crs_inviaCreazioneCorso", "Corso Creato");
        $a = $comitato->regionale()->email;
		print_r($email);
		// "email:".$email;
        if (empty($email)){
            $a = Volontario::id("2");
        }
        $m->a = $a;
        //$m->a = $aut->partecipazione()->volontario();
        //$m->da = "pizar79@gmail.com";
        // $m->a = $comitato;
        /*
        $m->_COMITATO     = maiuscolo($comitato);
        $m->_CF           = $iscritto->codiceFiscale;
        $m->_CORSO        = $tipo->nome;
        //$m->_SERIALE    = $risultato->seriale;
        $m->_VOLONTARIO   = $iscritto->nomeCompleto();
        $m->_DATAESAME    = date('d/m/Y', $this->tEsame);
        $m->_DATA         = date('d/m/Y', time());
        $m->_LUOGO        = $this->organizzatore()->comune;
        $m->_VOLON        = $sesso;
         * 
         */
        $m->invia(true);
        
        return ;
    }
    
    
    /**
     * Genera attestato, sulla base del corso e del volontario
     * @return PDF 
     */
    public function inviaRichiestaIscrizione(Volontario $iscrivente, $dati=array()) {
        //$iscritto = Volontario::id("2");
        
        $sesso = null;
        if ( $iscrivente->sesso == UOMO ){
            $sesso = "Volontario";
        }else{
            $sesso = "Volontaria";
        }
       
        $tipo = TipoCorso::id($this->tipo);
       
        $m = new Email('crs/richiestaIscrizione', "Richiesta di iscrizione ad un corso" );
        //$m->a = $aut->partecipazione()->volontario();
        //$m->da = "pizar79@gmail.com";
        $m->a = $this->presidente()->email;
        //$m->a = 'marco.radossi@gmail.com';
        $m->_NOME         = $this->presidente()->nomeCompleto();
        $m->_VOLONTARIO   = $iscrivente->nomeCompleto();
        $m->_CF           = $iscrivente->codiceFiscale;
        $m->_CORSO        = $tipo->nome;
        $m->_DATA         = $this->inizio()->format('d/m/Y');
        $m->_LUOGO        = $this->luogo;
        
        $m->_DATI_NOME    = @normalizzaNome($dati['inputNome']).' '.@normalizzaNome($dati['inputCogome']);
        $m->_DATI_TELEFONO    = @normalizzaNome($dati['inputNome']).' '.@normalizzaNome($dati['inputCogome']);
        $m->_DATI_EMAIL    = @($dati['inputTelefono']);
        $m->_DATI_RICHIESTA    = @($dati['inputRichiesta']);
        $m->invia(true);
        
        return $f;
    }
    
    
    /**
     * Genera scheda valutazione, sulla base del corso e del volontario
     * @return PDF 
     */
    public function generaScheda($iscritto) {
        
        $pb = PartecipazioneCorso::filtra([
                ['volontario', $iscritto],
                ['corso', $this],
                ['stato', PARTECIPAZIONE_EFFETTUATA_SUCCESSO]
            ]);

        $pb = array_merge( $pb, PartecipazioneCorso::filtra([
                ['volontario', $iscritto],
                ['corsoBase', $this],
                ['stato', PARTECIPAZIONE_EFFETTUATA_FALLIMENTO]
            ]));

        $pb = array_unique($pb);
        $pb = $pb[0];

        /* costruisco i testi del pdf secondo regolamento */
        if ($pb->p1){
            $p1 = "Positivo";
        }else{
            $p1 = "Negativo";
        }

        if ($pb->p2){
            $p2 = "Positivo";
        }else{
            $p2 = "Negativo";
        }

        if ( $pb->stato==PARTECIPAZIONE_EFFETTUATA_SUCCESSO ){

            $idoneo = "Idoneo";

        }else{

            $idoneo = "Non Idoneo";

        }

        /* Appongo eventuali X */
        $extra1 = "_";
        $extra2 = "_";

        if ($pb->e1){

            $extra1 = "X";

        }

        if ($pb->e2){

            $extra2 = "X";

        }

        /*testi con sesso già inserito */
        if ($iscritto->sesso==UOMO){

            $candidato = "il candidato";

        }else{

            $candidato = "la candidata";

        }

        $file  = "Scheda valutazione ";
        $file .= $iscritto->nomeCompleto();
        $file .= ".pdf";

        $p = new PDF('schedacorso', $file);
        $p->_COMITATO     = $this->organizzatore()->nomeCompleto();
        $p->_VERBALENUM   = $this->progressivo();
        $p->_DATAESAME    = date('d/m/Y', $this->tEsame);
        $p->_UNOESITO     = $p1;
        $p->_ARGUNO       = $pb->a1;
        $p->_DUEESITO     = $p2;
        $p->_ARGDUE       = $pb->a2;
        $p->_NOMECOMPLETO = $iscritto->nomeCompleto();
        $p->_LUOGONASCITA = $iscritto->comuneNascita;
        $p->_CF           = $iscritto->codiceFiscale;
        $p->_DATANASCITA  = date('d/m/Y', $iscritto->dataNascita);
        $p->_IDONETA      = $idoneo;
        $p->_EXTRAUNO     = $extra1;
        $p->_EXTRADUE     = $extra2;
        $p->_CANDIDATO    = $candidato;
        $f = $p->salvaFile(null,true);
        return $f;
    }


    /**
     * Ritorna la data dell'attivazione del corso se presente
     * Ritorna null se data assente
     * @return DT
     */
    public function dataAttivazione() {
        if ( $this->dataAttivazione ){
            return DT::daTimestamp($this->dataAttivazione)->format('d/m/Y'); 
        }else{
            return null;
        }
    }

    /**
     * Ritorna la data della convocazione della commissione esaminatrice
     * Ritorna null se data assente
     * @return DT
     */
    public function dataConvocazione() {
        if ( $this->dataAttivazione ){
            return DT::daTimestamp($this->dataConvocazione)->format('d/m/Y'); 
        }else{
            return null;
        }
    }

    /**
     * Ritorna la data di termine del corso
     * Ritorna null se data assente
     * @return DT
     */
    public function dataTermine() {
        if ( $this->dataAttivazione ){
            return DT::daTimestamp($this->dataConvocazione)->format('d/m/Y'); 
        }else{
            return null;
        }
    }
    
    /**
     * Creo un array con valori unici in base ad un attributo dei 
     * dati extra dei corsi
     */
    public function tipo() {
        return TipoCorso::id($this->tipo);
    }
    

    /**
     * Ottiene la GeoPolitica corrispondente alla Visibilita' del corso'
     * es., se il corso e' visibile a livello provinciale, ottiene oggetto Provinciale corrispondente
     * @return GeoPolitica
     */
    public function visibilita() {
        global $conf;
        $needle = $conf['est_corso2geopolitica'][(int) $this->visibilita];
        $x = $this->organizzatore();
        while ( $x::$_ESTENSIONE != $needle ) {
            if ( $x instanceOf Nazionale ) {
                throw new Errore();
            }
            $x = $x->superiore();
        }
        return $x;
    }
    
    
    /**
     * Cerca oggetti con le corrispondenze specificate
     *
     * @param array $_array     La query associativa di ricerca tipo, provincia, dataInizio,dataFine, geo
     * @param string $_order    Ordine espresso come SQL
     * @param Volontario $me    volontario loggato
     * @return array            Array di oggetti
     */

    public static function ricerca($_array, $_order = null, Volontario $me = null) {
        global $db, $conf, $cache;

        if ( false && $cache && static::$_versione == -1 ) {
            static::_caricaVersione();            
        }

        if ( $_order ) {
            $_order = 'ORDER BY ' . $_order;
        }

        $select = " ";
        $join = " ";
        $where = "WHERE 1";
        if (!empty($_array["inizio"])) {
            $where .= " AND DATE_FORMAT(FROM_UNIXTIME(inizio), '%Y-%m-%d') > STR_TO_DATE(:inizio, '%Y-%m-%d') ";
        }
        
        /*
        if (!empty($_array["fine"])) {
            $where .= " AND DATE_FORMAT(FROM_UNIXTIME(tEsame), '%Y-%m-%d') < STR_TO_DATE(:fine, '%Y-%m-%d')";
        }
        */
        
        if (!empty($_array["type"])){
            $typeArray = array_fill(0, count($_array["type"]), ':type');
            foreach($typeArray as $i => &$type_tmp){
                $type_tmp = $type_tmp."_".$i;
            }
            $where .= " AND certificato IN (".implode(',', $typeArray).")";
        }
        
        if (!empty($_array["provincia"])){
            $provArray = array_fill(0, count($_array["provincia"]), ':prov');
            foreach($provArray as $i => &$prov_tmp){
                $prov_tmp = $prov_tmp."_".$i;
            }
            $where .= " AND provincia IN (".implode(',', $provArray).")";
        }
        
        if (!empty($_array["coords"]->latitude) && !empty($_array["coords"]->longitude)) {
            $where .= " AND st_distance(point(:long, :lat), geo) < 50";
        }
        
        if (!empty($me)){
            $select = ", g.data AS inizio, g.luogo AS luogoLezione";
            $join  .= " LEFT JOIN ".static::$_jt_lezioni." g ON c.id = g.corso ";
            
            $select .= ", i.ruolo ";      
            $join  .= " RIGHT JOIN ".static::$_jt_iscrizioni." i ON c.id = i.corso ";
            $where .= " AND i.volontario = :me";
        }
        
        $sql = "SELECT c.* $select FROM ".static::$_t." c $join $where $_order";
        //print $sql;
        $hash = null;
        if ( false && $cache && static::$_cacheable ) {
            $hash = md5($sql);
            $r = static::_ottieniQuery($hash);
            if ( $r !== false  ) {
                $cache->incr( chiave('__re') );
                return $r;
            }
        }
        
        $query = $db->prepare($sql);
        if (!empty($_array["inizio"])) {
            $query->bindParam(":inizio", $_array["inizio"], PDO::PARAM_STR) ;
        }
        
        /*
        if (!empty($_array["fine"])) {
            $query->bindParam(":fine", $_array["fine"], PDO::PARAM_STR);
        }
        */
        
        if (!empty($_array["type"])) {
            foreach($_array["type"] as $j => $t_tmp){
                $query->bindParam(":type_".$j, $t_tmp);
            }
        }
        
        if (!empty($_array["provincia"])) {
            foreach($_array["provincia"] as $i => $p_tmp){
                $query->bindParam(":prov_".$i, $p_tmp);
            }
        }
        
        if (!empty($_array["coords"]->latitude) && !empty($_array["coords"]->longitude)) {
            $query->bindParam(":long", $_array["coords"]->longitude);
            $query->bindParam(":lat", $_array["coords"]->latitude);
        }
        
         if (!empty($me)){
            $query->bindParam(":me", $me->id);
        }

        $query->execute();
        
        $t = $c = [];
        while ( $r = $query->fetch(PDO::FETCH_ASSOC) ) {
            $tmp = new Corso($r['id'], $r);
            $t[] = $tmp;
            if ( false ){
                $c[] = $r;
            }
        }
        
        if ( false && $cache && static::$_cacheable ) {
            static::_cacheQuery($hash, $c);
        }
         
        return $t;
    }
    
    /**
     * 
     * @global type $db
     * @param type $yyyy
     * @param type $tipocorsoId
     * @return type
     */
    public function generaSeriale($yyyy, $tipocorsoId) {
        global $db;

        $sql = "UPDATE ".static::$_t." SET seriale = generaSerialeCorso(:yyyy, :tipocorsoId) WHERE id=:id AND seriale IS NULL";
        
        $query = $db->prepare($sql);
        $query->bindParam(":yyyy", $yyyy);
        $query->bindParam(":tipocorsoId", $tipocorsoId);
        $query->bindParam(":id", $this->id);
        $query->execute();
        
        return;
    }
    
    /**
     * Cerca i corsi da chiudere
     *
     * @return array            Array di oggetti
     */
    public static function corsiDaChiudere() {
        return Corso::filtra([["stato", CORSO_S_DA_ELABORARE]]);
    }
    
    /**
     * Ritorna i risultati del corso
     *
     * @return array    Array di oggetti
     */
    public function giornateCorso() {
        return GiornataCorso::filtra([["corso", $this->id]], 'data ASC');
    }
    
    /**
     * Ritorna i risultati del corso
     *
     * @return array    Array di oggetti
     */
    public function risultati() {
        return RisultatoCorso::filtra([["corso", $this->id]]);
    }
    
    
    public function chiudi() {
    // Verifico i corsi da chiudere
        $risultati = $this->risultati();
        
        $contatore = 0;
        
        
        
        
        $verbale = array();
        $verbale["progressivo"] = "001/2015";
        $verbale["data"] = "xx/xx/2015";
        
        foreach($risultati as $risultato){
            $volontario = $risultato->volontario();

            if ($risultato->idoneita == CORSO_RISULTATO_IDONEO && !empty($volontario)){
                
                $risultato->generaSeriale(intval(date("Y", $risultato->timestamp)), $this->tipo);
                $risultato = RisultatoCorso::id($risultato->id);

                $contatore++;
                $f = $this->generaAttestato($risultato, $volontario, $verbale);
                $risultato->file = $f->id;
                $risultato->generato = 1;

                $this->inviaAttestato($risultato, $volontario, $f);
                
                // Aggiunto il titolo al discente che ha superato il corso
                $titoloCorso = new TitoloCorso();
                $titoloCorso->volontario = $volontario->id;
                $titoloCorso->inizio = $risultato->timestamp;
                $titoloCorso->fine = intval($titoloCorso->inizio) + (60 * 60 * 24 * 365);
                $titoloCorso->titolo = $this->tipo;
                $titoloCorso->codice = $risultato->seriale; 
            }
            
        }
        
        // Verbale, generazione e invio
        $f = $this->generaVerbale($risultati);
        $this->verbale = $f->id;
        $this->inviaVerbale($f);
        
        $this->stato = CORSO_S_CHIUSO;
        
        return $contatore;
    }
    
    
    public static function chiudiCorsi() {
    // Verifico i corsi da chiudere
        $corsi = Corso::corsiDaChiudere();
        $contatore = 0;
        
        foreach($corsi as $corso){
            $contatore += $corso->chiudi();
        }
        
        return $contatore;
    }
    
}