<?php

/**
 *
 * @author Olivier Rodriguez
 */
namespace Ulysse\Base\Interfaces;

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
interface DataPathedI extends ArrayI, StringDelimitersI, Attributable
{

	/**
	 * Si l'attribut est vrai alors, lors d'une affectation, tout chemin non existant dans les données sera créé.
	 */
	public const ATTRIBUTE_FORCE_CREATE = 0;

	/**
	 * L'attribut doit être un booléen ou une chaine de caractère représentant le type d'exception à lever lors d'un accès à un chemin non existant.
	 * Si l'attribut est false, ou d'un autre type que bool ou string alors aucune exception ne sera levée.
	 */
	public const ATTRIBUTE_ACCESS_EXCEPTION_CLASS = 1;

	// ======================================================
	public function __construct($data = null, $delimiters = '.');

	// ======================================================
	public function setData($data): void;

	public function getData();

	// ======================================================

	/**
	 * Vérifie si la valeur retournée spécifie l'inexistance de celle-ci
	 *
	 * @param mixed $val
	 * @return bool
	 */
	public static function valueExists($val): bool;

	/**
	 * Renvoie toutes les clés ayant au plus une certaine longueur à partir de la racine.
	 *
	 * Les sous-clés des clés renvoyées ne sont pas renvoyées unitairement.
	 *
	 * @param int|array $depths
	 * @return array
	 */
	public function getKeys(int $maxDepth = 1): array;

	// ======================================================
	public function &get($path);

	/**
	 * Affecte une valeur à un chemin.
	 */
	public function setRef($path, &$val, bool $forceCreate = null): void;

	public function set($path, $val, bool $forceCreate = null): void;

	public function exists($paths): bool;

	public function unset($path): void;

	// ======================================================
	public function &__get($path);

	public function __set($path, $val);

	public function __isset($path);

	public function __unset($path);
}