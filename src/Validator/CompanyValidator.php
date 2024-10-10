<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Classe para validar a Empresa
 */
class CompanyValidator
{
    public static function validFields(array $data) : array
    {
        $message_error = "";
        
        if($data == null || !array_key_exists("opening_date", $data) || !$data["opening_date"]){
            $message_error = "A data de abertura obrigatória";
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