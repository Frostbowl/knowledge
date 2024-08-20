<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('name', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Entrez votre nom et votre prénom',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped'=>false,
                'attr'=>['autocomplete'=> 'new-password'],
                'constraints'=>[
                    new NotBlank([
                        'message'=> 'Entrez un mot de passe',
                    ]),
                    new Length([
                        'min'=>6,
                        'minMessage'=>'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max'=>4096,
                    ]),
                ],
            ])
            ->add('plainPasswordConfirm', PasswordType::class, [
                'mapped'=>false,
                'attr'=>['autocomplete'=>'new-password'],
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Confirmez votre mot de passe',
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
                $form = $event->getForm();
                $date = $event->getData();
                $plainPassword = $form->get('plainPassword')->getData();
                $plainPasswordConfirm = $form->get('plainPasswordConfirm')->getData();

                if($plainPassword !== $plainPasswordConfirm){
                    $form->get('plainPasswordConfirm')->addError(new FormError('Les mots de passe ne correspondent pas'));
                }
            })
        ;
    }   

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['registration']
        ]);
    }
}
