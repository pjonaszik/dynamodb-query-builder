<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class GtEqExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class GtEqExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s >= :%s';
}
