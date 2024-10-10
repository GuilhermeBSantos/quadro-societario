<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Classe para validar usuários
 */
class UsersValidator
{
    public static function validFields(array $data) : array
    {
        $message_error = "";
        
        if($data == null || !array_key_exists("email", $data) || !array_key_exists("password", $data) || !$data["email"] || !$data["password"]){
            $message_error ="Existem campos faltando, por favor verifique o email e a senha";
        }
        
        $data_request = [
            "status" => "",
            "message" => "",
            "code" => 200
        ];

        if($message_error == ""){
            $data_request["status"] = 'success';
            $data_request["code"] = Response::HTTP_OK;
            $data_request["message"] = "Requisição feita com Sucesso";
        }
        else{
            $data_request["status"] = 'error';
            $data_request["code"] = Response::HTTP_BAD_REQUEST;
            $data_request["message"] = $message_error;
        }
        
        return $data_request;
    }

}