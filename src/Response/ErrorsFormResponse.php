<?php

namespace App\Response;

use Symfony\Component\Form\FormInterface;

/**
 * Aqui é uma classe para validar erros do formulario
 */
class ErrorsFormResponse
{
    public static function getFirstFormError(FormInterface $form): ?string
    {
        // Verificar se o formulário tem erros
        if (!$form->isSubmitted() || $form->isValid()) {
            return null;
        }

        // Obter o primeiro erro do formulário
        foreach ($form->getErrors(true, false) as $error) {
            if(method_exists($error, '__toString')){
                return $error->__toString();
            }else{
                return $error->getMessage();
            }
            
        }

        return null;
    }
}