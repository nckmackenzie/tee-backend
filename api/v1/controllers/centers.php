<?php
require_once '../models/Centers.php';
require_once '../lib/sendResponse.php';
require_once '../lib/sanitizeString.php';

$centers = new Centers();

//get by id
if(array_key_exists('id',$_GET)){ //get,patch and delet methods ie /centers/1
    $id = $_GET['id'];
    //validation
    if(empty($id) || !is_numeric($id)){
        sendHttpResponse(400,false,'Invalid ID provided');
        exit;
    }
    //check http method
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            $centersReturned = $centers->getCenter($id);
            if ($centersReturned) {
            
               $center = array();
               $center['id']  = $centersReturned->ID;
               $center['centerName']  = $centersReturned->CenterName;
               $center['contact']  = $centersReturned->Contact;
               $center['email']  = $centersReturned->Email;
               $center['isHead']  = $centersReturned->IsHead == 1 ? true : false;
               $centerArr[] = $center; 

               $returnData = array();
               $returnData['rows_returned'] = 1;
               $returnData['centers'] = $centerArr;
               sendHttpResponse(200,true,null,$returnData);
               exit;
            }else{
                sendHttpResponse(404,false,'Center not found');
                exit;
            }

        } catch (PDOException $e) {
            sendHttpResponse(500,false,$e->getMessage());
            exit;
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){
        
    } 
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        try {
            $deletedCenter = $centers->deleteCenter($id);

            if ($deletedCenter === 0) {
                sendHttpResponse(404,false,'Center not found');
                exit;
            }else{
                sendHttpResponse(200,true,'Center deleted');
                exit;
            }

        } catch (PDOException $e) {
            sendHttpResponse(500,false);
        }
    }else{
        sendHttpResponse(405,false,'Invalid request method');
        exit;
    }
}elseif(empty($_GET)){ //get and post methods ie /centers
    //check req method
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        try {
            
            $allCenters = $centers->getAllCenters();
            if ($allCenters) {
                $centerArr = array();
               
                while($row = $allCenters->fetch(PDO::FETCH_ASSOC)){
                    $center['id']  = $row['ID'];
                    $center['centerName']  = $row['CenterName'];
                    $center['contact']  = $row['Contact'];
                    $center['email']  = $row['Email'];
                    $centerArr[] = $center; 
                }
                // print_r(json_encode($centerArr));
                $returnData = array();
                $returnData['rows_returned'] = count($centerArr);
                $returnData['centers'] = $centerArr;
                sendHttpResponse(200,true,null,$returnData);
            }else{
                sendHttpResponse(404,false,'Centers not found');
                exit;
            }

        } catch (PDOException $e) {
            sendHttpResponse(500,false,$e->getMessage());
            exit;
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = array();
        try {
            //check if data is json
            if($_SERVER['HTTP_CONTENT_TYPE'] !== 'application/json'){
                sendHttpResponse(400,false,'Content type header not set to json');
                exit;
            }
            //get entered data in json format
            $rawPostData = file_get_contents('php://input');
            $jsonData = json_decode($rawPostData);
            if(!$jsonData){
                sendHttpResponse(400,false,'Request body not valid json');
                exit;
            }
            //validate data
            if(empty($jsonData->centerName) || !isset($jsonData->centerName)){
                array_push($errors,'Center name is required');
            }

            if(isset($jsonData->email) && !filter_var($jsonData->email,FILTER_VALIDATE_EMAIL)){
                array_push($errors,'Invalid email address provided');
            }

            if(isset($jsonData->isHead) && !is_bool($jsonData->isHead)){
                array_push($errors,'IsHead can either be true or false');
            }

            if(count($errors) !==0){
                sendHttpResponse(400,false,$errors);
                exit;
            }

            $convetedData = array(
                'id' => '',
                'centerName' => filterThis(strtolower($jsonData->centerName)),
                'contact' => isset($jsonData->contact) ? filterThis($jsonData->contact) : null,
                'email' => isset($jsonData->email) ? filterThis($jsonData->email) : null,
                'isHead' => isset($jsonData->isHead) ? $jsonData->isHead : false,
            );

            $response = $centers->createCenter($convetedData);
            if (!$response) {
                sendHttpResponse(500,false,'Center not created. Try again');
                exit;
            }

            $convetedData['id'] = $response;
            $convetedDataArr[] = $convetedData;

            $returnData = array();
            $returnData['rows_returned'] = 1;
            $returnData['center'] = $convetedDataArr;
            sendHttpResponse(201,true,'Center created successfully',$returnData);
            exit;

        } catch (PDOException $e) {
            sendHttpResponse(500,false,$e->getMessage());
            exit;
        }
    }else{
        sendHttpResponse(405,false,'Invalid request method');
        exit;
    }

}else{
    sendHttpResponse(404,false,'No endpoint found');
}