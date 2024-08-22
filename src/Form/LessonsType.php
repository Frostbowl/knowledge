<?php
namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Lessons;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LessonsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label'=>'Nom de la leçon',
                'required'=>true,
                'attr'=>['placeholder'=>'Entrez le nom de la leçon',],
            ])
            ->add('description', TextareaType::class, [
                'label'=>'Description',
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Entrez ici la leçon',
                    'rows'=> 7,
                ],
            ])
            ->add('videoFile', FileType::class,[
                'label'=> 'Fichier Video',
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Choisissez un fichier video',
                ],
            ])
            ->add('prix', NumberType::class, [
                'label'=>'Prix',
                'attr'=>['placeholder'=>'Entrez le prix de la leçon',],
                'required'=>true,
            ])
            ->add('cursus', EntityType::class, [
                'class'=>Cursus::class,
                'label'=>'Cursus',
                'required'=>true,
                'choice_label'=>'name',
                'placeholder'=>'Sélectionnez un cursus',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>Lessons::class,
        ]);
    }
}
