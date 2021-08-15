<?php declare(strict_types=1);

namespace App\Form;

use App\Utils\Web3Util;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AutoFarmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('masterchef', TextType::class, [
            'label' => false,
            'required' => true,
            'help' => 'Masterchef contract address',
            'attr' => [
                'placeholder' => 'Masterchef address (eg 0x09Bfd...)',
            ],
            'constraints' => [
                new NotBlank(),
                new Callback([$this, 'validateAddress']),
            ]
        ]);

        $builder->add('address', TextType::class, [
            'label' => false,
            'required' => false,
            'help' => 'Wallet address',
            'attr' => [
                'placeholder' => '(Optional) Your wallet address (eg 0x0A847...)',
            ],
            'constraints' => [
                new Callback([$this, 'validateAddress']),
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public static function validateAddress($data, ExecutionContextInterface $context): void
    {
        if (empty($data)) {
            return;
        }

        if (!Web3Util::isAddress($data)) {
            $context->addViolation('Invalid address');
        }
    }
}