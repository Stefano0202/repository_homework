<?php
    // Oggetto per la gestione di un file XML con DOM
    class GestoreXMLDOM
    {
        // Attributi della classe
        // Pathname del file (privato)
        // Oggetto DOM per utilizzo del documento XML
        // Flag per check errori
        protected $pathname = "";
        protected $oggettoDOM = null;
        protected $errori = true;

        // Costruttore
        function __construct($str, $modalita_valutazione)
        {
            // Tentativo di apertura del documento
            if ( file_exists($str) )
            {
                $this->pathname = $str;
                $xmlString = "";
                foreach ( file($str) as $node )
                    $xmlString .= trim($node);

                // Istanziazione dell'oggetto DOM
                $this->oggettoDOM = new DOMDocument();
                $this->oggettoDOM->loadXML($xmlString);

                // Validazione del contenuto XML
                if (($modalita_valutazione == 0 && $this->oggettoDOM->validate()) ||
                        ($modalita_valutazione == 1 && $this->oggettoDOM->schemaValidate("../xml/partiteSchema.xsd")))
                    $this->errori = false;
            }
        }

        // Metodo per verificare usabilita' oggetto DOM
        function checkValidita()
        {
            return !$this->errori;
        }

        // Metodi getter
        function getOggettoDOM()
        {
            if ( $this->checkValidita() )
                return $this->oggettoDOM;
            else
                return null;
        }
        
        // Metodo per salvare il contenuto nel file XML
        // Se non viene passato il pathname (stringa vuota) viene sfruttato quello
        // da cui e' stato creato l'oggetto DOM
        function salvaXML($path)
        {
            if ( $this->checkValidita() )
            {
                if ( strlen($path) == 0 )
                    $path = $this->pathname;
                $this->oggettoDOM->save($path);
            }
        }
    }

    class GestoreXMLDOMSquadre extends GestoreXMLDOM
    {
        // Costruttore che fa riferimento alla superclasse
        function __construct($str, $mod)
        {
            parent::__construct($str, $mod);
        }

        // Metodo per ottenere la lista di squadre
        // dal file XML
        function getListaSquadre()
        {
            if ( $this->checkValidita() )
            {
                return $this->getOggettoDOM()->documentElement->childNodes;
            }
            else
                return null;
        }

        // Metodo per ottenere nome della squadra a partire dall'id
        function getNomeSquadra($id)
        {
            if ( $this->checkValidita() && strlen($id) > 0 )
            {
                return $this->getOggettoDOM()->getElementById($id)->textContent;
            }
            else
                return "";
        }
    }

    class GestoreXMLDOMPartite extends GestoreXMLDOM
    {
        // Costruttore che fa riferimento alla superclasse
        function __construct($str, $mod)
        {
            parent::__construct($str, $mod);
        }

        // Metodo per ottenere la lista di partite
        // dal file XML
        function getListaPartite()
        {
            if ( $this->checkValidita() )
            {
                return $this->getOggettoDOM()->documentElement->childNodes;
            }
            else
                return null;
        }

        // Metodo per ricercare una partita nel file XML
        // Ritorna un valore booleano
        function ricercaPartita($sq_casa, $sq_ospite, $data)
        {
            $esito = false;

            // Elenco di partite
            $partite = $this->oggettoDOM->documentElement->childNodes;

            // Ciclo di ricerca
            for ( $i=0; $i < count($partite) && !$esito; $i++ )
            {
                // Ottengo i campi dalla partita
                $p = $partite[$i];
                $sc = $p->firstChild;
                $so = $sc->nextSibling;
                $d = $p->lastChild->textContent;

                if ( $sq_casa == $sc->textContent && $sq_ospite == $so->textContent && $d == $data )
                    $esito = true;
            }
            
            return $esito;
        }

        // Metodo per inserire una partita nel file XML
        // Ritorna un valore booleano
        function inserisciPartita($sq_casa, $sq_ospite, $goal_casa, $goal_ospite, $data)
        {
            $esito = false;
            
            // Verifico validita oggetto DOM e mantengo vincolo di unicita' della partita
            if ( $this->checkValidita() && !$this->ricercaPartita($sq_casa, $sq_ospite, $data) )
            {
                // Creazione nuova partita
                $nuova_partita = $this->oggettoDOM->createElement("partita");
                $nuova_sqcasa = $this->oggettoDOM->createElement("squadraCasa", $sq_casa);
                $nuova_sqosp = $this->oggettoDOM->createElement("squadraOspite", $sq_ospite);
                $nuova_golcasa = $this->oggettoDOM->createElement("golCasa", $goal_casa);
                $nuova_golosp = $this->oggettoDOM->createElement("golOspite", $goal_ospite);
                $nuova_data = $this->oggettoDOM->createElement("data", $data);

                // Creazione della struttura gerarchica
                $nuova_partita->appendChild($nuova_sqcasa);
                $nuova_partita->appendChild($nuova_sqosp);
                $nuova_partita->appendChild($nuova_golcasa);
                $nuova_partita->appendChild($nuova_golosp);
                $nuova_partita->appendChild($nuova_data);

                // Aggancio della partita
                $this->oggettoDOM->documentElement->appendChild($nuova_partita);
                $esito = true;
            }

            return $esito;
        }
    }
?>