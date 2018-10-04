<?php
namespace Ulysse\Base;

use stdClass;

/**
 * A unique value meaning 'unexistant value'
 */
function notExists()
{
	static $ret;

	if ($ret === null)
		$ret = new stdClass();

	return $ret;
}