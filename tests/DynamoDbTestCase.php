<?php

declare(strict_types=1);

namespace Tests;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use Aws\Sdk;
use Pjonaszik\DynamoDb\QueryBuilder\QueryBuilder;
use Pjonaszik\DynamoDb\QueryBuilder\UpdateBuilder;
use PHPUnit\Framework\TestCase;

class DynamoDbTestCase extends TestCase
{
    /**
     * @return DynamoDbClient
     */
    protected function db(): DynamoDbClient
    {

        $sdk = new Sdk(
            [
                'region'      => 'eu-central-1',
                'version'     => 'latest',
                'endpoint'    => 'http://localstack:4566',
                'credentials' => false,
            ]
        );

        return $sdk->createDynamoDb();
    }

    /**
     * @param $tableName
     * @param array|null $schema
     * @return Result
     */
    protected function createSchema($tableName, ?array $schema = null): Result
    {
        return $this->db()->createTable(
            $schema ??
            [
                'AttributeDefinitions'  =>
                    [
                        [
                            'AttributeName' => 'id',
                            'AttributeType' => 'N',
                        ],
                    ],
                'TableName'             => $tableName,
                'KeySchema'             =>
                    [
                        [
                            'AttributeName' => 'id',
                            'KeyType'       => 'HASH'
                        ]
                    ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits'  => 1,
                    'WriteCapacityUnits' => 1
                ]
            ]
        );
    }

    /**
     * @param $tableName
     *
     * @return void
     */
    protected function deleteTable($tableName): void
    {

        $db = $this->db();
        $db->deleteTable(
            [
                'TableName' => $tableName
            ]
        );
    }

    /**
     * @return Marshaler
     */
    protected function getMarshaler(): Marshaler
    {

        return new Marshaler();
    }

    /**
     * @param $tableName
     * @param array|null $requestQuerySyntax
     * @return array
     */
    protected function scanTable($tableName, ?array $requestQuerySyntax = null): array
    {
        $scanProps = $requestQuerySyntax
            ? array_merge(['TableName' => $tableName], $requestQuerySyntax)
            : ['TableName' => $tableName];
        return array_map(
            function ($item) {
                return $this->getMarshaler()->unmarshalItem($item);
            },
            $this->db()->scan($scanProps)['Items']
        );
    }

    /**
     * @param string $tableName
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(string $tableName): QueryBuilder
    {

        return new QueryBuilder(
            $tableName,
        );
    }

    /**
     * @param string $tableName
     *
     * @return UpdateBuilder
     */
    protected function getUpdateBuilder(string $tableName): UpdateBuilder
    {

        return new UpdateBuilder(
            $tableName,
        );
    }

    /**
     * @param $tableName
     *
     * @return bool
     */
    protected function tableExist($tableName): bool
    {

        $result = $this->db()->listTables()['TableNames'];

        return in_array($tableName, $result);
    }
}
