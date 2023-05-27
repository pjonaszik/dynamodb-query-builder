<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

use Aws\DynamoDb\Marshaler;
use Pjonaszik\DynamoDb\QueryBuilder\QueryBuilderException;
use ReflectionClass;

/**
 * Class Factory
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class Factory
{
    /**
     * @var Marshaler
     */
    protected Marshaler $marshaler;

    /**
     * Factory constructor.
     *
     * @param Marshaler $marshaler
     */
    public function __construct(Marshaler $marshaler)
    {

        $this->marshaler = $marshaler;
    }

    /**
     * @param array $spec
     *
     * @return AbstractExpression|string
     * @throws QueryBuilderException|\ReflectionException
     */
    public function getExpression(array $spec): string|AbstractExpression
    {
        extract($spec);
        $class = __NAMESPACE__ . '\\' . $expression . 'Expression';
        if (!class_exists($class)) {
            throw QueryBuilderException::expressionNotFound($expression);
        }

        $reflection = new ReflectionClass($class);
        /** @var AbstractExpression $expression */
        $expression = $reflection->newInstance($this->marshaler)
            ->setKey($key)
            ->setValue($value)
            ->setOperator($operator);

        return $expression;
    }
}
