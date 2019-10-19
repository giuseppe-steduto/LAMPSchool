<?php
$token = "987901422:AAG-T0WEzGDy_jYqfe5e2xNqEh0PPXUcv3g"; //Token bot Telegram

// Passaggio dei parametri nella sessione
		require "../lib/funzioni.php";
		require '../php-ini.php';

    /*
      Funziona che invia un messaggio specificato in $testo alla chat con id
      $chat_id.

      Restituisce la risposta del server in formato JSON
    */
    function sendMessage($chat_id, $testo, $token) {
      $data = array("chat_id" => $chat_id, "text" => $testo);
      $data = json_encode($data);
      $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
      //Registrare chat_id nel database al docente corrispondente all'user inserito
      $options = array(
          'http' => array(
              'header'  => "Content-type: application/json",
              'method'  => 'POST',
              'content' => $data
          )
      );
      $context  = stream_context_create($options);
      $r = file_get_contents($url, false, $context);
      return $r;
    }

    function generaCodice() {
        return rand(10000, 99999);
    }

    //Fai la richiesta HTTP al bot Telegram per vedere se è tutto okay
    if(!isBotOnline($token)) {
        exit();
    }
    //Interrogo il bot per vedere se ho dei messaggi arrivati:
    $json = file_get_contents('php://input');
	if($json == "") {
		echo "<br />Nessuna richiesta arrivata!<br />";
		exit();
	}
    // Converts it into a PHP object
    $messaggio = json_decode($json);
    $text = $messaggio->{"message"}->{"text"};
    $file = fopen("myfile.txt", "w") or die("Unable to open file!");
    fwrite($file, $text);
	fclose($file);

    $chat_id = $messaggio->{"message"}->{'chat'}->{'id'};
    //Ricavo le credenziali dal messaggio, che sarà tipo "nomeutente password"
    $credenziali = explode(" ", $text);
    if(count($credenziali) != 2) { //Se il formato del messaggio è sbagliato
        $testo = "Credenziali non scritte correttamente.";
        sendMessage($chat_id, $testo, $token);
    }
    else {
		$seme = md5(date('Y-m-d'));
        $user = $credenziali[0];
        $pass = $credenziali[1];
		$pass = md5(md5(md5($pass)) . $seme);
        //Controllo user e pass
        //Ovviamente questo andrà fatto dal database

        $con = mysqli_connect($db_server, $db_user, $db_password, $db_nome) or die("Errore connessione");
		require '../lib/req_assegna_parametri_a_sessione.php';
        $indirizzoip = IndirizzoIpReale();
        $_SESSION['indirizzoip'] = $indirizzoip;
        $seme = md5(date('Y-m-d'));
        $ultimoaccesso = "";
        //  $_SESSION['versione']=$versione;
        //Connessione al server SQL
        if (!$con) {
            die("<h1> Connessione al server fallita </h1>");
        }

        //$username = stringa_html('utente');
        //$pass = stringa_html('password');

        // VERIFICO SE IP VIENE DA TOR

        $query = "select * from tbl_torlist where indirizzo LIKE '$indirizzoip%'";
        $ris = eseguiQuery($con, $query, false);
        if (mysqli_num_rows($ris) > 0)
        {
            inserisci_log("LAMPSchool§" . date('m-d|H:i:s') . "§" . $indirizzoip . "§Bloccato Accesso TOR: $user -" . $pass . "§" . $_SERVER['HTTP_USER_AGENT']);
            header("location: login.php?messaggio=Utente sconosciuto");
            die;
        }

        $accessouniversale = false;
        @$fp = fopen("lampschool/unikey.txt", "r");
        if ($fp)
        {
            $unikey = fread($fp, 32);
            //print $unikey;
            //print md5($pass);
            if (md5($unikey . $seme) == $pass)
            {
                $accessouniversale = true;
                $_SESSION['accessouniversale'] = true;
            }
        }

		if ($pass != md5(md5($chiaveuniversale) . $seme) & (!$accessouniversale))
		{
		    $sql = "SELECT *,unix_timestamp(ultimamodifica) AS ultmod FROM tbl_utenti WHERE userid='" . $user . "' AND  md5(concat(password,'$seme'))='" . elimina_apici($pass) . "'";
		} else
		{
		    $sql = "SELECT *,unix_timestamp(ultimamodifica) AS ultmod FROM tbl_utenti WHERE userid='" . $username . "'";
		}

        $result = eseguiQuery($con, $sql);

        if (mysqli_num_rows($result) > 0) // VERIFICO SE C'E' L'UTENTE
        {
          $sql = "UPDATE tbl_utenti SET idtelegram = ".$chat_id." WHERE userid='" . elimina_apici($user) . "' AND  md5(concat(password,'$seme'))='" . elimina_apici($pass) . "'";
          $result = eseguiQuery($con, $sql);
          sendMessage($chat_id, "Registrazione effettuata!", $token); //va registrato nei log
          $testo = "<b>ATTENZIONE</b> </ br> Per motivi di sicurezza cancellare il messagio in cui si inviano le proprie credenziali. Grazie!";
          sendMessage($chat_id, $testo, $token);
        }
        else {
			sendMessage($chat_id, "Accesso fallito!", $token); //va registrato nei log
		}
    }
 ?>
