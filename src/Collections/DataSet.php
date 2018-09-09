<?php
namespace Awadac\DataBundle\Object\Collection;

use Ulysse\Base\Collections\DataPathed;

/**
 * Ensemble de données accessibles par chemin avec délimiteur '.'
 *
 * @author orodriguez
 *
 */
class DataSet extends DataPathed
{

	/**
	 * Le délimiteur des chemins ($path)
	 *
	 * @var string
	 */
	const PATH_DELIMITER = '.';

	function __construct($config = [])
	{
		parent::__construct($config, self::PATH_DELIMITER);
	}
}