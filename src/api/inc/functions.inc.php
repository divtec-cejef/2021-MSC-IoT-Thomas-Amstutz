<?php

  /**
   * Check if a key is valid
   * @param $key Key to check
   * @return true if the key is valid of false if it's not
   */
  function isValidKey($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file);
    foreach ($json as $item) {
      if ($item == $key)
        return true;
    }

    return false;
  }

  /**
   * Convert an epoch time to a DateTime
   * @param $time epoch time to convert
   * @return the converted value
   */
  function convertEpoch($time) {
    $formatedTime = new DateTime("@$time");

    return $formatedTime->format('Y-m-d H:i:s');
  }

  /**
   * Connect to the database
   * @return the connection to the database 
   */
  function conn_db() {
    $dsn = 'mysql:host=nh489.myd.infomaniak.com;dbname=' . DB_NAME . ';charset=UTF8';
    try {
      $dbh = new PDO($dsn, DB_USER, DB_PWD);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $dbh;
    } catch(PDOException $e) {
      print "error ! :" . $e->getMessage() . "<br>";
      die();
    }
  }

  /**
   * Get all the sensors from the database
   * @return an array of sensors
   */
  function getAllSensors() {
    try {
      $dbh = conn_db();

      $sql = "SELECT dev_id, loc.loc_name
              FROM tb_devices
              INNER JOIN tb_locations loc ON fk_dev_loc = loc.pk_loc;";

      $stmt = $dbh->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetchAll();
  }

  /**
   * Get a specific sensor from the database
   * @param $id The id of the sensor
   * @return the wanted element
   */
  function getSensorById($id) {
    try {
      $dbh = conn_db();

      $sql = "SELECT dev_id, loc.loc_name
              FROM tb_devices
              INNER JOIN tb_locations loc ON fk_dev_loc = loc.pk_loc
              WHERE pk_dev = :id";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetch();
  }

  /**
   * Add a new location to the database
   * @param $loc_name The name of the new location
   * @return the primary key of this new location
   */
  function addLocation($loc_name) {
    try {
      $dbh = conn_db();

      $sql = "INSERT INTO tb_locations (loc_name)
              VALUES (:loc_name);";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':loc_name', $loc_name);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $dbh->lastInsertId();
  }

  /**
   * Add a new sensor to the database
   * @param $device_id  The name of the new sensor
   * @param $fk_loc     The index of the location of this sensor
   * @return the primary key of this new sensor
   */
  function addSensor($device_id, $fk_loc) {
    try {
      $dbh = conn_db();

      $sql = "INSERT INTO tb_devices (dev_id, fk_dev_loc)
              VALUES (:dev_id, :fk_loc);";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':dev_id', $device_id);
      $stmt->bindParam(':fk_loc', $fk_loc, PDO::PARAM_INT);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $dbh->lastInsertId();
  }

  /**
   * Check for a location
   *  * if it exists get the index
   *  * if it doesn't create a new location and get it's index
   * @param $location The location we want to check
   * @return the index of the location
   */
  function checkLocation($location) {
    if (!doesLocationExists($location))
      return addLocation($location);
    else
      return getLocationIdByName($location);
  }

  /**
   * Check for a sensor
   *  * if it exists get the index
   *  * if it doesn't create a new sensor and get it's index
   * @param $device The sensor we want to check
   * @return the index of the sensor
   */
  function checkDevice($device) {
    if (!doesDeviceExists($device)){
      $loc = "B1-04";
      $loc_id = checkLocation($loc);

      return addSensor($device, $loc_id);
    } else
      return getDeviceIdBySigfoxId($device);
  }

  /**
   * Add a new measerument to the database
   * @param $humidity     The received humidity
   * @param $temperature  The received temperature
   * @param $date         The DateTime of the measurement
   * @param $seqNumber    The number of this sequence
   * @param $dev_id       The device who took the measurement
   * @return the index of the created mesurement
   */
  function addValue($humidity, $temperature, $date, $seqNumber, $dev_id) {
    try {
      $dbh = conn_db();

      $sql = "INSERT INTO tb_results (res_humidity, res_temperature, res_date, res_seq, fk_res_dev)
              VALUES (:humidity, :temperature, :date, :seqNumber, :device);";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':humidity', $humidity);
      $stmt->bindParam(':temperature', $temperature);
      $stmt->bindParam(':date', $date);
      $stmt->bindParam(':seqNumber', $seqNumber, PDO::PARAM_INT);
      $stmt->bindParam(':device', $dev_id, PDO::PARAM_INT);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $dbh->lastInsertId();
  }

  /**
   * Get all the measurement from the database
   * @return an array of measurement
   */
  function getAllValues() {
    try {
      $dbh = conn_db();

      $sql = "SELECT res_humidity, res_temperature, res_date, res_seq, dev.dev_id, loc.loc_name
              FROM tb_results res 
              INNER JOIN tb_devices dev ON res.fk_res_dev = dev.pk_dev 
              INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
              ORDER BY res_date DESC;";

      $stmt = $dbh->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetchAll();
  }

  /**
   * Get a specific measurement from the database
   * @param $id The id of the measurment
   * @return the wanted element
   */
  function getValueById($id) {
    try {
      $dbh = conn_db();

      $sql = "SELECT res_humidity, res_temperature, res_date, res_seq, dev.dev_id, loc.loc_name
              FROM tb_results res 
              INNER JOIN tb_devices dev ON res.fk_res_dev = dev.pk_dev 
              INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
              WHERE pk_res = :id";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetch();
  }

  /**
   * Check if a location exists in the database
   * @param $loc_name The name of the wanted location
   * @return a boolean which indicates if the location exists
   */
  function doesLocationExists($loc_name) {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_loc
              FROM tb_locations
              WHERE loc_name = :name";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':name', $loc_name);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->rowCount() > 0;
  }

  /**
   * Finds a location in the database from it's name
   * @param $loc_name The name of the wanted location
   * @return the id of the found location
   */
  function getLocationIdByName($loc_name) {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_loc
              FROM tb_locations
              WHERE loc_name = :name";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':name', $loc_name);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return (int)$stmt->fetch()['pk_loc'];
  }

  /**
   * Check if a sensor exists in the database
   * @param $dev_id The name of the wanted sensor
   * @return a boolean which indicates if the sensor exists
   */
  function doesDeviceExists($dev_id) {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_dev
              FROM tb_devices
              WHERE dev_id = :id";

      $stmt = $dbh->prepare($sql);        
      $stmt->bindParam(':id', $dev_id);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->rowCount() > 0;
  }

  /**
   * Finds a sensor in the database from it's Sigfox id
   * @param $dev_id The name of the wanted sensor
   * @return the id of the found sensor
   */
  function getDeviceIdBySigfoxId($dev_id) {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_dev
              FROM tb_devices
              WHERE dev_id = :id";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':id', $dev_id);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return (int)$stmt->fetch()['pk_dev'];
  }

  function getLocationIdFromName($location) {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_loc
              FROM tb_locations
              WHERE loc_name = :location";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':location', $location);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return (int)$stmt->fetch()['pk_loc'];
  }

  function getValuesByLocation($locationId) {
    try {
      $dbh = conn_db();

      $sql = "SELECT res_humidity, res_temperature, res_date, res_seq, dev.dev_id, loc.loc_name
              FROM tb_results res 
              INNER JOIN tb_devices dev ON res.fk_res_dev = dev.pk_dev 
              INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
              WHERE loc.pk_loc = :id
              ORDER BY res_date DESC;";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':id', $locationId, PDO::PARAM_INT);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetchAll();
  }

  function getLocationByName($location) {
    $locationId = getLocationIdByName($location);
    return getValuesByLocation($locationId);
  }



  function getAvgValues() {
    $pkList = getAllLocationsId();

    $allMaxDateRowPk = [];
 
    foreach ($pkList as $pk) {
        $allMaxDateRowPk = array_merge($allMaxDateRowPk, getMaxDateFromLocation($pk['pk_loc']));    
    }
 
    $allLastResults = [];
    
    foreach($allMaxDateRowPk as $pk){
        // $allLastResults = array_merge($allLastResults, getValueById($pk['pk_res']));
        $allLastResults += getValueById($pk['pk_res']);
        // var_dump($allLastResults);
    }
 
    return $allLastResults;
  }

  // function getResultByPk($pk) {
  //   try {
  //     $dbh = conn_db();

  //     $sql = "SELECT *
  //             FROM tb_results";

  //     $stmt = $dbh->prepare($sql);
  //     $stmt->setFetchMode(PDO::FETCH_ASSOC);

  //     $stmt->execute();
  //   } catch(PDOException $e) {
  //     echo $e->getMessage();
  //     die();
  //   }

  //   return $stmt->fetchAll();
  // }

  function getMaxDateFromLocation($pk_loc) {
    try {
      $dbh = conn_db();

      $sql = "SELECT * FROM tb_results
              WHERE tb_results.res_date = (
                  SELECT MAX(tb_results.res_date)
                  FROM tb_results
                  WHERE fk_res_dev = (
                      SELECT dev.pk_dev
                      FROM tb_devices dev
                      INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
                      WHERE loc.pk_loc = :fk
                  )
              ) AND tb_results.fk_res_dev = (
                  SELECT fk_res_dev
                  FROM tb_results
                  WHERE fk_res_dev = (
                      SELECT dev.pk_dev
                      FROM tb_devices dev
                      INNER JOIN tb_locations loc ON dev.fk_dev_loc = loc.pk_loc
                      WHERE loc.pk_loc = :fk
                  )
                  LIMIT 1
              )";

      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':fk', $pk_loc, PDO::PARAM_INT);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch (PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetchAll();
  }

  function getAllLocationsId() {
    try {
      $dbh = conn_db();

      $sql = "SELECT pk_loc
              FROM tb_locations";

      $stmt = $dbh->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute();
    } catch(PDOException $e) {
      echo $e->getMessage();
      die();
    }

    return $stmt->fetchAll();
  }