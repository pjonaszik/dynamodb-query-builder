<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class ExpressionCollection
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class ExpressionCollection implements ExpressionInterface
{
    /**
     * @var array
     */
    protected array $expressions = [];

    /**
     * @var string
     */
    protected string $operator;

    /**
     * @param ExpressionInterface $expression
     */
    public function addExpression(ExpressionInterface $expression)
    {

        $this->expressions[] = $expression;
    }

    /**
     * @param array $expressions
     */
    public function addExpressionArray(array $expressions)
    {

        foreach ($expressions as $expression) {
            $this->addExpression($expression);
        }
    }

    /**
     * @return string
     */
    public function getExpressionString(): string
    {

        $expressionStr = '(%s)';

        $expressions = [];
        /** @var ExpressionInterface $expression */
        foreach ($this->expressions as $expression) {
            if (count($expressions)) {
                $expressions[] = $expression->getOperator();
            }
            $expressions[] = $expression->getExpressionString();
        }

        return sprintf($expressionStr, implode(' ', $expressions));
    }

    /**
     * @return array
     */
    public function getValue(): array
    {

        $value = [];
        /** @var ExpressionInterface $expression */
        foreach ($this->expressions as $expression) {
            $value += $expression->getValue();
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {

        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator(string $operator = 'and')
    {

        $this->operator = $operator;
    }
}
