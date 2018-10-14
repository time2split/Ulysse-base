<?php

/**
 * @author Olivier Rodriguez
 */

/**
 * Calcule la moyenne de $ints.
 */
function average(array $ints): int
{
    if (empty($ints))
        return 0;

    return \array_sum($ints) / \count($ints);
}

/**
 * Calcule la moyenne géométrique de $ints
 */
function geometricAverage(array $ints): int
{
    if (empty($ints))
        return 0;

    return \pow(\array_product($ints), 1 / \count($ints));
}

if (\extension_loaded('gmp')) {

    function bigAverage(array $ints): int
    {
        if (empty($ints))
            return 0;
        $sum = 0;

        \array_walk($ints, function (int $val) use (&$sum) {
            $sum = \gmp_add($sum, $val);
        });
        return \gmp_intval(\gmp_div($sum, \count($ints)));
    }

    function bigGeometricAverage(array $ints): int
    {
        if (empty($ints))
            return 0;
        $avg = 1;

        \array_walk($ints, function (int $val) use (&$avg) {
            $avg = \gmp_mul($avg, $val);
        });
        return \gmp_intval(\gmp_root($avg, \count($ints)));
    }
} elseif (\extension_loaded('bcmath')) {

    function bigAverage(array $ints): int
    {
        if (empty($ints))
            return 0;
        $sum = '0';

        \array_walk($ints, function (int $val) use (&$sum) {
            $sum = \bcadd($sum, (string) $val);
        });
        return (int) \bcdiv($sum, (string) \count($ints));
    }

    function bigGeometricAverage(array $ints): int
    {
        if (empty($ints))
            return 0;
        $avg = '1';

        \array_walk($ints, function (int $val) use (&$avg) {
            $avg = \bcmul($avg, (string) $val);
        });
        return (int) \bcpow($avg, (string) 1.0 / \count($ints));
    }
} else {

    function bigAverage(array $ints): int
    {
        return average($ints);
    }

    function bigGeometricAverage(array $ints): int
    {
        return geometricAverage($ints);
    }
}