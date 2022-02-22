<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class FarmSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('query', TextType::class, [
            'label' => 'Text Filter',
            'required' => false,
            'attr' => [
                'placeholder' => 'Name, Token, LP (Addr.)',
            ],
        ]);

        $builder->add('page', HiddenType::class, [
            'constraints' => [
                new Range(['min' => 0, 'max' => 100000]),
            ],
            'label' => false,
            'required' => false,
        ]);

        $sorts = [
            'tvl_asc',
            'tvl_desc',
            'name_asc',
            'name_desc',
            'apy_asc',
            'apy_desc',
            'provider_asc',
            'provider_desc',
        ];

        $builder->add('sort', ChoiceType::class, [
            'attr' => [
                'class' => 'd-none',
            ],
            'choices' => array_combine($sorts, $sorts),
            'label' => false,
            'required' => false,
        ]);

        $chains = $options['chains'];

        usort($chains, static fn($a, $b) => $a['title'] <=> $b['title']);

        $builder->add('chains', ChoiceType::class, [
            'label' => sprintf('Chains (%s)', count($chains)),
            'required' => false,
            'choices' => array_map(static fn($provider) => new \ArrayObject($provider), $chains),
            'multiple' => true,
            'attr' => [
                'placeholder' => 'Filter Chains',
                'class' => 'selectize-icon',
            ],
            'choice_value' => function (\ArrayAccess $provider) {
                return $provider['id'];
            },
            'choice_label' => function (\ArrayAccess $provider) {
                return $provider['shortTitle'] ?? $provider['title'];
            },
            'choice_attr' => function (\ArrayAccess $provider) {
                return ['data-selectize' => json_encode(['icon' => $provider['icon']])];
            }
        ]);

        $providers = $options['providers'];

        usort($providers, static fn($a, $b) => strtolower($a['label']) <=> strtolower($b['label']));

        $builder->add('providers', ChoiceType::class, [
            'label' => sprintf('Platforms (%s)', count($providers)),
            'required' => false,
            'choices' => array_map(static fn($provider) => new \ArrayObject($provider), $providers),
            'multiple' => true,
            'attr' => [
                'placeholder' => 'Filter Platforms',
                'class' => 'selectize-icon',
            ],
            'choice_value' => function (\ArrayAccess $provider) {
                return $provider['id'];
            },
            'choice_label' => function (\ArrayAccess $provider) {
                return $provider['label'];
            },
            'choice_attr' => function(\ArrayAccess $provider) {
                return ['data-selectize' => json_encode(['icon' => $provider['icon']])];
            }
        ]);

        $tags = [
            'Auto-Compound' => 'auto_compound',
            'Stable' => 'stable',
            'Lend-Borrow' => 'lend_borrow',
            'Leverage' => 'leverage',
            'Interest-Bearing-Token' => 'ib_token',
            'Earns' => 'earns',
            'Bond' => 'bond',
        ];

        $builder->add('tags', ChoiceType::class, [
            'label' => sprintf('Tags (%s)', count($tags)),
            'required' => false,
            'choices' => $tags,
            'multiple' => true,
            'attr' => [
                'placeholder' => 'Filter Tags',
                'class' => 'selectize',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'chains' => [],
            'providers' => [],
        ]);
    }
}