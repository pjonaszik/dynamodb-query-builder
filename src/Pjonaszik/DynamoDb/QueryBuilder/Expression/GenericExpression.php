<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class GenericExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class GenericExpression implements ExpressionInterface
{
    /**
     * GenericExpression constructor.
     *
     * @param string $expressionStr
     * @param mixed  $value
     * @param string $operator
     */
    public function __construct(
        protected string $expressionStr,
        protected mixed $value,
        protected string $operator = 'and',
    ) {
    }

    /**
     * @return string
     */
    public function getExpressionString(): string
    {
        return $this->expressionStr;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }
}
