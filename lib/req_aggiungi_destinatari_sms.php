<?php

if (strpos($rec['telcel'], "+") != FALSE)
{
    $dest = array();
    $destinatarialunno = array();
    $destinatarialunno = explode("+", $rec['telcel']);
    foreach ($destinatarialunno as $destalu)     // AGGIUNGE UN INVIO PER OGNI CELLULARE
    {
        $dest['recipient'] = "39" . trim($destalu); // .$rec['telcel'];
        $dest['nome'] = $rec['nome'] . " " . $rec['cognome'];
        $iddest[] = $rec['idalunno'];
        $destinatari[] = $dest;
        $contasmsass++;
        $invio = true;
    }
} else
{
    $dest = array();
    $destinatarialunno = array();
    $destinatarialunno = explode(",", $rec['telcel']);
    $destalu = $destinatarialunno[0];
    $dest['recipient'] = "39" . trim($destalu); // .$rec['telcel'];
    $dest['nome'] = $rec['nome'] . " " . $rec['cognome'];
    $iddest[] = $rec['idalunno'];
    $destinatari[] = $dest;
    $contasmsass++;
    $invio = true;
}

