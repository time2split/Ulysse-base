<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Traits;

use function Ulysse\Base\Functions\Arrays\flattenValues;
use function Ulysse\Base\Functions\Arrays\makeItArray;

trait StringDelimitersT
{

	protected $delimiters = [];

	public function addStringDelimiter(string ...$delimiters): void
	{
		$this->delimiters = \array_merge($this->delimiters, $delimiters);
	}

	public function addStringDelimiters(array $delimiters): void
	{
		$this->addStringDelimiter(...flattenValues(makeItArray($delimiters)));
	}

	public function cleanStringDelimiters(): void
	{
		$this->delimiters = [];
	}

	public function getStringDelimiters(): array
	{
		return $this->delimiters;
	}

	protected function getMainDelimiter(): ?string
	{
		return $this->delimiters[0] ?? null;
	}

	public function StringChop(string $text): array
	{
		$mainDelimiter = $this->getMainDelimiter();

		if ($mainDelimiter === null)
			return $text;

		if (count($this->delimiters) > 1)
			$text = str_replace(array_slice($this->delimiters, 1), $mainDelimiter, $text);

		return explode($mainDelimiter, $text);
	}
}