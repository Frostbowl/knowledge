<?php
namespace App\Form;

use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CursusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'=>'Nom du cursus',
                'required'=>true,
                'attr'=>['placeholder'=>'Entrez le nom du cursus',],
            ])
            ->add('prix', NumberType::class,[
                'label'=>'Prix',
                'attr'=>['placeholder'=>'Entrez le prix du cursus',],
                'required'=>true,
            ])
            ->add('theme', EntityType::class, [
                'class'=> Theme::class,
                'choice_label'=>'name',
                'placeholder'=>'Sélectionner le thème du cursus',
                'required'=>true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults([
            'data_class'=> Cursus::class,
        ]);
    }
}