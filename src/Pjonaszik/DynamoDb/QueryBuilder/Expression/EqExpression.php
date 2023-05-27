<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class EqExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class EqExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s = :%s';
}
