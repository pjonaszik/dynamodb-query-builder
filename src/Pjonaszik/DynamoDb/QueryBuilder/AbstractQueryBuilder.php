<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

use Aws\DynamoDb\Marshaler;

/**
 * Class AbstractQueryBuilder
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    /**
     * @var array
     */
    protected array $query;

    /**
     * @var Marshaler
     */
    protected Marshaler $marshaler;

    /**
     * QueryBuilder constructor.
     *
     * @param string $tableName
     * @param null|string $indexName
     */
    public function __construct(
        protected string $tableName,
        protected ?string $indexName = null,
    ) {
        $this->marshaler = new Marshaler();
    }

    /**
     * @param array|null $queryRequestSyntax
     * @return array
     */
    public function getQuery(?array $queryRequestSyntax = null): array
    {
        return $this->query;
    }
}
