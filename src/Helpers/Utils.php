<?php
namespace Ulysse\Base\Helpers\Utils;

use function Ulysse\Base\Helpers\ArrayHelp\array_export;

/**
 * Exporte les données en format de représentation php en utilisant array_export pour les tableaux.
 */
function export($val, string $eol = "\n", string $tab = "\t"): string
{
    if (\is_array($val))
        return array_export($val, $eol, $tab);

    return \var_export($val, true);
}