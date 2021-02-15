<?php

  function isValidKey($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file);
    foreach ($json as $item) {
        if ($item == $key) {
            return true;
        }
    }

    return false;
  }

  function convertEpoch($time) {
    $formatedTime = new DateTime("@$time");  // convert UNIX timestamp to PHP DateTime
    return $formatedTime->format('Y-m-d H:i:s');
  }

  function conn_db() {
    $dsn = 'mysql:host=nh489.myd.infomaniak.com;dbname=' . DB_NAME . ';charset=UTF8';
    try {
        $dbh = new PDO($dsn, DB_USER, DB_PWD);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
    catch (PDOException $e) {
        print "error ! :" . $e->getMessage() . "<br>";
        die();
    }
  }

  function getAllSensors() {
    // récupération de tous les enregistrements
    try {
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT dev_id, loc.loc_name
                FROM tb_devices
                INNER JOIN tb_locations loc ON fk_dev_loc = loc.pk_loc;";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //exécution de la requête
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

    } catch (PDOException $e) {
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->fetchAll();
  }

  function getSensorById($id) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT dev_id, loc.loc_name
                FROM tb_devices
                INNER JOIN tb_locations loc ON fk_dev_loc = loc.pk_loc
                WHERE pk_dev = :id";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }


    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->fetch();
  }

  function addLocation($loc_name) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "INSERT INTO tb_locations (loc_name)
                VALUES (:loc_name);";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':loc_name', $loc_name);

        //exécution de la requête
        $stmt->execute();
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    return $dbh->lastInsertId();
  }

  function addSensor($device_id, $fk_loc) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "INSERT INTO tb_devices (dev_id, fk_dev_loc)
                VALUES (:dev_id, :fk_loc);";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':dev_id', $device_id);
        $stmt->bindParam(':fk_loc', $fk_loc, PDO::PARAM_INT);

        //exécution de la requête
        $stmt->execute();
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    return $dbh->lastInsertId();
  }

  function checkLocation($location) {
    if (!doesLocationExists($location))
        return addLocation($location);
    else
        return getLocationIdByName($location);
  }

  function checkDevice($device) {
    if (!doesDeviceExists($device)){
        $loc = "B1-04";
        $loc_id = checkLocation($loc);

        return addSensor($device, $loc_id);
    } else
        return getDeviceIdBySigfoxId($device);
  }

  function addValue($humidity, $temperature, $date, $seqNumber, $dev_id) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "INSERT INTO tb_results (res_humidity, res_temperature, res_date, res_seq, fk_res_dev)
                VALUES (:humidity, :temperature, :date, :seqNumber, :device);";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':humidity', $humidity);
        $stmt->bindParam(':temperature', $temperature);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':seqNumber', $seqNumber, PDO::PARAM_INT);
        $stmt->bindParam(':device', $dev_id, PDO::PARAM_INT);

        //exécution de la requête
        $stmt->execute();
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    return $dbh->lastInsertId();
  }

  function getAllValues() {
    // récupération de tous les enregistrements
    try {
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT res_humidity, res_temperature, res_date, res_seq, dev.dev_id, loc.loc_name
                FROM tb_results res 
                INNER JOIN tb_devices dev ON res.fk_res_dev = dev.pk_dev 
                INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc;";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //exécution de la requête
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

    } catch (PDOException $e) {
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->fetchAll();
  }

  function getValueById($id) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT res_humidity, res_temperature, res_date, res_seq, dev.dev_id, loc.loc_name
                FROM tb_results res 
                INNER JOIN tb_devices dev ON res.fk_res_dev = dev.pk_dev 
                INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
                WHERE pk_res = :id";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->fetch();
  }


  function doesLocationExists($loc_name) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT pk_loc
                FROM tb_locations
                WHERE loc_name = :name";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':name', $loc_name);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->rowCount() > 0;
  }

  function getLocationIdByName($loc_name) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT pk_loc
                FROM tb_locations
                WHERE loc_name = :name";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':name', $loc_name);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return (int)$stmt->fetch()['pk_loc'];
  }

  function doesDeviceExists($dev_id) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT pk_dev
                FROM tb_devices
                WHERE dev_id = :id";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':id', $dev_id);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return $stmt->rowCount() > 0;
  }

  function getDeviceIdBySigfoxId($dev_id) {
    // récupération de tous les enregistrements
    try{
        // insertion des données dans la base de données
        $dbh = conn_db();

        // modèle de requête
        $sql = "SELECT pk_dev
                FROM tb_devices
                WHERE dev_id = :id";

        //préparation de la requête sur le serveur
        $stmt = $dbh->prepare($sql);

        //association du marqueur à une variable (E/S)
        $stmt->bindParam(':id', $dev_id);

        //exécution de la requête
        $stmt->execute();

        // définir le mode de présentation des données
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e){
        echo $e->getMessage();
        die();
    }

    // retourne un tableau d'enregistrement ou le $stmt
    return (int)$stmt->fetch()['pk_dev'];
  }