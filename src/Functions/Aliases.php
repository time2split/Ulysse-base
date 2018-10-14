<?php
/**
 * Ensemble de fonctions permettant d'effectuer des opérations de substitutions simples.
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Functions\Aliases;

use function Ulysse\Base\Functions\Arrays\keyWalkDepth;

/**
 * Retourne tous les alias possibles de $id.
 *
 * @param mixed $id
 * @param \ArrayAccess|array $aliases
 * @return array
 */
function getAllAliasesSubstitutions($id, $aliases): array
{
	if (!isset($aliases[$id]))
		return [
			$id
		];

	$stack = [
		$id => null
	];

	do
	{
		$tmp = $aliases[$id];

		if (isset($stack[$tmp]))
			break;

		$stack[$tmp] = null;
		$id = $tmp;
	}
	while (isset($aliases[$id]));
	return \array_keys($stack);
}

/**
 * Substitution d'alias.
 *
 * Substitue à un identifiant son dernier alias dans une séquence d'alias.
 *
 * @param mixed $id
 *        	L'identifiant de référence
 * @param \ArrayAccess|array $aliases
 *        	Contient une séquence de <code>[id_i => alias_i]</code>
 */
function substituteId($id, $aliases)
{
	$substitutions = getAllAliasesSubstitutions($id, $aliases);
	return $substitutions[\count($substitutions) - 1];
}

/**
 * Substitue les clés de $array par tous ses alias.
 *
 * @param \ArrayAccess|array $array
 * @param array $aliases
 * @deprecated
 */
function substituteKeys(array &$array, $aliases, int $maxDepth = 0)
{
	keyWalkDepth($array, function ($val, &$key, $aliases)
	{
		if (isset($aliases[$key]))
			$key = substituteId($key, $aliases);
	}, $aliases, $maxDepth);
}