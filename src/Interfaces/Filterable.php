<?php
namespace Ulysse\Base\Interfaces;

/**
 * An object which is a set of data that can be filtered.
 *
 * @author orodriguez
 *
 */
interface Filterable
{

	/**
	 * Apply a filter.
	 */
	public function filter(Filter $filter);
}