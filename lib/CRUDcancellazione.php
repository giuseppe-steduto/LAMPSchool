<?php

session_start();

/*
  Copyright (C) 2015 Pietro Tamburrano
  Questo programma è un software libero; potete redistribuirlo e/o modificarlo secondo i termini della
  GNU Affero General Public License come pubblicata
  dalla Free Software Foundation; sia la versione 3,
  sia (a vostra scelta) ogni versione successiva.

  Questo programma è distribuito nella speranza che sia utile
  ma SENZA ALCUNA GARANZIA; senza anche l'implicita garanzia di
  POTER ESSERE VENDUTO o di IDONEITA' A UN PROPOSITO PARTICOLARE.
  Vedere la GNU Affero General Public License per ulteriori dettagli.

  Dovreste aver ricevuto una copia della GNU Affero General Public License
  in questo programma; se non l'avete ricevuta, vedete http://www.gnu.org/licenses/
 */

/* Programma per la visualizzazione dell'elenco delle tbl_classi. */

@require_once("../php-ini" . $_SESSION['suffisso'] . ".php");
@require_once("../lib/funzioni.php");

// istruzioni per tornare alla pagina di login 
////session_start();
$tipoutente = $_SESSION["tipoutente"]; //prende la variabile presente nella sessione
if ($tipoutente == "")
{
    header("location: ../login/login.php?suffisso=" . $_SESSION['suffisso']);
    die;
}


$daticrud = $_SESSION['daticrud'];
$titolo = "Cancellazione " . $daticrud['aliastabella'];
$script = "";
stampa_head($titolo, "", $script, "MAPSD");
stampa_testata("<a href='../login/ele_ges.php'>PAGINA PRINCIPALE</a> - <a href='CRUD.php'>ELENCO</a> - $titolo", "", "$nome_scuola", "$comune_scuola");

$id = stringa_html('id');

$daticrud = $_SESSION['daticrud'];
//ordina_array_su_campo_sottoarray($daticrud['campi'], 7);
$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome) or die("Errore connessione!");

// COSTRUZIONE QUERY DI INSERIMENTO



$querydel = "delete from " . $daticrud['tabella'] . " where " . $daticrud['campochiave'] . " = '$id'";
print $querydel;
eseguiQuery($con, $querydel);
inserisci_log($_SESSION['userid'] . "§" . date('m-d|H:i:s') . "§" . $_SESSION['indirizzoip'] . "§" . $querydel . "");

// TTTT Aggiungere al log

header("location: ../lib/CRUD.php?suffisso=" . $_SESSION['suffisso']);

stampa_piede("");
mysqli_close($con);


