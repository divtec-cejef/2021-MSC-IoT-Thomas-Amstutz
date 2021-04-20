<?php

  /********************************************** API KEYS **********************************************/
  /**
   * Check if a key is valid
   * @param $key Key to check
   * @return true if the key is valid of false if it's not
   */
  function isValidKey($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return true;
    }

    return false;
  }

  /**
   * Check if a key can add other keys
   * @param $key Key to check
   * @return true if the key can add other keys
   */
  function isMasterKey($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return $item['is_master'];
    }
  }

  /**
   * Check if a key can read a value
   * @param $key Key to check
   * @return true if the key can read
   */
  function canRead($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return $item['can_read'];
    }
  }

  /**
   * Check if a key can add a value
   * @param $key Key to check
   * @return true if the key can add
   */
  function canAdd($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return $item['can_add'];
    }
  }

  /**
   * Check if a key can update a value
   * @param $key Key to check
   * @return true if the key can update
   */
  function canUpdate($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return $item['can_update'];
    }
  }

  /**
   * Check if a key can delete a value
   * @param $key Key to check
   * @return true if the key can delete
   */
  function canDelete($key) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    foreach ($json as $item) {
      if ($item["key"] == $key)
        return $item['can_update'];
    }
  }

  /**
   * @return all the api keys
   */
  function getAllKeys() {
    $file = file_get_contents(API_KEYS);
    return $file;
  }

  function addKey($key, $masterKey) {
    $file = file_get_contents(API_KEYS);
    $json = json_decode($file, true);
    array_push($json, $key);
    $newJson = json_encode($json, JSON_PRETTY_PRINT);
    file_put_contents(API_KEYS, $newJson);
    return $newJson;
  }

  /**
   * Convert an epoch time to a DateTime
   * @param $time epoch time to convert
   * @param $addHour add one or multiple hours to whe final time (default to false)
   * @param $hoursToAdd number of hours to add (default to 1)
   * @return the converted value
   */
  function convertEpoch($time, $addHour = false, $hoursToAdd = 1) {
    $formatedTime = new DateTime("@$time");

    // Add one hour to correct time timezone problems
    if ($addHour)
      date_add($formatedTime, date_interval_create_from_date_string("$hoursToAdd hour"));

    return $formatedTime->format('Y-m-d H:i:s');
  }