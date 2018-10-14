<?php
/**
 * Fonctions d'aide pour les tableaux et objets traversables.
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Functions\Arrays;

/**
 * Vérifie que $array soit une liste, c'est-à-dire un tableau ne contenant des clés entières.
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
 * Vérifie que array est un document, c'est-à-dire un tableau contenant au moins une clé string.
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

/**
 * Déplie un tableau sur un champ.
 *
 * Le dépliage d'un tableay $array agit sur un champ $field.
 * Il consiste à effectuer autant de copie du tableau d'origine qu'il y a d'items dans (array)$array[$field], et d'affecter pour chaque copie $copie[$field] à une valeur de array[$field].
 *
 * @param array $array
 *        	La tableau à déplier
 * @param mixed $field
 *        	Le champ du tableau à déplier
 * @return array
 */
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

function keyPad(array $array, array $keys, $value = null): array
{
	foreach ($keys as $key)
	{
		if (!array_key_exists($key, $array))
			$array[$key] = $value;
	}
	return $array;
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
function keyWalkDepth(array &$array, callable $callback, $userData = null, int $maxDepth = -1): void
{
	if ($maxDepth == 0)
		keyWalk($array, $callback, $userData);
	elseif ($maxDepth < 0)
		keyWalkRecursive($array, $callback, $userData);
	else
		keyWalkDepth_($array, $callback, $userData, $maxDepth);
}

/**
 * Similaire à array_walk mais permet de modifier les clés.
 * L'ordre d'apparition des clés peut être modifié.
 *
 * @param array $array
 * @param callable $callback
 * @param mixed $userData
 */
function keyWalk(array &$array, callable $callback, $userData = null): void
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
function keyWalkRecursive(array &$array, callable $callback, $userData = null): void
{
	$newKeys = [];

	foreach ($array as $k => &$v)
	{
		$lastk = $k;
		$callback($v, $k, $userData);

		if (\is_array($v))
			keyWalkRecursive($v, $callback, $userData);

		if ($k !== $lastk)
		{
			$newKeys[$k] = $v;
			unset($array[$lastk]);
		}
	}
	$array += $newKeys;
}

function keyWalkDepth_(array &$array, callable $callback, $userData, int $maxDepth): void
{
	$newKeys = [];
	$maxDepth--;

	foreach ($array as $k => &$v)
	{
		$lastk = $k;
		$callback($v, $k, $userData);

		if (\is_array($v) && $maxDepth >= 0)
			keyWalkDepth_($v, $callback, $userData, $maxDepth);

		if ($k !== $lastk)
		{
			$newKeys[$k] = $v;
			unset($array[$lastk]);
		}
	}
	$array += $newKeys;
}

function keyMapDepth(callable $callback, array $array, int $maxDepth = -1): array
{
	if ($maxDepth == 0)
		return keyMap($callback, $array);
	elseif ($maxDepth < 0)
		return keyMapRecursive($callback, $array);
	else
		return keyMapDepth_($callback, $array, $maxDepth);
}

/**
 * Similaire à array_map mais permet de modifier les clés.
 *
 * @param callable $callback
 * @param array $array
 * @return array
 */
function keyMap(callable $callback, array ...$arrays): array
{
	$ret = [];

	// Un seul tableau en argument
	if (count($arrays) === 1)
	{
		$array = $arrays[0];

		foreach (\array_map($callback, \array_keys($array), $array) as list ($key, $val))
			$ret[$key] = $val;
	}
	// Plusieurs tableaux
	else
	{
		// On récupère toutes les clés des tableaux
		$allKeys = \array_keys(\array_merge(...$arrays));

		// On complète les clés non présentes dans certains tableaux avec des valeurs null
		foreach ($arrays as &$array)
			$array = keyPad($array, $allKeys);

		foreach ($allKeys as $key)
		{
			foreach (\array_map($callback, $key, ...selectColumn($arrays, $key)) as list ($key, $val))
				$ret[$key] = $val;
		}
	}
	return $ret;
}

function keyMapRecursive(callable $callback, array $array): array
{
	$ret = [];

	foreach (\array_map($callback, $array, \array_keys($array)) as list ($key, $val))
	{
		if (\is_array($val))
			$val = keyMapRecursive($callback, $val);

		$ret[$key] = $val;
	}
	return $ret;
}

function keyMapDepth_(callable $callback, array $array, int $maxDepth): array
{
	$maxDepth--;
	$ret = [];

	foreach (\array_map($callback, $array, \array_keys($array)) as list ($key, $val))
	{
		if (\is_array($val) && $maxDepth >= 0)
			$val = keyMapDepth_($callback, $val);

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
function export(array $array, string $eol = "\n", string $tab = "\t"): string
{
	return export_($array, $eol, $tab, 0, null) . ']';
}

function export_(array $array, string $eol, string $tab, int $depth): string
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
			$tmp = export_($item, $eol, $tab, $nextDepth, $lastKey);
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

	return makeValuesArray($source, -1);
}

/**
 * Transforme toutes les valeurs non scalaires qui peuvent l'être dans $source en array.
 *
 * @param mixed $source
 * @param int $maxDepth
 */
function makeValuesArray($source, int $maxDepth = -1)
{
	if ($maxDepth === 0)
		return $source;

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

	if (\is_iterable($source) && $maxDepth !== 1)
	{
		$nmaxdepth = $maxDepth < 0 ? -1 : $maxDepth - 1;

		foreach ($source as &$v)
			$v = makeValuesArray($v, $nmaxdepth);
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