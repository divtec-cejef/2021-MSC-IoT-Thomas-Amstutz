<?php

require_once 'router.php';
require_once "./inc/util.inc.php";

// Get the URL to the working directory
$sub_dir = dirname($_SERVER['PHP_SELF']);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

/**** API KEYS ****/
route('get', $sub_dir . '/keys', function ($matches, $rxd) {
    $headers = apache_request_headers();
    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && isMasterKey($headers['X-Api-Key'])) {
        $data = getAllKeys();
        
        if (empty($data)) {
            http_response_code(404);
            $data = "{}";
        } else {
            http_response_code(200);
        }
    } else {
        $data = [
            "error" => "Invalid master key"
        ];
        
        http_response_code(400);
    }
    echo $data;
    exit();
});

route('get', $sub_dir . '/keys/verify/([A-Z-a-z-0-9]+)', function ($matches, $rxd) {
    $keyToCheck = $matches[1][0];
    $headers = apache_request_headers();
    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && isMasterKey($headers['X-Api-Key'])) {
        $data = isValidKey($keyToCheck);
    } else {
        $data = [
            "error" => "Invalid master key"
        ];
        
        http_response_code(400);
    }
    var_dump($data);
    exit();
});

route('post', $sub_dir . '/keys', function ($matches, $rxd) {
    $json = file_get_contents('php://input');
    $postData = json_decode($json, true);

    $headers = apache_request_headers();

    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && isMasterKey($headers['X-Api-Key'])) {
        if (!isValidKey($postData['key'])) {
            $newKey = array(
                "key"=> $postData['key'],
                "is_master"=> false,
                "can_read"=> $postData['can_read'],
                "can_add"=> $postData['can_add'],
                "can_update"=> $postData['can_update'],
                "can_delete"=> $postData['can_delete'],
            );
            $data = addKey($newKey, $headers['X-Api-Key']);
            
            if (empty($data)) {
                http_response_code(400);
                $data = "{}";
            } else {
                http_response_code(201);
            }
        } else {
            $data = [
                "error" => "This key already exists"
            ];
            
            http_response_code(400);
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

route('get', $sub_dir . '/locations/([A-Z-a-z-0-9]+)/values/latest', function ($matches, $rxd) {
    $loc_name = $matches[1][0];
    $data = getLocationByName($loc_name, true);
    
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
        $data = addValue((float)$postData['humidity'], (float)$postData['temperature'], convertEpoch($postData['date'], true, 2), $postData['seqNumber'], $deviceID);
        
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

route('put', $sub_dir . '/values', function ($matches, $rxd) {
    $json = file_get_contents('php://input');
    $postData = json_decode($json, true);

    $headers = apache_request_headers();

    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && canUpdate($headers['X-Api-Key'])) {
        $deviceID = checkDevice($postData['device']);
        $data = updateValue($postData['id'], (float)$postData['humidity'], (float)$postData['temperature'], convertEpoch($postData['date'], true), $postData['seqNumber'], $deviceID);
        
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

route('delete', $sub_dir . '/values/([0-9]+)', function ($matches, $rxd) {
    $id = $matches[1][0];

    $headers = apache_request_headers();

    if (isset($headers['X-Api-Key']) && isValidKey($headers['X-Api-Key']) && canDelete($headers['X-Api-Key'])) {
        $data = delValueById($id);
        
        if (empty($data)) {
            http_response_code(404);
            $data = "{}";
        } else {
            http_response_code(200);
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
