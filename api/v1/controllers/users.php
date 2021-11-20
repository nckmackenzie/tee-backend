<?php
require_once '../lib/sanitizeString.php';
require_once '../lib/sendResponse.php';
require_once '../lib/validateJson.php';
require_once '../models/Users.php';

$users = new Users();
$returnData = array();

if (array_key_exists('id',$_GET)) { //users/1 GET PATCH DELETE
    $id = trim($_GET['id']);
    //validate id exists and is numeric
    if (!isset($id) || empty($id) || !is_numeric($id)) {
        sendHttpResponse(400,false,'Invalid ID provided');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $response = $users->getUser($id);
            if (!$response) {
                sendHttpResponse(404,false,'No user found');
                exit;
            }
            $user = array();
            $user['id'] = (int)$response->ID;
            $user['userId'] = $response->UserID;
            $user['userName'] = $response->UserName;
            $user['userTypeId'] = (int)$response->UserTypeId;
            $user['userType'] = $response->UserType;
            $user['active'] = (int)$response->Active === 1 ? true : false;
            $user['centerId'] = (int)$response->CenterId;
            $user['centerName'] = $response->CenterName;
            $userArr[] = $user;

            $returnData['rows_returned'] = 1;
            $returnData['user'] = $userArr;
            sendHttpResponse(200,true,'User found',$returnData);
            
        } catch (Exception $e) {
            sendHttpResponse(500,false,$e->getMessage());
            exit;
        }
        exit;
    }
    // PATCH
    if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $errors =array();
        ValidateJSON(); //validate JSON

        try {
            $rawPostData = file_get_contents('php://input');
            $jsonData = json_decode($rawPostData);
            if(!$jsonData){
                sendHttpResponse(400,false,'Request body not valid json');
                exit;
            }

            $nameChanged = false; $userTypeChanged = false; $activeChanged = false;
            
            if (isset($jsonData->userName)) {
                $nameChanged = true;
            }
            if (isset($jsonData->userType)) {
                $userTypeChanged = true;
            }
            if (isset($jsonData->active)) {
                $activeChanged = true;
            }

            if (!$nameChanged && !$userTypeChanged && !$activeChanged) {
                sendHttpResponse(400,false,'No entries/fields provided for update');
                exit;
            }

            $updateData = array(
                'id' => $id,
                'nameChanged' => $nameChanged,
                'userTypeChanged' => $userTypeChanged,
                'activeChanged' => $activeChanged,
                'userName' => $nameChanged ? filterThis(strtolower($jsonData->userName)) : '',
                'userType' => $userTypeChanged ? $jsonData->userType : '',
                'active' => $activeChanged ? $jsonData->active : '',
            );

            $response = $users->updateUser($updateData);
            // echo json_encode($response);

            if(!$response){
                sendHttpResponse(404,false,'No user updated');
                exit;
            }

            $user = array();
            $user['id'] = $response->ID;
            $user['userId'] = $response->UserID;
            $user['userName'] = $response->UserName;
            $user['userTypeId'] = (int)$response->UserTypeId;
            $user['userType'] = $response->UserType;
            $user['active'] = (int)$response->Active === 1 ? true : false;
            $user['centerId'] = (int)$response->CenterId;
            $user['centerName'] = $response->CenterName;
            $userArr[] = $user;

            $returnData['rows_returned'] = 1;
            $returnData['user'] = $userArr;
            sendHttpResponse(200,true,'User found',$returnData);


        } catch (Exception $e) {
            sendHttpResponse(500,false,$e->getMessage());
        }

        exit;
    }
    //DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        
        exit;
    }
    //IF NEITHER
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || $_SERVER['REQUEST_METHOD'] !== 'PATCH' || $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        sendHttpResponse(405,false,'Invalid request method');
        exit;
    }
    
}elseif (array_key_exists('cid',$_GET)) { // users/center/1  GET ONLY
    
}elseif (empty($_GET)) { // /users    --GET AND POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors =array();
        ValidateJSON(); //validate JSON

        try {
            $rawPostData = file_get_contents('php://input');
            $jsonData = json_decode($rawPostData);
            if(!$jsonData){
                sendHttpResponse(400,false,'Request body not valid json');
                exit;
            }

            //VALIDATE DATA ENTERED
            if (!isset($jsonData->userId) || empty($jsonData->userId)) {
                array_push($errors,'UserID is required');
            }

            if (!isset($jsonData->userName) || empty($jsonData->userName)) {
                array_push($errors,'User name is required');
            }

            if (!isset($jsonData->userType) || empty($jsonData->userType)) {
                array_push($errors,'User type is required');
            }

            if (!isset($jsonData->password) || empty($jsonData->password)) {
                array_push($errors,'Password is required');
            }

            if (!isset($jsonData->confirmPassword) || empty($jsonData->confirmPassword)) {
                array_push($errors,'Confirm password');
            }

            if ((isset($jsonData->password) && isset($jsonData->confirmPassword)) && 
                ($jsonData->password !== $jsonData->confirmPassword)) {
                array_push($errors,'Passwords don\'t match');
            }

            if (!isset($jsonData->center) || empty($jsonData->center)) {
                array_push($errors,'Center is required');
            }

            if(isset($jsonData->userId) && isset($jsonData->center) 
               && $users->checkUserIdExists($jsonData->userId,$jsonData->center,'')){
                array_push($errors,'UserID exists');
            }

            if (count($errors) > 0) {
                sendHttpResponse(400,false,$errors);
                exit;
            }

            $userData = array(
                'id' => '',
                'userId' => filterThis(strtolower($jsonData->userId)),
                'userName' => filterThis(strtolower($jsonData->userName)),
                'userType' => $jsonData->userType,
                'center' => $jsonData->center
            );
            $password = filterThis($jsonData->password);
            $response = $users->createUser($userData,$password);

            if (!$response) {
                sendHttpResponse(500,false,'Center not created. Try again');
                exit;
            }

            $userData['id'] = $response;
            $userArr[] = $userData;
            $returnData['rows_returned'] = 1;
            $returnData['user'] = $userArr;
            sendHttpResponse(201,true,'User created successfully',$returnData);
            exit;

        } catch (Exception $e) {
            sendHttpResponse(500,false,$e->getMessage());
            exit;
        }
        exit;
    }
    //GET ALL USERS
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        exit;
    }
}else { // no route
    sendHttpResponse(404,false,'No endpoint found');
}





