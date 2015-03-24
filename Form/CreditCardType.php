<?php
namespace Cardinity\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('exp_year', 'choice', [
                'choices' => $this->years()
            ])
            ->add('exp_month', 'choice', [
                'choices' => $this->months()
            ])
            ->add('cvc')
            ->add('save', 'submit')
        ;
    }

    public function getName()
    {
        return 'credit_card';
    }

    private function years()
    {
        $return = [];
        for ($i = date('Y'); $i <= date('Y')+7; $i++) {
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
