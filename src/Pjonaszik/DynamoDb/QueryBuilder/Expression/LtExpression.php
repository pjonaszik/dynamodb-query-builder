<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class LtExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class LtExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s < :%s';
}
