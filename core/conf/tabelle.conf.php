<?php

/*
 * ©2012 Croce Rossa Italiana
 */

$conf['database']['tables'] = [
        [
            'name'      =>  'sessioni',
            'fields'    =>  '
                id       varchar(128) PRIMARY KEY,
                utente   int,
                azione   varchar(64),
                ip       varchar(64),
                agent    varchar(255)
            '
        ],
        [
            'name'      =>  'datiSessione',
            'fields'    =>  '
                id       varchar(128),
                nome     varchar(32),
                valore   text,
                PRIMARY KEY (id, nome)
            '
        ],
        [
            'name'      =>  'anagrafica',
            'fields'    =>  '
                id              int PRIMARY KEY,
                nome            varchar(255),
                cognome         varchar(255),
                stato           varchar(8),
                email           varchar(255),
                password        varchar(127),
                codiceFiscale   varchar(16),
                timestamp       varchar(64),
                foto                 text,
                admin           varchar(8)
            '
        ],
        [
            'name'      =>  'dettagliPersona',
            'fields'    =>  '
                id       varchar(128),
                nome     varchar(32),
                valore   text,
                PRIMARY KEY (id, nome)
            '
        ],
        [
            'name'      =>  'comitati',
            'fields'    =>  '
                id       int PRIMARY KEY,
                nome     varchar(64),
                colore   varchar(8)
            '
        ],
        [
            'name'      =>  'avatar',
            'fields'    =>  '
                id          int PRIMARY KEY,
                utente      varchar(64),
                timestamp   varchar(8)
            '
        ],
        [
            'name'      =>  'appartenenza',
            'fields'    =>  '
                id          int PRIMARY KEY,
                volontario  varchar(16),
                comitato    varchar(16),
                stato       varchar(8),
                inizio      varchar(64),
                fine        varchar(64),
                timestamp   varchar(64),
                conferma    varchar(64)
            '
        ],
        [
            'name'      =>  'titoli',
            'fields'    =>  '
                id          int PRIMARY KEY,
                nome        varchar(255),
                tipo        varchar(8),
                FULLTEXT ( nome )
            '
        ],
        [
            'name'      =>  'titoliPersonali',
            'fields'    =>  '
                id              int PRIMARY KEY,
                volontario      varchar(16),
                titolo          varchar(16),
                inizio          varchar(64),
                fine            varchar(64),
                luogo   varchar(64),
                codice varchar(64),
                tConferma       varchar(64),
                pConferma       varchar(64)
            '
        ],
        [
            'name'      =>  'attivita',
            'fields'    =>  '
                id          int,
                nome            varchar(255),
                luogo           varchar(255),
                comitato        varchar(32),
                pubblica        varchar(8),
                inizio          varchar(64),
                fine            varchar(64),
                responsabile    varchar(32),
                PRIMARY KEY (id, nome)
            '
        ],
        [
            'name'      =>  'dettagliAttivita',
            'fields'    =>  '
                id       varchar(128),
                nome     varchar(32),
                valore   text,
                PRIMARY KEY (id, nome)
            '
        ]
];