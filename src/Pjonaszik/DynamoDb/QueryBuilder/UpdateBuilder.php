<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

/**
 * Class UpdateBuilder
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
class UpdateBuilder extends AbstractUpdateBuilder
{
    /**
     * @param array $keys
     * @param array $values
     * @return UpdateItem
     */
    public function buildUpdateQuery(array $keys, array $values): UpdateItem
    {
        return (new UpdateItem($this->tableName))
            ->buildUpdateQuery($keys, $values);
    }
}
