<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

use Aws\DynamoDb\Marshaler;
use InvalidArgumentException;

/**
 * Class ArrayArgExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
abstract class ArrayArgExpression extends AbstractExpression
{
    /**
     * @var array
     */
    protected array $value = [];

    /**
     * ArrayArgExpression constructor.
     *
     * @param Marshaler $marshaler
     */
    public function __construct(protected Marshaler $marshaler)
    {

        parent::__construct($marshaler);
    }

    /**
     * @param mixed $value
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setValue(mixed $value): static
    {

        if(!is_array($value)) {
            throw new InvalidArgumentException(
                'Argument should be array'
            );
        }

        foreach ($value as $val) {
            $this->value += $this->marshaler->marshalItem(
                [
                    ':' . hash('crc32b', uniqid()) => $val
                ]
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }
}
