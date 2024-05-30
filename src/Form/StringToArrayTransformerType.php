<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\DataTransformerInterface;
use App\Form\StringToArrayTransformer;


class StringToArrayTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        // Convert array to a comma-separated string
        return is_array($value) ? implode(',', $value) : '';
    }

    public function reverseTransform($string): mixed
    {
        // Convert comma-separated string to array
        return is_string($string) ? explode(',', $string) : [];
    }
}