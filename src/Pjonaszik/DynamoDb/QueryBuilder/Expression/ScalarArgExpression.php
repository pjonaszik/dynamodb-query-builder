<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

use Aws\DynamoDb\Marshaler;

/**
 * Class ScalarArgExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
abstract class ScalarArgExpression extends AbstractExpression
{
    /**
     * @var int|string
     */
    protected int|string $value;

    /**
     * @var string
     */
    protected string $paramName;

    /**
     * ScalarArgExpression constructor.
     *
     * @param Marshaler $marshaler
     */
    public function __construct(protected Marshaler $marshaler)
    {

        parent::__construct($this->marshaler);

        $this->paramName = hash('crc32b', uniqid());
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue(mixed $value): static
    {

        $this->value = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {

        return $this->marshaler->marshalItem([':' . $this->paramName => $this->value]);
    }

    /**
     * @return string
     */
    public function getExpressionString(): string
    {

        return sprintf($this->expression, $this->key, $this->paramName);
    }
}
