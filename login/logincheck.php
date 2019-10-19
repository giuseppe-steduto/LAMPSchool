<?php

session_start();

require_once '../php-ini' . $_SESSION['suffisso'] . '.php';
require_once '../lib/funzioni.php';
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

/* Programma per il controllo dell'accesso. */


$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome) or die("Errore connessione");

// Passaggio dei parametri nella sessione
require "../lib/req_assegna_parametri_a_sessione.php";




$indirizzoip = IndirizzoIpReale();

$_SESSION['indirizzoip'] = $indirizzoip;


$seme = md5(date('Y-m-d'));

$ultimoaccesso = "";


//  $_SESSION['versione']=$versione;
//Connessione al server SQL


if (!$con)
{
    die("<h1> Connessione al server fallita </h1>");
}

$username = stringa_html('utente');
$password = stringa_html('password');

// VERIFICO SE IP VIENE DA TOR

$query = "select * from tbl_torlist where indirizzo LIKE '$indirizzoip%'";
$ris = eseguiQuery($con, $query);
if (mysqli_num_rows($ris) > 0)
{
    inserisci_log("LAMPSchool§" . date('m-d|H:i:s') . "§" . $indirizzoip . "§Bloccato Accesso TOR: $username - $password§" . $_SERVER['HTTP_USER_AGENT']);
    header("location: login.php?messaggio=Utente sconosciuto&suffisso=" . $_SESSION['suffisso']);
    die;
}



$accessouniversale = false;

@$fp = fopen("../unikey.txt", "r");
if ($fp)
{
    $unikey = fread($fp, 32);
    //print $unikey;
    //print md5($password);
    if (md5($unikey . $seme) == $password)
    {
        $accessouniversale = true;
        $_SESSION['accessouniversale'] = true;
    }
}

if ($password != md5(md5($chiaveuniversale) . $seme) & (!$accessouniversale))
{
    $sql = "SELECT *,unix_timestamp(ultimamodifica) AS ultmod FROM tbl_utenti WHERE userid='" . $username . "' AND  md5(concat(password,'$seme'))='" . elimina_apici($password) . "'";
} else
{
    $sql = "SELECT *,unix_timestamp(ultimamodifica) AS ultmod FROM tbl_utenti WHERE userid='" . $username . "'";
}

$result = eseguiQuery($con, $sql);

if (mysqli_num_rows($result) <= 0) // VERIFICO SE C'E' L'UTENTE
{
    // VERIFICO SE L'ACCESSO E' QUELLO PER GLI ESAMI DI STATO
    if (($username == 'esamidistato' && $password == md5($passwordesame . $seme)) | $accessouniversale)
    {
        // die("Sono qui!");
        $_SESSION['tipoutente'] = 'E';
        $_SESSION['userid'] = 'ESAMI';
        $_SESSION['idutente'] = 'esamedistato';

        $_SESSION['cognome'] = "Esame ";
        $_SESSION['nome'] = "di stato";

        inserisci_log("LAMPSchool§" . date('m-d|H:i:s') . " §" . IndirizzoIpReale() . "§Accesso ESAMI");
    } else
    {
        if ($_SESSION['suffisso'] != "")
        {
            $suff = $_SESSION['suffisso'] . "/";
        } else
            $suff = "";
        inserisci_log("LAMPSchool§" . date('m-d|H:i:s') . " §" . IndirizzoIpReale() . "§Accesso errato: $username - $password");

        header("location: login.php?messaggio=Utente sconosciuto&suffisso=" . $_SESSION['suffisso']);
        die;
    }
}
else  // UTENTE TROVATO
{
    $data = mysqli_fetch_array($result);
    $_SESSION['idtelegram'] = $data['idtelegram'];
    if(isBotOnline($token)){ //bot_telegram controllo se il bot è attivo grazie alla funzione implementanta in ../lib/funzioni.php
      $telegram_otp = rand(10000, 99999); //genero l'otp per telegram a 5 cifre numeriche
      if(sendTelegramMessage($_SESSION['idtelegram'], $telegram_otp, $token)){
        //va all'inserimento dell'otp per confermare l'accesso
      }
      else {
        //scrive nel file di log che l'operazione non è andata a buob fine
      }
    }
    $_SESSION['userid'] = $data['userid'];
    $_SESSION['tipoutente'] = $data['tipo'];
    $_SESSION['sostegno'] = docente_sostegno($data['idutente'], $con);
    $_SESSION['idutente'] = $data['idutente'];
    $_SESSION['dischpwd'] = $data['dischpwd'];
    $passdb = $data['password'];  // TTTT per controllo iniziale alunni
    // print "Data: $dataultimamodifica - Ora: $dataodierna";
    // print "Diff: $giornidiff";




    if ($_SESSION['tipoutente'] == 'T')
    {
        //  $sql = "SELECT * FROM tbl_tutori WHERE idutente='" . $_SESSION['idutente'] . "'";
        $sql = "SELECT * FROM tbl_alunni WHERE idalunno='" . $_SESSION['idutente'] . "'";
        $ris = eseguiQuery($con, $sql);

        if ($val = mysqli_fetch_array($ris))
        {
            $_SESSION['idstudente'] = $val["idalunno"];
            $_SESSION['cognome'] = $val["cognome"];
            $_SESSION['nome'] = $val["nome"];
        }
    }

    if ($_SESSION['tipoutente'] == 'L')
    {
        //print "PASSDB: $passdb";
        //  $sql = "SELECT * FROM tbl_tutori WHERE idutente='" . $_SESSION['idutente'] . "'";
        $sql = "SELECT * FROM tbl_alunni WHERE idalunno='" . ($_SESSION['idutente'] - 2100000000) . "'";

        $ris = eseguiQuery($con, $sql);

        if ($val = mysqli_fetch_array($ris))
        {
            $_SESSION['idstudente'] = $val["idalunno"];
            $_SESSION['cognome'] = $val["cognome"];
            $_SESSION['nome'] = $val["nome"];
            $_SESSION['codfiscale'] = $val['codfiscale'];
        }
    }

    if ($_SESSION['tipoutente'] == 'D' | $_SESSION['tipoutente'] == 'S' | $_SESSION['tipoutente'] == 'P')
    {
        $sql = "SELECT * FROM tbl_docenti WHERE idutente='" . $_SESSION['idutente'] . "'";
        $ris = eseguiQuery($con, $sql);

        if ($val = mysqli_fetch_array($ris))
        {
            $_SESSION['cognome'] = $val["cognome"];
            $_SESSION['nome'] = $val["nome"];
        }
        // VERIFICO SE C'E' UNA DEROGA PER IL LIMITE DI INSERIMENTO
        $sql = "SELECT * FROM tbl_derogheinserimento WHERE iddocente='" . $_SESSION['idutente'] . "' AND DATA='" . date('Y-m-d') . "'";
        $ris = eseguiQuery($con, $sql);

        if (mysqli_num_rows($ris) > 0)
        {
            $_SESSION['derogalimite'] = true;
        } else
        {
            $_SESSION['derogalimite'] = false;
        }
    }

    if ($_SESSION['tipoutente'] == 'A')
    {
        $sql = "SELECT * FROM tbl_amministrativi WHERE idutente='" . $_SESSION['idutente'] . "'";
        $ris = eseguiQuery($con, $sql);

        if ($val = mysqli_fetch_array($ris))
        {
            $_SESSION['cognome'] = $val["cognome"];
            $_SESSION['nome'] = $val["nome"];
        }
    }

    if ($_SESSION['tipoutente'] == "S" | $_SESSION['tipoutente'] == "D")
    {
        $_SESSION['cattsost'] = cattedre_sostegno($_SESSION['idutente'], $con);
        $_SESSION['cattnorm'] = cattedre_normali($_SESSION['idutente'], $con);
    }

    if ($_SESSION['tipoutente'] == 'M')
    {
        // $idscuola = md5($nomefilelog);
        // print "<iframe style='visibility:hidden;display:none' src='http://www.lampschool.net/test/testesist.php?ids=$idscuola&nos=$nome_scuola&cos=$comune_scuola&ver=$versioneprecedente&asc=$annoscol'></iframe>";
    }
    //
    //  AZIONI PRIMO ACCESSO DELLA GIORNATA
    //
    if ($modocron == "acc")
    {
        $query = "SELECT dataacc FROM tbl_logacc
                   WHERE idlog = (SELECT max(idlog) FROM tbl_logacc)";
        $ris = eseguiQuery($con, $query);
        $rec = mysqli_fetch_array($ris);
        $dataultimoaccesso = $rec['dataacc'];
        $dataultimo = substr($dataultimoaccesso, 0, 10);
        //print $dataultimo;
        $dataoggi = date("Y/m/d");
        //print $dataoggi;
        if ($dataoggi > $dataultimo)
        {
            daily_cron($_SESSION['suffisso'], $con, '110100');
        }
    }
    //
    //  FINE AZIONI PRIMO ACCESSO DELLA GIORNATA
    //


        // Inserimento nel log dell'accesso
    if ($_SESSION['suffisso'] != "")
    {
        $suff = $_SESSION['suffisso'] . "/";
    } else
        $suff = "";
    inserisci_log("LAMPSchool§" . date('m-d|H:i:s') . "§" . IndirizzoIpReale() . "§Accesso: $username - $password§" . $_SERVER['HTTP_USER_AGENT']);

    // Ricerca ultimo accesso
    $query = "select dataacc from " . $_SESSION["prefisso"] . "tbl_logacc where idlog=(select max(idlog) from " . $_SESSION["prefisso"] . "tbl_logacc where utente='$username' and comando='Accesso')";
    $ris = eseguiQuery($con, $query, false);
    if (mysqli_num_rows($ris) == 0)
    {
        $ultimoaccesso = "";
    } else
    {
        $rec = mysqli_fetch_array($ris);
        $ultimoaccesso = $rec['dataacc'];
        $dataultaccute = substr($ultimoaccesso, 0, 10);
        $oraultaccute = substr($ultimoaccesso, 13, 5);
        $giornoultaccute = giorno_settimana($dataultaccute);
        $ultimoaccesso = $giornoultaccute . " " . data_italiana($dataultaccute) . " h. " . $oraultaccute;
    }
    // Inserimento dell'accesso in tabella
    // $indirizzoip = IndirizzoIpReale();
    // $_SESSION['indirizzoip'] = $indirizzoip;
    if ($password != md5(md5($chiaveuniversale) . $seme) & (!$accessouniversale))
    {
        $sql = "INSERT INTO " . $_SESSION["prefisso"] . "tbl_logacc( utente , dataacc, comando,indirizzo) values('$username','" . date('Y/m/d - H:i') . "','Accesso','$indirizzoip')";
    } else
    {
        $sql = "INSERT INTO " . $_SESSION["prefisso"] . "tbl_logacc( utente , dataacc, comando,indirizzo) values('$username','" . date('Y/m/d - H:i') . "','Chiave universale','$indirizzoip')";
    }

    eseguiQuery($con, $sql, false);
}


mysqli_close($con);

header("location: ele_ges.php?suffisso=" . $_SESSION['suffisso']);
