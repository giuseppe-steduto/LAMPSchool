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

$idclasse = stringa_html('idclasse');

$idalu = stringa_html('idalu');

$titolo = "Rigenerazione password alunni";
$script = "";
$con = mysqli_connect($db_server, $db_user, $db_password, $db_nome) or die("Errore durante la connessione: " . mysqli_error($con));
$idcl = estrai_classe_alunno($idalu, $con);

stampa_head($titolo, "", $script, "SMPA");
if ($_SESSION['tipoutente'] == 'M')
    stampa_testata("<a href='../login/ele_ges.php'>PAGINA PRINCIPALE</a> - <a href='alu_rigenera_password.php'>Rigenera password</a> -  $titolo", "", "$nome_scuola", "$comune_scuola");
else
    stampa_testata("<a href='../login/ele_ges.php'>PAGINA PRINCIPALE</a> - <a href='../alunni/vis_alu.php?idcla=$idcl'>Elenco alunni</a> - $titolo", "", "$nome_scuola", "$comune_scuola");
$annoscolastico = $annoscol . "/" . ($annoscol + 1);


if ($idclasse != 0)
{
    $query = "select idclasse,anno,sezione,specializzazione from tbl_classi where idclasse=$idclasse";
    $ris = eseguiQuery($con, $query);

    $val = mysqli_fetch_array($ris);

    print "<center><b>Elenco password per alunni della classe: " . $val['anno'] . $val['sezione'] . " " . $val['specializzazione'] . "</b></center><br/><br/>";
}
print "<form name='stampa'  target='_blank' action='../alunni/alu_stampa_pass_alu.php' method='POST'>";

print "<table align='center' border='1'><tr><td><b>Alunno</b></td><td><b>Utente</b></td><td><b>Password</b></td></tr>";

if ($idclasse != 0)
{

    $query = "SELECT * FROM tbl_alunni
			  WHERE idclasse='" . $idclasse . "' ORDER BY cognome,nome,datanascita";
    
} else
{

    $query = "SELECT * FROM tbl_alunni
        WHERE idalunno='$idalu'";
   
}




$nf = session_id() . ".csv";
$nomefile = "$cartellabuffer/" . $nf;
$fp = fopen($nomefile, 'w');
$numpass = 0;
$arr_id = "";
$arr_ut = "";
$arr_pw = "";
$risalunni = eseguiQuery($con, $query);
//print ("Righe ".mysqli_num_rows($risalunni));
while ($valalunni = mysqli_fetch_array($risalunni))
{
    $numpass++;

// VERIFICO SE C'E' GIA' L'UTENTE ALUNNO
    $idalu = $valalunni['idalunno'];
    $query = "SELECT * FROM tbl_utenti
        WHERE idutente='" . ($idalu + 2100000000) . "'";
    $risute = eseguiQuery($con, $query);
    $utente = "al" . $_SESSION['suffisso'] . $idalu;
    if (mysqli_num_rows($risute) == 0)
    {
        $idutente = $idalu + 2100000000;

        $query = "insert into tbl_utenti(idutente,userid, tipo) values ($idutente,'$utente','L')";
        eseguiQuery($con, $query);
    }

    print ("
					 <tr>
						 <td>" . $valalunni['cognome'] . " " . $valalunni['nome'] . " (" . data_italiana($valalunni['datanascita']) . ")" . "</td>");

    $idalunno = $idalu;

    $pass = creapassword();
    $arr_id .= "$idalunno|";
    $arr_ut .= "$utente|";
    $arr_pw .= "$pass|";
    print ("<td>$utente</td><td>$pass</td></tr>");
    $qupd = "update tbl_utenti set password=md5('" . md5($pass) . "') where idutente=($idalunno+2100000000)";
    eseguiQuery($con, $qupd);

    if ($tokenservizimoodle != '')
    {
        $idmoodle = getIdMoodle($tokenservizimoodle, $urlmoodle, $utente);

        cambiaPasswordMoodle($tokenservizimoodle, $urlmoodle, $idmoodle, $utente, $pass);
    }
    //print "qui";
    fputcsv($fp, array($valalunni['cognome'], $valalunni['nome'], data_italiana($valalunni['datanascita']), $utente, $pass), ";");
    //print "qua";
}
print("</table>");
$arr_id = substr($arr_id, 0, strlen($arr_id) - 1);
$arr_ut = substr($arr_ut, 0, strlen($arr_ut) - 1);
$arr_pw = substr($arr_pw, 0, strlen($arr_pw) - 1);
print "<input type='hidden' name='arrid' value='$arr_id'> 
       <input type='hidden' name='arrut' value='$arr_ut'> 
       <input type='hidden' name='arrpw' value='$arr_pw'> 
       <input type='hidden' name='numpass' value='$numpass'> 
       <center><br><input type='submit' value='STAMPA COMUNICAZIONI'></center>
       </form>";
fclose($fp);


print ("<br/><center><a href='$cartellabuffer/$nf'><img src='../immagini/csv.png'></a></center>");

stampa_piede("");
mysqli_close($con);



