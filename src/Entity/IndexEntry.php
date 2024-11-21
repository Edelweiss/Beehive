<?php
namespace App\Entity;

use App\Repository\IndexEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Compilation;

class IndexEntry
{
    private $id;
    private $type;
    private $topic;
    private $tab;
    private $papyNew;
    private $greekNew;
    private $lemma;
    private $sort;
    private $phrase;
    private $compilations;
    private $corrections;

    public function __construct()
    {
        $this->compilations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function getTab()
    {
        return $this->tab;
    }

    public function setPapyNew($papyNew)
    {
        $this->papyNew = $papyNew;
    }

    public function getPapyNew()
    {
        return $this->papyNew;
    }

    public function setGreekNew($greekNew)
    {
        $this->greekNew = $greekNew;
    }

    public function getGreekNew()
    {
        return $this->greekNew;
    }

    public function setLemma($lemma)
    {
        $this->lemma = $lemma;
    }

    public function getLemma()
    {
        return $this->lemma;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function addCorrection(\App\Entity\Correction $correction)
    {
        $this->corrections[] = $correction;
    }

    public function removeCorrection(\App\Entity\Correction $correction)
    {
        if (!$this->corrections->contains($correction)) {
            return;
        }    
        $this->corrections->removeElement($correction);
        $correction->removeIndexEntry($this);
    }

    public function getCorrections()
    {
        return $this->corrections;
    }

    public function addCcompilation(\App\Entity\Compilation $compilation)
    {
        $this->compilations[] = $compilation;
    }

    public function getCompilations()
    {
        return $this->compilations;
    }
}