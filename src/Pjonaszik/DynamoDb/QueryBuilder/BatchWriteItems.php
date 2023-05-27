<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

/**
 * Class BatchWriteItems
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
class BatchWriteItems extends AbstractQueryBuilder
{
    /**
     * @var array
     */
    protected array $query = [
        'RequestItems' => []
    ];

    /**
     * @param array $key
     *
     * @return $this
     */
    public function delete(array $key): static
    {

        if (empty($this->query['RequestItems'][$this->tableName])) {
            $this->query['RequestItems'][$this->tableName] = [
                [
                    'DeleteRequest' => [
                        'Key' => $this->marshaler->marshalItem($key)
                    ]
                ]
            ];
        } else {
            $this->query['RequestItems'][$this->tableName][] = [
                'DeleteRequest' => [
                    'Key' => $this->marshaler->marshalItem($key)
                ]
            ];
        }

        return $this;
    }


    /**
     * @param array $item
     *
     * @return $this
     */
    public function put(array $item): static
    {
        if (empty($this->query['RequestItems'][$this->tableName])) {
            $this->query['RequestItems'][$this->tableName] = [
                [
                    'PutRequest' => [
                        'Item' => $this->marshaler->marshalItem($item)
                    ]
                ]
            ];
        } else {
            $this->query['RequestItems'][$this->tableName][] = [
                'PutRequest' => [
                    'Item' => $this->marshaler->marshalItem($item)
                ]
            ];
        }

        return $this;
    }
}
