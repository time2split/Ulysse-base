<?php
/**
 * Fonctions d'aide pour les tableaux et objets traversables.
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Helpers\Arrays;

/**
 * Vérifie que $array soit une liste, c'est-à-dire un tableau ne contenant des clés entieres.
 */
function isList(array $array): bool
{
	foreach (\array_keys($array) as $k)
	{
		if (\is_string($k))
			return false;
	}
	return true;
}

/**
 * Vérifie que array est un document, c'esy-à-dire un tableau contenant au moins une clé string.
 *
 * @param array $array
 * @return bool
 */
function isDocument(array $array): bool
{
	return !isList($array);
}

function isArrayAccessible($set)
{
	return \is_array($set) || $set instanceof \ArrayAccess;
}

function unwind(array $array, $field): array
{
	$ret = [];
	$tmp = $array;

	foreach ((array)$array[$field] as $val)
	{
		$tmp[$field] = $val;
		$ret[] = $tmp;
	}
	return $ret;
}

/**
 * Récupère les colonnes $colNames de $dataSet.
 * Les colonnes doivent exister dans chaque $item de $dataset.
 *
 * @param array $dataSet
 * @param mixed ...$colNames
 * @return array Un tableau indexé par les clés de $dataset et contenant les colonnes de $colNames.
 */
function selectColumn(array $dataset, ...$colNames): array
{
	if (empty($dataset))
		return [];

	$ret = [];

	foreach ($dataset as $k => $data)
	{
		foreach ($colNames as $col)
			$ret[$k][$col] = $data[$col];
	}
	return $ret;
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
function substituteAlias($id, $aliases)
{
	$substitutions = getAllAliasesSubstitutions($id, $aliases);
	return $substitutions[\count($substitutions) - 1];
}

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
 * Substitue les clés de $array avec tout ses alias.
 *
 * @param \ArrayAccess|array $array
 * @param array $aliases
 */
function substituteKeyAlias(array &$array, $aliases, int $maxDepth = 0)
{
	array_kwalk_depth($array, function ($val, &$key, $aliases)
	{
		if (isset($aliases[$key]))
			$key = substituteAlias($key, $aliases);
	}, $aliases, $maxDepth);
}

/**
 * Similaire à array_walk mais permet de modifier les clés.
 * L'ordre d'apparition des clés peut être modifié.
 *
 * @param array $array
 * @param callable $callback
 * @param mixed $userData
 * @param int $maxDepth
 *        	Profondeur maximale autorisée. <br>
 *        	Une valeur de 0 traitera les éléments de $array sans récursion. <br>
 *        	Une valeur < 0 définit une profondeur infinie
 */
function array_kwalk_depth(array &$array, callable $callback, $userData = null, int $maxDepth = -1): void
{
	if ($maxDepth == 0)
		array_kwalk($array, $callback, $userData);
	elseif ($maxDepth < 0)
		array_kwalk_recursive($array, $callback, $userData);
	else
		array_kwalk_depth_($array, $callback, $userData, $maxDepth);
}

/**
 * Similaire à array_walk mais permet de modifier les clés.
 * L'ordre d'apparition des clés peut être modifié.
 *
 * @param array $array
 * @param callable $callback
 * @param mixed $userData
 */
function array_kwalk(array &$array, callable $callback, $userData = null): void
{
	$newKeys = [];

	foreach ($array as $k => &$v)
	{
		$lastk = $k;
		$callback($v, $k, $userData);

		if ($k !== $lastk)
		{
			$newKeys[$k] = $v;
			unset($array[$lastk]);
		}
	}
	$array += $newKeys;
}

/**
 * Appel récursif pour chaque sous valeur tableau.
 * L'ordre d'apparition des clés peut être modifié.
 *
 * @param array $array
 * @param callable $callback
 * @param mixed $userData
 */
function array_kwalk_recursive(array &$array, callable $callback, $userData = null): void
{
	$newKeys = [];

	foreach ($array as $k => &$v)
	{
		$lastk = $k;
		$callback($v, $k, $userData);

		if (\is_array($v))
			array_kwalk_recursive($v, $callback, $userData);

		if ($k !== $lastk)
		{
			$newKeys[$k] = $v;
			unset($array[$lastk]);
		}
	}
	$array += $newKeys;
}

function array_kwalk_depth_(array &$array, callable $callback, $userData, int $maxDepth): void
{
	$newKeys = [];
	$maxDepth--;

	foreach ($array as $k => &$v)
	{
		$lastk = $k;
		$callback($v, $k, $userData);

		if (\is_array($v) && $maxDepth >= 0)
			array_kwalk_depth_($v, $callback, $userData, $maxDepth);

		if ($k !== $lastk)
		{
			$newKeys[$k] = $v;
			unset($array[$lastk]);
		}
	}
	$array += $newKeys;
}

function array_kmap_depth(callable $callback, array $array, int $maxDepth = -1): array
{
	if ($maxDepth == 0)
		return array_kmap($callback, $array);
	elseif ($maxDepth < 0)
		return array_kmap_recursive($callback, $array);
	else
		return array_kmap_depth_($callback, $array, $maxDepth);
}

/**
 * Similaire à array_map mais permet de modifier les clés.
 * Une différence est à noter : la fonction ne prends en paramèter qu'un unique tableau (pour le
 * moment ...).
 *
 * @param callable $callback
 * @param array $array
 * @return array
 */
function array_kmap(callable $callback, array $array): array
{
	$ret = [];

	foreach (\array_map($callback, $array, \array_keys($array)) as list ($key, $val))
		$ret[$key] = $val;

	return $ret;
}

function array_kmap_recursive(callable $callback, array $array): array
{
	$ret = [];

	foreach (\array_map($callback, $array, \array_keys($array)) as list ($key, $val))
	{
		if (\is_array($val))
			$val = array_kmap_recursive($callback, $val);

		$ret[$key] = $val;
	}
	return $ret;
}

function array_kmap_depth_(callable $callback, array $array, int $maxDepth): array
{
	$maxDepth--;
	$ret = [];

	foreach (\array_map($callback, $array, \array_keys($array)) as list ($key, $val))
	{
		if (\is_array($val) && $maxDepth >= 0)
			$val = array_kmap_depth_($callback, $val);

		$ret[$key] = $val;
	}
	return $ret;
}

/**
 * Renvoie la représentation php de $array.
 * La différence avec la fonction \export de php est que cette fonction fait en sorte de n'afficher
 * les clés que lorque nécessaire.
 * De plus elle utilise la notation [] pour représenter les tableaux.
 *
 * @see http://php.net/manual/en/function.var-export.php Documentation de var_export
 * @param array $array
 */
function array_export(array $array, string $eol = "\n", string $tab = "\t"): string
{
	return array_export_($array, $eol, $tab, 0, null) . ']';
}

function array_export_(array $array, string $eol, string $tab, int $depth): string
{
	$nextDepth = $depth + 1;
	$hereTab = $tab === '' ? '' : \str_repeat($tab, $depth);
	$insideTab = $hereTab . $tab;
	$buffer = '';
	$lastKey = -1;

	foreach ($array as $k => $item)
	{

		// Vérification de la séquence de clés
		if (\is_int($k))
		{
			if ($k == $lastKey + 1)
			{
				$lastKey++;
				$hereKey = null;
			}
			else
			{
				$lastKey = $k;
				$hereKey = $k;
			}
		}
		else
			$hereKey = $k;

		if ($hereKey !== null)
			$hereKey = \var_export($hereKey, true) . ' => ';

		if (\is_array($item))
		{
			$tmp = array_export_($item, $eol, $tab, $nextDepth, $lastKey);
			$hereBuff = $tmp . (empty($tmp) ?: $insideTab) . ']';
		}
		else
			$hereBuff = \var_export($item, true);

		$buffer .= $insideTab . $hereKey . $hereBuff . ',' . $eol;
	}
	if (empty($buffer))
		return '[';

	return '[' . $eol . $buffer;
}

/**
 * Transforme toutes les valeurs non scalaires qui peuvent l'être dans $source en array
 *
 * @param mixed $source
 * @return array Si $source est un scalaire retourne (array)$source
 */
function makeItArray($source): array
{
	if (\is_scalar($source))
		return (array)$source;

	return makeValuesArray($source);
}

/**
 * Transforme toutes les valeurs non scalaires qui peuvent l'être dans $source en array.
 *
 * @param mixed $source
 */
function makeValuesArray($source)
{
	if (\is_array($source));
	elseif (\is_iterable($source))
	{
		// On convertie $source en array
		$tmp = [];

		foreach ($source as $k => $v)
			$tmp[$k] = $v;

		$source = $tmp;
	}
	elseif (\is_object($source))
	{
		if (\is_callable([
			$source,
			'toArray'
		]))
		{
			$source = $source->toArray();
		}
	}

	if (\is_iterable($source))
	{
		foreach ($source as &$v)
			$v = makeValuesArray($v);
	}
	return $source;
}

/**
 * Applatit sur les valeurs d'un iterable
 * Effectue flattenArrayValues(makeItArray($source))
 *
 * @param mixed $source
 * @return array
 */
function flattenValues(array $source, int $maxDepth = -1): array
{
	$ret = [];
	$nmaxdepth = $maxDepth < 0 ? -1 : $maxDepth - 1;

	foreach ($source as $v)
	{
		if ($maxDepth == 0)
			$ret[] = [
				$v
			];
		elseif (is_array($v))
			$ret[] = flattenValues($v, $nmaxdepth);
		else
			$ret[] = [
				$v
			];
	}

	if (empty($ret))
		return $ret;

	return \array_merge(...$ret);
}

function flatten(array $source, string $delimiter = '.', int $maxDepth = -1): array
{
	if ($maxDepth === 1)
		return $source;

	$ret = [];
	flatten_($source, $delimiter, $maxDepth, '', $ret);
	return $ret;
}

function flatten_(array $source, string $delimiter, int $maxDepth, string $key, array &$ret): void
{
	if ($maxDepth === 0)
		return;

	if ($maxDepth < 0)
		$nmaxDepth = -1;
	else
		$nmaxDepth = $maxDepth - 1;

	$prefixKey = empty($key) ? '' : $key . $delimiter;

	foreach ($source as $k => $v)
	{
		$tmpKey = $prefixKey . $k;

		if ($nmaxDepth == 0)
			$ret[$tmpKey] = $v;
		else if (is_array($v))
		{
			if (empty($v))
				$ret[$tmpKey] = [];
			else
				flatten_($v, $delimiter, $nmaxDepth, $tmpKey, $ret);
		}
		else
			$ret[$tmpKey] = $v;
	}
}