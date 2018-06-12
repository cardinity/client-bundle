<?php

namespace Cardinity\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Luhn;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('holder')
            ->add('pan', null, [
                'constraints' => new Luhn(),
            ])
            ->add('exp_year', ChoiceType::class, [
                'choices' => $this->years()
            ])
            ->add('exp_month', ChoiceType::class, [
                'choices' => $this->months()
            ])
            ->add('cvc')
            ->add('save', SubmitType::class);
    }

    public function getName()
    {
        return 'credit_card';
    }

    private function years()
    {
        $return = [];
        for ($i = date('Y'); $i <= date('Y') + 7; $i++) {
            $return[$i] = $i;
        }
        return $return;
    }

    private function months()
    {
        $return = [];
        for ($i = 1; $i <= 12; $i++) {
            $return[$i] = $i;
        }
        return $return;
    }
}
