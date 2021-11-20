<?php

class Response
{
    private $success;
    private $httpStatusCode;
    private $messages = array();
    private $data;
    private $responseData = array();

    public function setSuccess($success)
    {
        $this->success = $success;
    }

    public function setHttpResponseCode($code)
    {
        $this->httpStatusCode = $code;
    }

    public function addMessages($message)
    {
        $this->messages[] = $message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function send()
    {
        header('Content-type: application/json;charset=utf-8');
        //validation
        if(($this->success !== true && $this->success !== false) || 
            !is_numeric($this->httpStatusCode)){

            http_response_code(500);
            $this->responseData['success'] = false;
            $this->addMessages('Response creation error due to invalid entries');
            $this->responseData['messages'] = $this->messages;
            // exit;
        }else{
            http_response_code($this->httpStatusCode);
            $this->responseData['success'] = $this->success;
            $this->responseData['messages'] = $this->messages;
            $this->responseData['data'] = $this->data;
        }   
        echo json_encode($this->responseData);
    }
}