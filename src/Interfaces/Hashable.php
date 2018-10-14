<?php
namespace Awadac\DataBundle\Interfaces;

/**
 * Interface pour les objets hashables.
 *
 * @author orodriguez
 *
 */
interface Hashable
{

    public function hashCode(): string;
}