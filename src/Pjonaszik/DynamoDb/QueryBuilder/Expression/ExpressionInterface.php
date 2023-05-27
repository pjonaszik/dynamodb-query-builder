<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Interface ExpressionInterface
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
interface ExpressionInterface
{
    /**
     * @return string
     */
    public function getExpressionString(): string;

    /**
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * @return string
     */
    public function getOperator(): string;
}
