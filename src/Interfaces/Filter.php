<?php
namespace Ulysse\Base\Interfaces;

interface Filter
{

	public function validate($item): bool;

	public function notValidate($item): bool;

	public function filter(iterable $set, bool $notValidate = false): array;

	// public function getKeys(): array;

	// public function getProjectionKeys(): array;
}