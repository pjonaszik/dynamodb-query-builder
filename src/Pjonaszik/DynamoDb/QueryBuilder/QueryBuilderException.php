<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

use Exception;

/**
 * Class QueryBuilderException
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
class QueryBuilderException extends Exception
{
    /**
     * @param string $expression
     *
     * @return QueryBuilderException
     */
    public static function expressionNotFound(string $expression): QueryBuilderException
    {

        return new self(
            sprintf(
                'Expression "%s" not found',
                htmlspecialchars($expression)
            )
        );
    }
}
