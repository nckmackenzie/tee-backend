<?php
function ValidateJSON()
{
    if($_SERVER['HTTP_CONTENT_TYPE'] !== 'application/json'){
        sendHttpResponse(400,false,'Content type header not set to json');
        exit;
    }
}