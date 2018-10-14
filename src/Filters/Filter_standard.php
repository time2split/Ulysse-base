<?php
namespace Ulysse\Base\Filters;

use function Awadac\DataBundle\Helpers\ArrayHelp\flattenArray;
use function Awadac\DataBundle\Helpers\ArrayHelp\selectColumn;
use function Awadac\DataBundle\Helpers\Utils\export;
use Awadac\DataBundle\Object\Collection\DataSet;

/**
 * Langage standard de filtre.
 *
 * @author orodriguez
 *
 */
class Filter_standard implements \Ulysse\Base\Interfaces\Filter
{

    const COMPILE_VAR_NAME = '_';

    const OPERATOR_TO_PHP = [
        '$or' => '||',
        '$and' => '&&',
        '$not' => '!',
        '$eq' => '==',
        '$gt' => '>',
        '$lt' => '<',
        '$gte' => '>=',
        '$lte' => '<='
    ];

    const OPERATORS = [
        '$or',
        '$and',
        '$not',
        '$eq',
        '$gt',
        '$lt',
        '$gte',
        '$lte',
        '$between',
        '$in',
        '$prefix',
        '$suffix',
        '$factor',
        '$regex'
    ];

    protected $instructions;

    protected $keys = [];

    protected $project;

    public function __construct(array $filter, array $options = [])
    {
        if (empty($filter))
            $this->instructions = 'true';
        else {
            $filter = self::filterReformat($filter);
            $this->instructions = $this->compile($filter, null, $this->keys);
            $this->keys = \array_unique(\array_keys(flattenArray($this->keys)));
        }
        $this->project = $this->projectReformat((array) ($options['$project'] ?? []));
    }

    public static function projectReformat(array $project)
    {
        $ret = [];

        foreach ($project as $k => $p) {
            if (\is_array($p))
                $ret[$k] = $p;
            else
                $ret[$p] = null;
        }
        return $ret;
    }

    /**
     * Retransforme le filtre en un arbre syntaxique.
     *
     * Cela permet d'homogénéiser la structure du filtre pour les méthodes l'utilisant
     */
    public static function filterReformat(array $filter): array
    {
        $filter = new DataSet($filter);
        return self::filterReformat_($filter->get(), null);
    }

    private static function filterReformat_(array $filter, $parentKey): array
    {
        $ret = [];

        // Première étape : on transforme les valeurs k => v en [$k => v]
        foreach ($filter as $key => $val) {
            if (\is_int($key))
                $ret[] = $val;
            else {
                if (\is_array($val))
                    $val = self::filterReformat_($val, $key);

                $ret[] = [
                    $key => $val
                ];
            }
        }

        // Pour le moment la valeur de $parentKey n'est pas utile (à voir pour les opérateurs
        // suivants)
        if (! \is_string($parentKey))
            $ret = [
                '$and' => $ret
            ];

        return $ret;
    }

    private function compile(array $filter, $infos = null, &$keySet)
    {
        $c = \count($filter);

        if ($c == 0)
            return null;

        $exceptionMessage = __FUNCTION__ . ": Bad filter format";

        if ($c > 1)
            throw new \LogicException($exceptionMessage);

        if ($infos === null)
            $infos = [
                'var' => '$' . self::COMPILE_VAR_NAME
            ];

        $VAR = $infos['var'];

        $key = \array_keys($filter)[0];
        $val = \array_values($filter)[0];

        if (isset(self::OPERATOR_TO_PHP[$key])) {
            switch ($key) {
                case '$or':
                case '$and':
                    $subFilters = [];
                    $c = \count($val);

                    foreach ($val as $subFilterVal)
                        $subFilters[] = $this->compile($subFilterVal, $infos, $keySet);

                    if ($c == 1)
                        return "$subFilters[0]";

                    $delimiter = self::OPERATOR_TO_PHP[$key];
                    return '(' . \implode(" $delimiter ", $subFilters) . ')';
                default:
                    throw new \LogicException($exceptionMessage);
            }
        } elseif (\is_array($val)) {
            if (\count($val) != 1)
                throw new \LogicException($exceptionMessage . " under $key");

            $keySet[$key] = [];
            $val = \array_values($val)[0];
            $subKey = \array_keys($val)[0];
            $subVal = $val[$subKey];

            switch ($subKey) {
                case '$not':
                    $operator = self::OPERATOR_TO_PHP[$subKey];

                    return $operator . '(' . $this->compile([
                        $key => $subVal
                    ], $infos, $keySet[$key]) . ')';
                    break;

                case '$in':
                    $subVal = (array) $subVal;
                    $c = \count($subVal);

                    if ($c == 1)
                        return $this->compile([
                            $key => \array_values($subVal)[0]
                        ], $infos);

                    $index = \array_combine($subVal, \array_fill(0, \count($subVal), true));
                    $index = \array_export($index, '', '');
                    $key = \var_export($key, true);
                    return "array_key_exists($key,$index)";

                case '$between':
                    $subVal = \array_values((array) $subVal);
                    $vals = export($subVal, '', '');
                    $key = \var_export($key, true);
                    $start = $subVal[0] ?? null;
                    $end = $subVal[1] ?? null;
                    $starts = \var_export($start, true);
                    $ends = \var_export($end, true);

                    if ($start !== null && $end !== null)
                        return "(${VAR}[$key] >= $starts && ${VAR}[$key] <= $ends)";
                    elseif ($start === null && $end === null)
                        return 'true';
                    elseif ($start === null)
                        return "(${VAR}[$key] <= $ends)";
                    else
                        return "(${VAR}[$key] >= $starts)";

                case '$eq':
                case '$gt':
                case '$lt':
                case '$gte':
                case '$lte':
                    $operator = self::OPERATOR_TO_PHP[$subKey];
                    $vals = export($subVal, '', '');
                    $key = \var_export($key, true);
                    return "(${VAR}[$key] {$operator} $vals)";

                case '$regex':
                    $subVal = (string) $subVal;
                    $vals = \var_export($subVal, true);
                    $key = \var_export($key, true);
                    $len = \strlen($subVal);
                    return "preg_match($vals, ${VAR}[$key])";

                case '$prefix':
                    $subVal = (string) $subVal;
                    $vals = \var_export($subVal, true);
                    $key = \var_export($key, true);
                    $len = \strlen($subVal);
                    return "(substr(${VAR}[$key], 0, $len) === $vals)";

                case '$suffix':
                    $subVal = (string) $subVal;
                    $vals = \var_export($subVal, true);
                    $key = \var_export($key, true);
                    $len = \strlen($subVal);
                    return "(substr(${VAR}[$key], -$len) === $vals)";

                case '$factor':
                    $subVal = (string) $subVal;
                    $vals = \var_export($subVal, true);
                    $key = \var_export($key, true);
                    $len = \strlen($subVal);
                    return "(strpos(${VAR}[$key], $vals) !== false)";

                default:
                    $keySet[$key] = [];
                    $key = \var_export($key, true);
                    return $this->compile([
                        '$and' => [
                            $val
                        ]
                    ], \array_replace($infos, [
                        'var' => "${VAR}[$key]"
                    ]), $keySet);
            }
            throw new \Exception("Filter value cannot be an array");
        } else {
            $keySet[$key] = [];

            if (($key[0] ?? '') == '$')
                $key = \substr($key, 1);

            $key = \var_export($key, true);

            if ($val === null)
                return "${VAR}[$key] === null";
            else {
                $vals = var_export($val, true);
                return "${VAR}[$key] == $vals";
            }
        }
    }

    public function filter($data, bool $notValidate = false): array
    {
        if (! \is_array($data))
            return [];

        $data = \array_filter($data, [
            $this,
            $notValidate ? 'notValidate' : 'validate'
        ]);

        if (! empty($this->project))
            return selectColumn($data, ...$this->getProjectionKeys());

        return \array_values($data);
    }

    public function notValidate($data): bool
    {
        ${self::COMPILE_VAR_NAME} = $data;
        try {
            return eval("return !($this->instructions);");
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function validate($data): bool
    {
        ${self::COMPILE_VAR_NAME} = $data;
        try {
            return eval("return $this->instructions;");
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function getProjectionKeys(): array
    {
        return \array_keys($this->project);
    }
}