<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class GtExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class GtExpression extends ScalarArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s > :%s';
}
