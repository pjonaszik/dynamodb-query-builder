<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder\Expression;

/**
 * Class InExpression
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder\Expression
 */
class InExpression extends ArrayArgExpression
{
    /**
     * @var string
     */
    protected string $expression = '%s in (%s)';

    /**
     * @return string
     */
    public function getExpressionString(): string
    {

        return sprintf(
            $this->expression,
            $this->key,
            implode(
                ',',
                array_keys($this->value)
            )
        );
    }
}
