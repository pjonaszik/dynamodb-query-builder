<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

use Aws\DynamoDb\Marshaler;

/**
 * Class AbstractExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
abstract class AbstractExpression implements ExpressionInterface
{
    /**
     * @var string
     */
    protected string $operator;

    /**
     * @var string
     */
    protected string $expression;

    /**
     * @var string
     */
    protected string $key;

    /**
     * AbstractExpression constructor.
     *
     * @param Marshaler $marshaler
     */
    public function __construct(protected Marshaler $marshaler)
    {
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    abstract public function setValue(mixed $value): mixed;

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey(string $key): static
    {

        $this->key = $key;

        return $this;
    }

    /**
     * @param string $operator
     *
     * @return $this
     */
    public function setOperator(string $operator): static
    {

        $this->operator = $operator;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {

        return $this->operator;
    }
}
