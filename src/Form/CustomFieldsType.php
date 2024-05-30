<?php

// src/Form/CustomFieldsType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomFieldsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $builder->add('integer_field_' . $i, TextType::class, ['label' => 'Целочисленное поле ' . $i, 'required' => false]);
            $builder->add('string_field_' . $i, TextType::class, ['label' => 'Строковое поле ' . $i, 'required' => false]);
            $builder->add('text_field_' . $i, TextType::class, ['label' => 'Многострочный текст ' . $i, 'required' => false]);
            $builder->add('boolean_field_' . $i, ChoiceType::class, [
                'label' => 'Логическое поле ' . $i,
                'required' => false,
                'choices' => [
                    'Да' => true,
                    'Нет' => false,
                ],
            ]);
            $builder->add('date_field_' . $i, TextType::class, ['label' => 'Поле даты ' . $i, 'required' => false]);
        }
    }
}
