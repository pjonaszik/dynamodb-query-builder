<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class NotEqExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class NotEqExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s <> :%s';
}
