<?php

require_once 'router.php';
require_once "./inc/util.inc.php";

// Get the URL to the working directory
$sub_dir = dirname($_SERVER['PHP_SELF']);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

/**** VALUES  ****/
route('get', $sub_dir . '/values', function ($matches, $rxd) {
    $data = getAllValues();
    
    http_response_code(200);
    echo json_encode($data);
    exit();
});

route('get', $sub_dir . '/values/average', function ($matches, $rxd) {
    $data = getAvgValues();
    
    http_response_code(200);
    echo json_encode($data);
    exit();
});

route('get', $sub_dir . '/values/([0-9]+)', function ($matches, $rxd) {
    $id = $matches[1][0];
    $data = getValueById($id);
    
    if (empty($data)) {
        http_response_code(404);
        $data = "{}";
    } else {
        http_response_code(200);
    }

    echo json_encode($data);
    exit();
});

route('get', $sub_dir . '/locations/([0-9]+)/values', function ($matches, $rxd) {
    $id = $matches[1][0];
    $data = getValuesByLocation($id);
    
    if (empty($data)) {
        http_response_code(404);
        $data = "{}";
    } else {
        http_response_code(200);
    }

    echo json_encode($data);
    exit();
});

route('get', $sub_dir . '/locations/([A-Z-a-z-0-9]+)/values', function ($matches, $rxd) {
    $loc_name = $matches[1][0];
    $data = getLocationByName($loc_name);
    
    if (empty($data)) {
        http_response_code(404);
        $data = "{}";
    } else {
        http_response_code(200);
    }

    echo json_encode($data);
    exit();
});

route('post', $sub_dir . '/values', function ($matches, $rxd) {
    $json = file_get_contents('php://input');
    $postData = json_decode($json, true);

    $headers = apache_request_headers();

    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && canAdd($headers['X-Api-Key'])) {
        $deviceID = checkDevice($postData['device']);
        $data = addValue((float)$postData['humidity'], (float)$postData['temperature'], convertEpoch($postData['date'], true), $postData['seqNumber'], $deviceID);
        
        if (empty($data)) {
            http_response_code(400);
            $data = "{}";
        } else {
            http_response_code(201);
        }
    } else {
        $data = [
            "error" => "Invalid key"
        ];
        
        http_response_code(400);
    }
    
    echo json_encode($data);
    exit();
});

/**** SENSORS  ****/
route('get', $sub_dir . '/sensors', function ($matches, $rxd) {
    $data = getAllSensors();
    
    http_response_code(200);
    echo json_encode($data);
    exit();
});

route('get', $sub_dir . '/sensors/([0-9]+)', function ($matches, $rxd) {
    $id = $matches[1][0];
    $data = getSensorById($id);
    
    if (empty($data)) {
        http_response_code(404);
        $data = "{}";
    } else {
        http_response_code(200);
    }

    echo json_encode($data);
    exit();
});

route('post', $sub_dir . '/sensors', function ($matches, $rxd) {
    $json = file_get_contents('php://input');
    $postData = json_decode($json, true);
    
    $headers = apache_request_headers();

    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && canAdd($headers['X-Api-Key'])) {
        $locationID = checkLocation($postData['location']);
        $data = addSensor($postData['device'], $locationID);
        
        if (empty($data)) {
            http_response_code(400);
            $data = "{}";
        } else {
            http_response_code(201);
        }
    } else {
        $data = [
            "error" => "Invalid key"
        ];

        http_response_code(400);
    }
    
    echo json_encode($data);
    exit();
});

// If the URL isn't correct
$data = [
    "error"     => "Unknown route"
];

http_response_code(400);
echo json_encode($data, JSON_FORCE_OBJECT);
