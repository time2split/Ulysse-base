<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Interfaces;

interface StringDelimitersI
{

	public function addStringDelimiter(string ...$delimiter): void;

	public function addStringDelimiters(array $delimiters): void;

	public function cleanStringDelimiters(): void;

	public function getStringDelimiters(): array;

	public function stringChop(string $text): array;
}