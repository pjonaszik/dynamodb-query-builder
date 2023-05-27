<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

/**
 * Interface QueryBuilderInterface
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
interface QueryBuilderInterface
{
    /**
     * @param array|null $queryRequestSyntax
     * @return array
     */
    public function getQuery(?array $queryRequestSyntax = null): array;
}
