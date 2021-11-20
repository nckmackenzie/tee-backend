<?php
require_once '../models/Response.php';
function sendHttpResponse($statusCode,$success,$message = null,$data =null){
    $response = new Response();
    $response->setHttpResponseCode($statusCode);
    $response->setSuccess($success);
    if($message !== null || !empty($message)){
        $response->addMessages($message);
    }
    $response->setData($data);
    $response->send();
}