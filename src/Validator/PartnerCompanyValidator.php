<?php

namespace App\Validator;

use App\Response\JsonApiResponse;
use Symfony\Component\HttpFoundation\Response;

class PartnerCompanyValidator
{
    public static function validFields(array $data) : array
    {
        $message_error = "";

        if($data == null || !array_key_exists("company_id", $data) || !array_key_exists("partner_id", $data) || !$data["company_id"] || !$data["partner_id"]){
            $message_error = "A empresa e o socio é obrigatorio";
        }
        

        if(!array_key_exists("participation", $data) || !$data["participation"] || is_numeric($data["participation"]) == false){
            $message_error = "A Participação é obrigatória";
        }

        if(floatval($data["participation"]) > 100){
            $message_error = "A Sociedade não pode ser maior que 100%";
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

    public static function validFieldsByCpf(array $data) : array
    {
        $message_error = "";
        
        if($data == null || !array_key_exists("company_id", $data) || !array_key_exists("cpf", $data) || !$data["company_id"] || !$data["cpf"]){
            $message_error = "A empresa e o socio é obrigatorio";
        }
        

        if(!array_key_exists("participation", $data) || !$data["participation"] || is_numeric($data["participation"]) == false){
            $message_error = "A Participação é obrigatória";
        }

        if(floatval($data["participation"]) > 100){
            $message_error = "A Sociedade não pode ser maior que 100%";
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

    public static function validFieldsCNPJ(array $data) : array
    {
        $message_error = "";

        if($data == null || !array_key_exists("cnpj", $data) || !array_key_exists("partner_id", $data) || !$data["cnpj"] || !$data["partner_id"]){
            $message_error = "A empresa e o socio é obrigatorio";
        }
        

        if(!array_key_exists("participation", $data) || !$data["participation"] || is_numeric($data["participation"]) == false){
            $message_error = "A Participação é obrigatória";
        }

        if(floatval($data["participation"]) > 100){
            $message_error = "A Sociedade não pode ser maior que 100%";
        }

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