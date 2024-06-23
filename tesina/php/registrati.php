<?php
    require_once 'parametriStile.php';

    $margine_popup = $margine_popup_mostra;
    $background_popup = $colore_background_popup_rosso;
    $display_popup = $opzione_display_popup_mostra;

    // Variabili utili all'identificazione dell'errore
    $msg = ''; $err = true;

    // Import del popup per comunicare errore o meno
    // I settings della finestra sono ottenuti preliminarmente a seconda della richiesta pervenuta
    $popup = file_get_contents("../html/popupErrore.html");

    // Verifico se l'utente è già loggato
    session_start();
    if (isset($_SESSION["nome"]))
        header("Location: area_riservata.php");    // Se sì, viene ridirezionato nell'area personale
    else if( isset($_POST["nome"]) && isset($_POST["cognome"]) && isset($_POST["citta"]) 
            && isset($_POST["indirizzo"]) && isset($_POST["mail"]) && isset($_POST["username"])
             && isset($_POST["password"])
        )
    {   
        // Verifico che i campi siano stati riempiti
        if ( strlen(trim($_POST["nome"])) > 0 && strlen(trim($_POST["cognome"])) > 0 &&
            strlen(trim($_POST["indirizzo"])) > 0 && strlen(trim($_POST["citta"])) > 0 &&
            strlen(trim($_POST["mail"])) > 0 && strlen(trim($_POST["username"])) > 0 &&
            strlen(trim($_POST["password"])) > 0
        )
        {
            // Arrivato qui, ho la certezza che i campi non sono vuoti

            // Elimino la sessione appena creata erroneamente
            require_once 'cancellaSessione.php';

            // Effettuo la connessione al database
            require_once 'connection.php';

            // Verifico che la connessione sia andata a buon fine
            if ( $connessione )
            {
                $msg = '<p>Errore nell\'esecuzione della query, ricontrollare i dati</p>'; 

                // Verifico che i dati siano corretti

                // Regex per il controllo della mail
                $regex = '/^([a-z]+)(_|[.]|-){0,1}(([a-z]|\d)+)@([a-z])*[.]([a-z])*$/';
                if ( preg_match($regex, $_POST["mail"]) )
                {
                    // Prelevo i dati dal post
                    $nome = $handleDB->real_escape_string($_POST["nome"]);
                    $cognome = $handleDB->real_escape_string($_POST["cognome"]);
                    $indirizzo = $handleDB->real_escape_string($_POST["indirizzo"]);
                    $citta = $handleDB->real_escape_string($_POST["citta"]);
                    $mail = $handleDB->real_escape_string($_POST["mail"]);
                    $username = $handleDB->real_escape_string($_POST["username"]);
                    $password = $handleDB->real_escape_string($_POST["password"]);

                    // Compongo la query per l'esecuzione
                    $q = "insert into $tb_utenti(nome, cognome, indirizzo, citta, cap, data_registrazione, 
                    username, mail, password, saldo_standard) values ('$nome', '$cognome', '$indirizzo', '$citta', 
                    '00000', DATE(NOW()), '$username', '$mail', SHA2('$password', 256), 0)";

                    // Esecuzione della query
                    try
                    {
                        $handleDB->query($q);
                        $msg = '<p>Registrazione avvenuta con successo</p>';
                        $err = false;
                    }
                    catch (Exception $e)
                    {
                        $err = true;
                        if ($handleDB->errno == 1062 )
                            $msg = '<p>e-mail o usernam gi$agrave; presente nel database</p>';
                    }
                }

                // Chiusura della connessione al database
                $handleDB->close();
            }
            else 
                $msg = '<p>Errore di comunicazione con il database</p>';
        }
        else {
            $msg = '<p> Campi vuoti </p>';   // Se qualche campo risulta vuoto, lo segnalo
        }
    }
    else // Elimino la sessione appena creata erronamente
    {
        require_once 'cancellaSessione.php';
        $err = false;
        echo "ooo";
        // Nascondo la barra per i popup
        $popup = str_replace("%OPZIONE_DISPLAY_POPUP%", $opzione_display_popup_nascondi, $popup);
    }

    echo '<?xml version = "1.0" encoding="UTF-8" ?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="../css/stileLayout.css" type="text/css" />
        <link rel="stylesheet" href="../css/stileCatalogo.css" type="text/css" />
        <link rel="stylesheet" href="../css/stileSidebar.css" type="text/css" />
        <link rel="stylesheet" href="../css/stileRegistrati.css" type="text/css" />
        <link rel="stylesheet" href="../css/stilePopup.css" type="text/css" />
        <link rel="icon" type="image/x-icon" href="../img/logo.png" />
        <script type="text/javascript" src="../js/utility.js"></script>
        <title>UNI-TECNO</title>
    </head>

    <body>
        <?php
            // Import della navbar
            // Nascondo il bottone registrati
            // Mostro il bottone accedi
            $nav = file_get_contents("../html/strutturaNavbarVisitatori.html");
            $nav = str_replace("%OPZIONE_DISPLAY_REGISTRATI%", "none", $nav);
            $nav = str_replace("%OPZIONE_DISPLAY_ACCEDI%", "block", $nav);
            echo $nav ."\n";

            // Import della sidebar e mostro solo le opzioni del visitatore
            $sidebar = file_get_contents("../html/strutturaSidebar.html");
            $sidebar = str_replace("%OPERAZIONI_UTENTE%", "", $sidebar);
            echo $sidebar . "\n";
        ?>

        <!-- FORM DI REGISTRAZIONE -->
        <div id="sezioneRegistrazione">
            <?php

                // Gestione della finestra popup
                if ($err){
                    $popup = str_replace("%CONTENUTO_FINESTRA_POPUP%", $msg, $popup);
                    $popup = str_replace("%OPZIONE_DISPLAY_POPUP%", $display_popup, $popup);
                    $popup = str_replace("%MARGINE_DESTRO_POPUP%", $margine_popup, $popup);
                    $popup = str_replace("%COLORE_SFONDO_POPUP%", $background_popup, $popup);
                    echo $popup . "\n";
                }
                else
                {
                    $popup = str_replace("%CONTENUTO_FINESTRA_POPUP%", $msg, $popup);
                    $popup = str_replace("%OPZIONE_DISPLAY_POPUP%", $display_popup, $popup);
                    $popup = str_replace("%MARGINE_DESTRO_POPUP%", $margine_popup, $popup);
                    $popup = str_replace("%COLORE_SFONDO_POPUP%", $colore_background_popup_verde, $popup);
                    echo $popup . "\n";
                }
            ?>

            <form id="formRegistrazione" method="post" action="<?php echo $_SERVER["PHP_SELF"] ?>">
                <fieldset>  <p>Nome:        </p>  <input type="text" name="nome" <?php if($err) echo 'value="'. $_POST["nome"] . '"';?>/>    </fieldset>
                <fieldset>  <p>Cognome:     </p>  <input type="text" name="cognome" <?php if($err) echo 'value="'. $_POST["cognome"] . '"';?>/> </fieldset>
                <fieldset>  <p>Indirizzo:   </p>  <input type="text" name="indirizzo" <?php if($err) echo 'value="'. $_POST["indirizzo"] . '"';?>/>    </fieldset>
                <fieldset>  <p>Citt&agrave;:</p>  <input type="text" name="citta" <?php if($err) echo 'value="'. $_POST["citta"] . '"';?>/>    </fieldset>
                <fieldset>  <p>Username:    </p>  <input type="text" name="username" <?php if($err) echo 'value="'. $_POST["username"] . '"';?>/>    </fieldset>
                <fieldset>  <p>Mail:        </p>  <input type="text" name="mail" <?php if($err) echo 'value="'. $_POST["mail"] . '"';?>/>    </fieldset>
                <fieldset>  <p>Password:    </p>  <input type="password" name="password"/>    </fieldset>
                
                <fieldset> <input type="reset" value="Cancella" />
                <input type="submit" value="Invia" name="btnRegistrati" /> </fieldset>
            </form>
        </div>
    </body>
</html>