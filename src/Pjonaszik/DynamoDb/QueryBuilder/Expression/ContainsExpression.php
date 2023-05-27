<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class ContainsExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class ContainsExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = 'contains(%s, :%s)';
}
