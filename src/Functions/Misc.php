<?php
namespace Ulysse\Base\Functions\Misc;

/**
 * Exporte les données en format de représentation php en utilisant array_export pour les tableaux.
 */
function export($val, string $eol = "\n", string $tab = "\t"): string
{
	if (\is_array($val))
		return \Ulysse\Base\Functions\Arrays\export($val, $eol, $tab);

	return var_export($val, true);
}