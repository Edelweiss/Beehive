<?php

namespace App\Form;

use App\Entity\IndexEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndexEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('type', ChoiceType::class, ['label' => 'Typ', 'choices' => self::getIndexTypes()])
            ->add('topic', ChoiceType::class, ['label' => 'Thema', 'choices' => self::getIndexTopics()])
            ->add('tab', ChoiceType::class, ['label' => 'Gruppe', 'choices' => self::getIndexTabs()])
            ->add('lemma', TextType::class, ['label' => 'Lemma'])
            ->add('papyNew', CheckboxType::class, ['label' => 'Papy neu'])
            ->add('greekNew', CheckboxType::class, ['label' => 'Griechisch neu'])
            ->add('sort', TextType::class, ['label' => 'Sortierung'])
            ->add('phrase', TextareaType::class, ['label' => 'Beschreibung'])
        ;
    }

    public static function getIndexTypes(){
        return ['Neues Wort' => 'Neues Wort', 'Ghostword' => 'Ghostword'];
    }

    public static function getIndexTopics(){
        return ['Allgemeiner Wortindex' => 'Allgemeiner Wortindex', 'Personennamen' => 'Personennamen', 'Könige, Kaiser, Konsuln' => 'Könige, Kaiser, Konsuln', 'Geographisches und Topographisches' => 'Geographisches und Topographisches', 'Monate und Tage' => 'Monate und Tage', 'Religion' => 'Religion', 'Zivil- und Militärverwaltung' => 'Zivil- und Militärverwaltung', 'Steuern' => 'Steuern', 'Berufsbezeichnungen' => 'Berufsbezeichnungen', 'Fundorte' => 'Fundorte', 'Sachen' => 'Sachen'];
    }

    public static function getIndexTabs(){
        return ['Α' => 'Α', 'Β' => 'Β', 'Γ' => 'Γ', 'Δ' => 'Δ', 'Ε' => 'Ε', 'Ζ' => 'Ζ', 'Η' => 'Η', 'Θ' => 'Θ', 'Ι' => 'Ι', 'Κ' => 'Κ', 'Λ' => 'Λ', 'Μ' => 'Μ', 'Ν' => 'Ν', 'Ξ' => 'Ξ', 'Ο' => 'Ο', 'Π' => 'Π', 'Ρ' => 'Ρ', 'Σ' => 'Σ', 'Τ' => 'Τ', 'Υ' => 'Υ', 'Φ' => 'Φ', 'Χ' => 'Χ', 'Ψ' => 'Ψ', 'Ω' => 'Ω', 'Lateinisch' => 'Lateinisch', 'Lateinisch (und Demotisch)' => 'Lateinisch (und Demotisch)', 'Koptisch' => 'Koptisch', 'Schadhaft' => 'Schadhaft', 'Sonstiges' => 'Sonstiges', '5' => '5', '6' => '6', 'ϙ' => 'ϙ'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IndexEntry::class,
        ]);
    }
}