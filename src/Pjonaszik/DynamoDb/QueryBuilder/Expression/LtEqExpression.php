<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class LtEqExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class LtEqExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s <= :%s';
}
