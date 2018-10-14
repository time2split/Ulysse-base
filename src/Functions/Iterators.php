<?php
namespace Awadac\DataBundle\Functions\Iterators;

/**
 * Retourne le prochain bloc de données de taille $size.
 *
 * @param \Iterator $iterator
 * @param int $size
 *        	La taille du block de données. <br>
 *        	Si $size < 0, l'itérateur entier sera consommé.
 * @return array
 */
function nextBlock(\Iterator $iterator, int $size = -1): array
{
	$ret = [];

	for (;;)
	{
		if ($size >= 0)
		{
			if ($size == 0)
				break;

			$size--;
		}

		if (!$iterator->valid())
			break;

		$k = $iterator->key();
		$ret[$k] = $iterator->current();
		$iterator->next();
	}
	return $ret;
}

/**
 * Consomme l'itérateur en entier et retourne ses données dans un tableau.
 *
 * @param \Iterator $iterator
 * @return array
 */
function toArray(\Iterator $iterator): array
{
	$ret = [];

	while ($iterator->valid())
	{
		$ret[] = $iterator->current();
		$iterator->next();
	}
	return $ret;
}

/**
 * Transforme $data en Iterator.
 *
 * Le retour peut être la donnée elle-même si elle est déjà un itérateur.
 *
 * @param mixed $data
 * @return \Iterator|null null si impossible
 */
function toIterator($data): \Iterator
{
	if ($data instanceof \Iterator)
		return $data;

	if ($data instanceof \Traversable)
		return new \IteratorIterator($data);

	if (is_array($data))
		return new \ArrayIterator($data);
}