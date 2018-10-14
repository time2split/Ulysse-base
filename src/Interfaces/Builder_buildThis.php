<?php
namespace Awadac\DataBundle\Interfaces;

/**
 * Implémentation par défaut de la méthode buildThis() de l'interface Builder.
 *
 * @author orodriguez
 *
 */
trait Builder_buildThis
{

    public function buildThis($target)
    {
        $this->setTarget($target);
        $this->build();
        return $this->getTarget();
    }
}