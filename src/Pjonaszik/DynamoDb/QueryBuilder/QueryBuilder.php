<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

/**
 * Class QueryBuilder
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
class QueryBuilder extends AbstractQueryBuilder
{
    /**
     * @return BatchWriteItems
     */
    public function batchWriteItems(): BatchWriteItems
    {

        return new BatchWriteItems($this->tableName);
    }

    /**
     * @return Scan
     */
    public function scan(): Scan
    {
        return new Scan($this->tableName);
    }

}
