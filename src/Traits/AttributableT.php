<?php
namespace Ulysse\Base\Traits;

/**
 * Implemente un comportement pour l'interface Attributable.
 *
 * @author zuri
 *
 */
trait AttributableT
{

	/**
	 * The associated array of attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	public function getAttribute($attributeType)
	{
		return $this->attributes[$attributeType];
	}

	public function setAttribute($attributeType, $value): void
	{
		if (!\array_key_exists($attributeType, $this->attributes))
			throw new \Exception("Invalid attribute $attributeType");

		$this->attributes[$attributeType] = $value;
	}

	public function setAttributes(array $attributes): void
	{
		foreach ($attributes as $k => $v)
			$this->setAttribute($k, $v);
	}
}