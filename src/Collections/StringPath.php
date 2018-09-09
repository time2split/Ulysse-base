<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Collections;

/**
 * Représentation d'un chemin.
 *
 * Un chemin est une suite d'identifiants représentant chacun un noeud de données.
 * Ces identifiants sont séparés textuellement par des délimiteurs.
 *
 * Il existe deux types d'identifiant spéciaux : 'current' et 'previous'.
 * 'current' représente le noeud de donnée actuel, et 'previous' le noeud de donnée précédent.
 *
 * L'analogie doit être faite avec les chemins d'un système de fichiers ; par défaut la classe
 * utilise le délimiteur et les identifiants issus de linux pour son système de fichiers.
 *
 * @author zuri
 *
 */
class StringPath extends StringCleaver
{

	private $tok_current;

	private $tok_previous;

	public function __construct(string $path = null, $delimiters = '/', string $tok_current = '.', string $tok_previous = '..')
	{
		parent::__construct($path, $delimiters);
		$this->tok_current = (array)$tok_current;
		$this->tok_previous = (array)$tok_previous;
	}

	public function isEmpty(): bool
	{
		return $this->text === null || $this->text === '';
	}

	public function isAbsolute(): bool
	{
		return $this->count() && $this[0] === '';
	}

	public function isRelative(): bool
	{
		return !$this->isAbsolute();
	}

	public function isNormalized(): bool
	{
		if ($this->isAbsolute())
		{
			$tokens = array_flip(array_merge($this->tok_current, $this->tok_previous));
			$me = array_flip($this->getArrayCopy());
			return !array_intersect_key($tokens, $me);
		}

		// Relatif
		$me = $this->getArrayCopy();
		$c = count($me);

		if ($c == 0)
			return true;

		$offset = 0;
		$tok_all = array_flip(array_merge($this->tok_current, $this->tok_previous));
		$tok_current = array_flip($this->tok_current);
		$tok_previous = array_flip($this->tok_previous);

		if (isset($tok_current[$me[0]]))
			$offset++;
		else
		{
			while (isset($tok_previous[$me[$offset]]))
				$offset++;
		}
		return !array_intersect_key(array_flip(array_slice($me, $offset, -1)), $tok_all + [
			'' => true
		]);
	}

	/**
	 * Nettoie les séparateurs de débuts et de fin
	 */
	public function trim(): void
	{
		$this->p_trim(3);
	}

	public function ltrim(): void
	{
		$this->p_trim(1);
	}

	public function rtrim(): void
	{
		$this->p_trim(2);
	}

	/**
	 *
	 * @param string $path
	 * @param int $directions
	 *        	1 gauche 2 droite 3 les deux
	 * @return string
	 */
	private function p_trim(int $directions): void
	{
		if ($this->isEmpty())
			return;

		$parts = $this->getArrayCopy();
		$right = (bool)($directions & 2);
		$left = (bool)($directions & 1);

		if ($left)
		{
			$keys = array_keys($parts);

			foreach ($keys as $k)
			{
				if ($parts[$k] !== '')
					break;

				unset($parts[$k]);
			}
		}

		if ($right)
		{
			$keys = array_reverse(array_keys($parts));

			foreach ($keys as $k)
			{
				if ($parts[$k] !== '')
					break;

				unset($parts[$k]);
			}
		}

		if ($parts !== $this->getArrayCopy())
			$this->setParts($parts);
	}

	public function normalize(): void
	{
		$tok_all = array_flip(array_merge($this->tok_current, $this->tok_previous));
		$tok_current = array_flip($this->tok_current);
		$tok_previous = array_flip($this->tok_previous);

		$isAbs = $this->isAbsolute();
		$new = [];
		$me = $this->getArrayCopy();
		$lastKeyAdd = -1;

		if ($isAbs)
			$me = array_slice($me, 1);

		// On supprime les delimiteurs successifs
		$keys = array_slice(array_keys($me), 1, -1);

		foreach ($keys as $k)
		{
			if ($me[$k] === '')
				unset($me[$k]);
		}

		foreach ($me as $pathPart)
		{
			if (isset($tok_current[$pathPart]))
			{
				// Pour les chemins relatifs on conserve le premier 'current'
				if ($lastKeyAdd === -1)
					$new[++$lastKeyAdd] = $pathPart;
			}
			elseif (isset($tok_previous[$pathPart]))
			{
				if ($lastKeyAdd < 0 || isset($tok_all[$new[$lastKeyAdd]]))
					$new[++$lastKeyAdd] = $pathPart;
				else
					unset($new[$lastKeyAdd--]);
			}
			else
				$new[++$lastKeyAdd] = $pathPart;
		}

		// Absolu
		if ($isAbs)
		{
			// On supprime l'éventuel 'current' après la racine
			if (!empty($new) && isset($tok_current[$new[0]]))
				unset($new[0]);

			// On nettoie les 'previous' après la racine
			$offset = 0;

			foreach ($new as $val)
			{
				if (!isset($tok_previous[$val]))
					break;

				$offset++;
			}

			if ($offset > 0)
				$new = array_slice($new, $offset);

			array_unshift($new, '');
		}
		// Relatif
		else
		{
			// Suppression d'un 'current' qui est suivi d'un 'previous'
			if (count($new) >= 2 && isset($tok_current[$new[0]]) && isset($tok_previous[$new[1]]))
				unset($new[0]);
		}
		$this->setParts($new);
	}
}