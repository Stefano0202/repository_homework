<?php
    require 'gestoriXML/gestoreDomande.php';
    require 'gestoriXML/gestoreRisposte.php';

    $g1 = new GestoreRisposte();
    $g = new GestoreDomande();

    /*$dom = $g->ottieniDomanda('1');
    $cont = $dom->firstChild->textContent;
    echo $cont;*/

    $e = $g1->verificaPresenzaRispostaFaq('1');
    if($e){
        echo "presente";
    }
    else 
        echo "assente";
    $e = $g1->verificaPresenzaRispostaFaq('9');
    if($e){
        echo "presente";
    }
    else 
        echo "assente";
?>