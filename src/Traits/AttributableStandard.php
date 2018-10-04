<?php
namespace Awadac\DataBundle\Interfaces;

/**
 * Implemente un comportement pour l'interface Attributable.
 *
 * @author zuri
 *
 */
trait AttributableStandard
{

	protected $attributes = [];

	public function getAttribute($attributeType)
	{
		return $this->attributes[$attributeType];
	}

	public function setAttribute($attributeType, $value): self
	{
		if (!\array_key_exists($attributeType, $this->attributes))
			throw new \Exception("Invalid attribute $attributeType");

		$this->attributes[$attributeType] = $value;
		return $this;
	}

	/**
	 * Permet d'affecter plusieurs attributs en une fois.
	 *
	 * Pour cela il faut que les attributs soient des valeurs scalaires.
	 *
	 * @param array $attributes
	 * @return self
	 */
	public function setAttributes(array $attributes): self
	{
		foreach ($attributes as $k => $v)
			$this->setAttribute($k, $v);

		return $this;
	}
}