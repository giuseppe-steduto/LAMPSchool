<?php

session_start();

/*
  Copyright (C) 2015 Pietro Tamburrano
  Questo programma è un software libero; potete redistribuirlo e/o modificarlo secondo i termini della
  GNU Affero General Public License come pubblicata
  dalla Free Software Foundation; sia la versione 3,
  sia (a vostra scelta) ogni versione successiva.

  Questo programma é distribuito nella speranza che sia utile
  ma SENZA ALCUNA GARANZIA; senza anche l'implicita garanzia di
  POTER ESSERE VENDUTO o di IDONEITA' A UN PROPOSITO PARTICOLARE.
  Vedere la GNU Affero General Public License per ulteriori dettagli.

  Dovreste aver ricevuto una copia della GNU Affero General Public License
  in questo programma; se non l'avete ricevuta, vedete http://www.gnu.org/licenses/
 */

@require_once("../php-ini" . $_SESSION['suffisso'] . ".php");
@require_once("../lib/funzioni.php");

// istruzioni per tornare alla pagina di login se non c'� una sessione valida
////session_start();
$tipoutente = $_SESSION["tipoutente"]; //prende la variabile presente nella sessione
if ($tipoutente == "")
{
    header("location: ../login/login.php?suffisso=" . $_SESSION['suffisso']);
    die;
}

$titolo = "Aggiunta sospensione colloqui";
$script = "";
stampa_head($titolo, "", $script, "MSPA");
stampa_testata("<a href='../login/ele_ges.php'>PAGINA PRINCIPALE</a> - <a href='sospensione.php'>Sospensione</a> - $titolo", "", "$nome_scuola", "$comune_scuola");

$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome) or die("Errore durante la connessione: " . mysqli_error($con));

$data = stringa_html('data');
$note = stringa_html('note');



$query = "insert into tbl_sospensionicolloqui(data,note)
        values ('" . data_to_db($data) . "','$note')";
$ris = eseguiQuery($con, $query);

print ("

       

        <form method='post' action='../colloqui/sospensioni.php' id='formsosp'>
   
         
        </form> 
      
        <SCRIPT language='JavaScript'>
                 {
                     document.getElementById('formsosp').submit();
                 }
         </SCRIPT>  
      
      ");
stampa_piede("");

