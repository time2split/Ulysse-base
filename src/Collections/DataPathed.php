<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Collections;

use function Ulysse\Base\notExists;
use function Ulysse\Base\Functions\Arrays\flatten;
use function Ulysse\Base\Functions\Arrays\flattenValues;
use Ulysse\Base\Traits\AttributableT;
use Ulysse\Base\Traits\StringDelimitersT;
use Ulysse\Base\Interfaces\DataPathedI;

/**
 * Représentation de données hiérarchisées accessibles avec une clé chemin.
 * Une clé chemin est une clé représentant un chemin dans la collection. Celle-ci est définie par une
 * suite de sous-clés, séparées par un/des délimiteur(s).
 *
 * <h1>Exemple ($data = newDataPathed([...],'.'))</h1>
 * <ul>
 * <li>$subDataPathed = $data['k1.k2']</li>
 * <li>$data['k1.k2.k3'] = $value</li>
 * <li>$var =& $data['k1.k2.k3']</li>
 * </ul>
 */
class DataPathed implements \IteratorAggregate, DataPathedI
{
	use StringDelimitersT;
	use AttributableT;

	/**
	 * Les données de la collection.
	 * Aucune contrainte n'est appliquée concernant le type de $data mais seul un type
	 * array|\ArrayAccess pourra ammener à une recherche de clé récursive.
	 */
	protected $data;

	/**
	 * Le chemin parcouru par la dernière opération.
	 *
	 * @var array
	 */
	protected $coveredPath;

	/**
	 * Le chemin restant à parcourir par la dernière opération.
	 *
	 * @var array
	 */
	protected $remainingPath;

	/**
	 * Forcer la création des chemins inexistants pour une nouvelle clé ajoutée.
	 *
	 * @var string
	 */
	private $forceCreate = false;

	// ======================================================
	// Init
	// ======================================================
	public function __construct($data = null, $delimiters = '.')
	{
		$this->attributes = [
			self::ATTRIBUTE_FORCE_CREATE => false,
			self::ATTRIBUTE_ACCESS_EXCEPTION_CLASS => \OutOfBoundsException::class
		];
		$this->setDelimiters($delimiters);
		$this->setData($data);
	}

	public function newSameAsMe($data = null): DataPathedI
	{
		return new static($data, $this->delimiters);
	}

	public function setForceCreate(bool $forceCreate = true): void
	{
		$this->forceCreate = $forceCreate;
	}

	public function getForceCreate(): bool
	{
		return $this->forceCreate;
	}

	public function setData($data): void
	{
		$this->data = $this->makeData($data);
	}

	public function getData()
	{
		return $this->data;
	}

	// ======================================================
	// Internal
	// ======================================================
	protected function AccessThrowException(): bool
	{
		$att = $this->getAttribute(self::ATTRIBUTE_ACCESS_EXCEPTION_CLASS);
		return is_string($att) || $att === true;
	}

	protected function makeData($data)
	{
		return self::makeData_($data, ...$this->delimiters);
	}

	protected static function makeData_($data, string ...$delimiters)
	{
		if (!\is_array($data))
			return $data;

		$ret = [];

		foreach ($data as $k => $v)
		{
			$pathSet = self::makeThePath_($k, ...$delimiters);
			$pret = & $ret;

			foreach ($pathSet as $p)
			{
				if (!isset($pret[$p]))
					$pret[$p] = [];

				$pret = & $pret[$p];
			}
			$pret = self::makeData_($v, ...$delimiters);
		}
		return $ret;
	}

	protected function makeThePath($path): array
	{
		return self::makeThePath_($path, ...$this->delimiters);
	}

	protected static function makeThePath_($path, string ...$delimiters): array
	{
		$pathSet = flattenValues((array)$path);

		if (empty($pathSet))
			return [];

		$delimiter = $delimiters[0];

		// On explose les sous-chemins
		\array_walk_recursive($pathSet, function (&$path) use ($delimiters, $delimiter)
		{
			if (!is_string($path))
				return;

			$pathReplaced = \str_replace($delimiters, $delimiter, $path);
			$path = \explode($delimiter, $pathReplaced);
		});
		return flattenValues($pathSet);
	}

	protected function getExceptionClass(): string
	{
		$att = $this->getAttribute(self::ATTRIBUTE_ACCESS_EXCEPTION_CLASS);

		if (is_string($att))
			return $att;

		return \OutOfBoundsException::class;
	}

	protected function pathException(array $coveredPath, array $remainingPath)
	{
		$delimiter = $this->delimiters[0] ?? '?';
		$pathS = \implode($delimiter, $coveredPath);
		$pathNES = \implode($delimiter, $remainingPath);
		$class = static::class;
		$ExceptionClass = $this->getExceptionClass();

		if (empty($pathS))
			return new $ExceptionClass("($class) The path '$pathNES' cannot be covered");

		return new $ExceptionClass("($class) The path '$pathS' has been covered but not '$pathNES'");
	}

	// ======================================================
	protected function &getOrAlert($path)
	{
		$val = & $this->getNotAlert($path);

		if (!empty($this->remainingPath))
			throw self::pathException($this->coveredPath, $this->remainingPath);

		return $val;
	}

	protected function &getNotAlert($path)
	{
		$pathSet = $this->makeThePath($path);
		$set = &$this->data;
		$this->coveredPath = [];

		foreach ($pathSet as $p)
		{
			if (\is_array($set) && \array_key_exists($p, $set))
				$set = &$set[$p];
			else
			{
				$v = notExists();
				$this->remainingPath = \array_slice($pathSet, \count($this->coveredPath));
				return $v;
			}
			$this->coveredPath[] = $p;
		}
		return $set;
	}

	protected function unsetOrAlert($path): void
	{
		$pathSet = $this->makeThePath($path);
		$lastKey = \array_pop($pathSet);
		$v = & $this->getOrAlert($pathSet);

		if (\is_array($v) && !\array_key_exists($lastKey, $v))
			throw $this->pathException($pathSet, (array)$lastKey);

		unset($v[$lastKey]);
	}

	protected function unsetNotAlert($path): void
	{
		$pathSet = $this->makeThePath($path);
		$lastKey = \array_pop($pathSet);
		$v = & $this->getNotAlert($pathSet);

		if (\is_array($v) && \array_key_exists($lastKey, $v))
			unset($v[$lastKey]);
	}

	// ======================================================
	// Accès publics
	// ======================================================
	public static function valueExists($val): bool
	{
		return $val !== notExists();
	}

	public function exists($paths): bool
	{
		return self::valueExists($this->get($paths));
	}

	public function &get($path)
	{
		if ($this->AccessThrowException())
			return $this->getOrAlert($path);

		return $this->getNotAlert($path);
	}

	public function setRef($path, &$val, bool $forceCreate = null): void
	{
		$val = $this->makeData($val);

		if (empty($path))
		{
			$this->data = & $val;
			return;
		}

		if ($forceCreate === null)
			$forceCreate = $this->forceCreate;

		$pathSet = $this->makeThePath($path);
		$set = & $this->data;
		$lastKey = \array_pop($pathSet);
		$covered = [];
		$pathSetS = \implode($this->delimiters[0], $pathSet);

		foreach ($pathSet as $p)
		{
			if (!($set instanceof \ArrayAccess) && !\is_array($set))
			{
				if ($forceCreate)
					$set = (array)$set;
				else
					throw new \InvalidArgumentException("The element \$this[$pathSetS] must be an array or an instance of ArrayAccess");
			}

			if ($forceCreate && !\key_exists($p, $set))
				$set[$p] = [];
			elseif (\key_exists($p, $set));
			else
				throw self::pathException($covered, \array_slice($pathSet, \count($covered)));

			$covered[] = $p;
			$set = & $set[$p];
		}

		if (!($set instanceof \ArrayAccess) && !\is_array($set))
		{
			if ($forceCreate)
				$set = (array)$set;
			else
				throw new \InvalidArgumentException("The element \$this[$pathSetS] must be an array or an instance of ArrayAccess");
		}
		$set[$lastKey] = & $val;
	}

	public function set($path, $val, bool $forceCreate = null): void
	{
		$this->setRef($path, $val, $forceCreate);
	}

	public function unset($path): void
	{
		if ($this->AccessThrowException())
			$this->unsetOrAlert($path);
		else
			$this->unsetNotAlert($path);
	}

	public function getKeys(int $maxDepth = 1): array
	{
		$delimiter = $this->delimiters[0];
		$flat = flatten($this->data, $delimiter, $maxDepth);
		return array_keys($flat);
	}

	public function getIterator(): \Iterator
	{
		$data = $this->data;

		if (\is_array($data))
			return new \ArrayIterator($data);

		return new \ArrayIterator([
			$data
		]);
	}

	public function serialize()
	{
		return \serialize([
			$this->data,
			$this->delimiters,
			$this->forceCreate
		]);
	}

	public function unserialize($serialized)
	{
		$this->initInternal();
		list ($this->data, $this->delimiters, $this->forceCreate) = \unserialize($serialized);
	}

	public function count(): int
	{
		$c = 0;
		array_walk_recursive($this->data, function () use (&$c)
		{
			$c++;
		});
		return $c;
	}

	// ======================================================
	// ArrayAccess
	// ======================================================
	public function &offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetExists($offset)
	{
		return $this->exists($offset);
	}

	public function offsetUnset($offset)
	{
		$this->unset($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value, $this->forceCreate);
	}

	// ======================================================
	// ObjectAccess
	// ======================================================
	public function &__get($path)
	{
		$data = & $this->get($path);
		$ret = $this->newSameAsMe();
		$ret->setRef(null, $data);
		return $ret;
	}

	public function __set($path, $val)
	{
		$this->set($path, $val, $this->forceCreate);
	}

	public function __isset($path)
	{
		return $this->exists($path);
	}

	public function __unset($path)
	{
		$this->unset($path);
	}
}