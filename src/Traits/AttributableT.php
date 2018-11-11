<?php
namespace Ulysse\Base\Traits;

use Ulysse\Base\Interfaces\Attributable;

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

	public function setAttribute($attributeType, $value): Attributable
	{
		if (!\array_key_exists($attributeType, $this->attributes))
			throw new \Exception("Invalid attribute $attributeType");

		$this->attributes[$attributeType] = $value;
		return $this;
	}

	public function setAttributes(array $attributes): Attributable
	{
		foreach ($attributes as $k => $v)
			$this->setAttribute($k, $v);

		return $this;
	}
}