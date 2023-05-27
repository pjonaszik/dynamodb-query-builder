<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class BeginsWithExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class BeginsWithExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = 'begins_with(%s, :%s)';
}
