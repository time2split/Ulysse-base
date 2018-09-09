<?php
namespace Ulysse\Base;

use stdClass;

/**
 * A unique value that means 'value unexistant'
 */
function notExists()
{
	static $ret;

	if ($ret === null)
		$ret = new stdClass();

	return $ret;
}