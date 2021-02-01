<?php

  if (isset($_GET['device']) && !empty($_GET['device']) && 
	  isset($_GET['value']) && !empty($_GET['value']) && 
	  isset($_GET['time']) && !empty($_GET['time'])) {
	// Ajouter dans la DB
	$sensorValue = $_GET['value'];
	$device = $_GET['device'];
    $time = $_GET['time'];
    
    $convertedHex = hex2bin($sensorValue);
    
    $temperature = substr($convertedHex, 0, 5);
    $humidity = substr($convertedHex, 5);
	
    echo "Message : <br> " . $temperature . "°C, $humidity, envoyé par $device à $time";
    
    var_dump(addValues($device, $time, $temperature, $humidity));
  } else {
	// rien
	echo "Pas OK";
  }

  function conn_db() {
    $user='nh489_adder';
    $pass='Admocal2!';
    $base='nh489_db_capteurs';

    $dsn='mysql:host=nh489.myd.infomaniak.com;dbname='.$base.';charset=UTF8';
    try
    {

        $dbh = new PDO($dsn, $user, $pass);
        /*** les erreurs sont gérées par des exceptions ***/
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
    catch (PDOException $e)
    {
        print "erreur ! :". $e->getMessage()."<br/>";
        die();
    }
  }

  function addValues($device, $time, $temperature, $humidity){
    try {
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "INSERT INTO tb_test (device, time, temperature, humidity)
			    VALUES (:device, :time, :temperature, :humidity);";

        //préparation de la requête sur le serveur
        $stmt = $dbh -> prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':device', $device, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':temperature', $date, PDO::PARAM_STR);
        $stmt->bindParam(':humidity', $phone, PDO::PARAM_STR);
        // $stmt->bindParam("ssdd", $device, $time, $temperature, $humidity);

        //exécution de la requête
        $stmt -> execute();

        // retourne le nombre de ligne affectée
        return $stmt -> rowCount();

    }
    catch(PDOException $e){
        echo $e -> getMessage();
        die();
    }
  }
