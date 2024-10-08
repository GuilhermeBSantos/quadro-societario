<?php

// src/Form/DataTransformer/DateTimeTransformer.php
namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeTransformer implements DataTransformerInterface
{
    public function transform($value) : mixed
    {
        if ($value === null) {
            return '';
        }

        if (!$value instanceof \DateTime) {
            throw new TransformationFailedException('Expected a \DateTime object.');
        }

        return $value->format('Y-m-d');
    }

    public function reverseTransform($value) : mixed
    {
        if ($value === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            throw new TransformationFailedException('Invalid date format.');
        }

        return $date;
    }
}