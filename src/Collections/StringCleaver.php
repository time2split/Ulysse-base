<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Collections;

use function Ulysse\Base\Helpers\Arrays\flattenValues;
use Ulysse\Base\Interfaces\Arrays;
use Ulysse\Base\Traits\StringDelimiters;

class StringCleaver extends \ArrayObject implements Arrays
{
	use StringDelimiters;

	protected $text;

	public function __construct(string $text = null, $delimiters = '.')
	{
		$this->setDelimiters($delimiters);
		$this->setText($text);
	}

	public function setText(?string $text)
	{
		$this->text = $text;

		if ($text !== null)
			$this->exchangeArray($this->chop($text));
		else
			$this->exchangeArray([]);
	}

	public function getText(): ?string
	{
		return $this->text;
	}

	public function __toString(): string
	{
		return $this->text;
	}

	public function setParts(?array $parts)
	{
		if ($parts === null)
		{
			$this->exchangeArray([]);
			$this->text = null;
		}
		else
		{
			$parts = \array_map('strval', flattenValues($parts));
			$this->exchangeArray($parts);
			$this->text = \implode($this->getMainDelimiter(), $parts);
		}
	}

	public function offsetSet($index, $newval)
	{
		$me = $this->getArrayCopy();
		$me[$index] = $newval;
		$this->setParts($me);
	}

	public function offsetUnset($index)
	{
		$me = $this->getArrayCopy();

		if (!\array_key_exists($index, $me))
			return;

		unset($me[$index]);
		$this->setParts($me);
	}
}
