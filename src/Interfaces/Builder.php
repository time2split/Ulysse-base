<?php
namespace Awadac\DataBundle\Interfaces;

interface Builder
{

    /**
     * Cible à construire
     * @param mixed $target
     */
    public function setTarget($target);

    
    public function getTarget();

    /**
     * Construit l'élément
     */
    public function build();

    /**
     *
     * @return mixed un nouvel élément construit
     */
//     public function newBuild();

    /**
     * Affecte, construit et retourne $target
     * 
     * @param mixed $target
     */
    public function buildThis($target);
}