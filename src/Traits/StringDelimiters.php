<?php
namespace Ulysse\Base\Traits;

use function Ulysse\Base\Helpers\Arrays\flattenValues;
use function Ulysse\Base\Helpers\Arrays\makeItArray;

trait StringDelimiters
{

	protected $delimiters;

	public function setDelimiter(string ...$delimiters): void
	{
		$this->delimiters = [];
		$this->addDelimiter(...$delimiters);
	}

	public function setDelimiters($delimiters): void
	{
		$this->delimiters = [];

		if ($delimiters !== null)
			$this->addDelimiters($delimiters);
	}

	public function addDelimiter(string ...$delimiters): void
	{
		$this->delimiters = array_merge($this->delimiters, $delimiters);
	}

	public function addDelimiters($delimiters): void
	{
		$this->addDelimiter(...flattenValues(makeItArray($delimiters)));
	}

	public function getDelimiters(): array
	{
		return $this->delimiters;
	}

	public function getMainDelimiter(): ?string
	{
		return $this->delimiters[0] ?? null;
	}

	public function chop(string $text): array
	{
		$mainDelimiter = $this->getMainDelimiter();

		if ($mainDelimiter === null)
			return $text;

		if (count($this->delimiters) > 1)
			$text = str_replace(array_slice($this->delimiters, 1), $mainDelimiter, $text);

		return explode($mainDelimiter, $text);
	}
}