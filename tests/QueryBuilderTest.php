<?php

declare(strict_types=1);

namespace Tests;

class QueryBuilderTest extends DynamoDbTestCase
{
    protected string $tableName = 'dynamo-query-builder-table-test';

    protected const FIXTURES = [
        [
            'id'     => 1,
            'game'   => 'FIFA',
            'version'   => '2023',
            'status' => 'enabled',
        ],
        [
            'id'     => 2,
            'game'   => 'COD',
            'version'   => 'Modern Warfare',
            'status' => 'disabled',
        ],
        [
            'id'     => 3,
            'game'   => 'Clash Of Clans',
            'version'   => '',
            'status' => 'enabled',
        ],

    ];

    public function setUp(): void
    {
        // Reset table
        if ($this->tableExist($this->tableName)) {
            $this->deleteTable($this->tableName);
        }

        // Create new one
        $this->createSchema($this->tableName);
        $qb = $this->getQueryBuilder($this->tableName)->batchWriteItems();
        foreach (self::FIXTURES as $item) {
            $qb->put($item);
        }
        $q = $qb->getQuery();

        $this->db()->batchWriteItem($q);
    }

    public function tearDown(): void
    {

        if ($this->tableExist($this->tableName)) {
            $this->deleteTable($this->tableName);
        }
    }

    public function testBatchPut(): void
    {
        $result = $this->scanTable($this->tableName);
        $this->assertEquals('FIFA', $result[1]['game']);
        $this->assertEquals(1, $result[1]['id']);
        $this->assertEquals('COD', $result[0]['game']);
        $this->assertEquals(2, $result[0]['id']);
    }

    public function testBatchDelete(): void
    {
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            ->delete(['id' => 1])
            ->delete(['id' => 2])
            ->delete(['id' => 3])
            ->getQuery();
        $this->db()->batchWriteItem($q);
        $result = $this->scanTable($this->tableName);

        $this->assertCount(0, $result);
    }

    public function testBatchPutDelete(): void
    {
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            ->delete(['id' => 1])
            ->delete(['id' => 2])
            ->delete(['id' => 3])
            ->put(
                [
                    'id'     => 4,
                    'game'   => 'PES',
                    'version'   => '2022',
                    'status' => 'enabled',
                ]
            )
            ->put(
                [
                    'id'     => 5,
                    'game'   => 'Resident Evil',
                    'version'   => '5',
                    'status' => 'disabled'
                ]
            )
            ->getQuery();

        $this->db()->batchWriteItem($q);
        $result = $this->scanTable($this->tableName);
        $this->assertCount(2, $result);
        $this->assertEquals('Resident Evil', $result[0]['game']);
        $this->assertEquals(5, $result[0]['id']);
        $this->assertEquals('PES', $result[1]['game']);
        $this->assertEquals(4, $result[1]['id']);
    }

    public function testScanAndEq(): void
    {
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->andEq('id', 1)
            ->getQuery();

        $result = $this->db()->scan($q)['Items'];

        $this->assertCount(1, $result);
        $this->assertEquals(['N' => 1], $result[0]['id']);
    }

    public function testScanAndOrEq(): void
    {
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->andEq('id', 2)
            ->orEq('id', 3)
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        $this->assertEquals(['N' => 2], $result[0]['id']);
        $this->assertEquals(['N' => 3], $result[1]['id']);
    }

    public function testScanContains(): void
    {
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(
                [
                    '#game' => 'game',
                ],
            )
            ->contains('#game', 'FIFA')
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(1, $result);
        $this->assertEquals(['S' => 'FIFA'], $result[0]['game']);
    }

    public function testScanAndOrContains(): void
    {
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(
                [
                    '#game' => 'game',
                    '#version' => 'version',
                ],
            )
            ->andContains('#version', 'Modern Warfare')
            ->orContains('#game', 'FIFA')
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        $this->assertEquals(['S' => 'COD'], $result[0]['game']);
        $this->assertEquals(['S' => 'FIFA'], $result[1]['game']);
    }

    public function testScanBeginsWith(): void
    {
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            ->put(
                [
                    'id'     => 6,
                    'game'   => 'Animal Crossing Company',
                    'version'   => 'Haunted Mansion',
                    'status' => 'enabled'
                ]
            )
            ->getQuery();
        $this->db()->batchWriteItem($q);

        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(
                [
                    '#game' => 'game'
                ]
            )
            ->andBeginsWith('#game', 'A')
            ->orBeginsWith('#game', 'Animal')
            ->orBeginsWith('#game', 'FI')
            ->orBeginsWith('#game', 'CO')
            ->getQuery();

        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(3, $result);
        $this->assertEquals(['S' => 'COD'], $result[0]['game']);
        $this->assertEquals(['S' => 'FIFA'], $result[1]['game']);
        $this->assertEquals(['S' => 'Animal Crossing Company'], $result[2]['game']);
    }

    public function testScanIn(): void
    {
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(['#game' => 'game'])
            ->in('#game', ['Animal Crossing Company', 'PES', 'COD'])
            ->orIn('id', [1]) // Optional - but assertCount will fail
            ->getQuery();

        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        $this->assertEquals(['S' => 'Modern Warfare'], $result[0]['version']);
        $this->assertEquals(['S' => '2023'], $result[1]['version']);
    }

    public function testScanNotEq(): void
    {
        $id = 1;
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->notEq('id', $id)
            ->getQuery();

        $result = $this->db()->scan($q)['Items'];

        $this->assertEquals(count($result), 2);

        $idArray = array_map(
            function ($item) {
                return $item['id']['N'];
            },
            $result
        );

        $this->assertFalse(in_array($id, $idArray));
    }

    public function testScanAndNotEq(): void
    {
        $id_fifa = 1;
        $id_pes = 4;
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->notEq('id', $id_fifa)
            ->andNotEq('id', $id_pes)
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        $this->assertEquals(2, $result[0]['id']['N']);
        $this->assertEquals(3, $result[1]['id']['N']);
    }

    public function testSubQuery(): void
    {
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            ->put(
                [
                    'id'     => 6,
                    'game'   => 'BattleField',
                    'version'   => 'ww2',
                    'status' => 'disabled'
                ]
            )
            ->put(
                [
                    'id'     => 7,
                    'game'   => 'Splashtoons',
                    'version'   => 'colorful',
                    'status' => 'disabled'
                ]
            )
            ->getQuery();
        $this->db()->batchWriteItem($q);

        $qb = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames([
                '#game' => 'game',
                '#status' => 'status',
                '#version' => 'version',
            ])
            ->eq('#status', 'disabled')
            ->subQuery(
                $this->getQueryBuilder($this->tableName)
                    ->scan()
                    ->withAttributeNames([ // Expressions attributes here must have been defined in the main query in (withAttributeNames)
                        '#game' => 'game',
                        '#version' => 'version',
                    ])
                    ->beginsWith('#game', 'Splash')
                    ->orBeginsWith('#version', 'ww2')
            )->getQuery();
        $items = $this->db()->scan($qb)['Items'];
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $item = $this->getMarshaler()->unmarshalItem($item);
            $this->assertEquals('disabled', $item['status']);
            $this->assertEquals(1, preg_match('/^(Splash|Battle).*$/', $item['game']));
        }
    }

    public function testUpdateQuery(): void
    {
        $gameId = 2;
        $gameName = 'BattleField';
        $gameVersion = '2030';
        $q = $this->getUpdateBuilder($this->tableName)
            ->buildUpdateQuery(['id' => $gameId], [
                'game'   => 'BattleField',
                'version'   => $gameVersion,
                'status' => 'disabled',
            ])
            ->getQuery();
        $this->db()->updateItem($q);
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->eq('id', $gameId)
            ->getQuery(['Limit' => 1]);
        $result = $this->scanTable($this->tableName, $q);
        $this->assertCount(1, $result);
        $this->assertSame($gameName, $result[0]['game']);
        $this->assertSame($gameVersion, $result[0]['version']);
    }
}
