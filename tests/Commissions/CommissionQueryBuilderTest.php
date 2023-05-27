<?php

declare(strict_types=1);

namespace Tests\Commissions;

use Carbon\Carbon;
use Faker\Factory; // Has been used to generate DESCRIPTION and WARNING constants
use DateTimeInterface;
use Tests\DynamoDbTestCase;

class CommissionQueryBuilderTest extends DynamoDbTestCase
{
    protected string $tableName = 'commissions-test';

    private const COMMISSION_ID = [
        'ee9d21c9-117c-5033-a6d2-02c99ee9aacc',
        'ad3469a8-b73e-56f1-b397-fbe90409ccf3',
        '3af3c9c4-929a-5ecd-97d4-860aa79a4304',
        '1e991fd1-1780-5fc3-93a0-b7b065f3de2f',
    ];

    private const DESCRIPTION = [
        "Dinah my dear! Let this be a very interesting dance to watch,' said Alice, who was a bright brass plate with the Lory, as soon as look at all comfortable, and it set to work nibbling at the end of.",
        "Adventures of hers would, in the sea!' cried the Mock Turtle yawned and shut his eyes.--'Tell her about the right size, that it was certainly English. 'I don't know much,' said Alice, seriously.",
        "Alice didn't think that very few little girls in my life!' She had already heard her voice close to them, they were mine before. If I or she fell very slowly, for she had nothing else to do, and.",
        "Cat. 'Do you know why it's called a whiting?' 'I never thought about it,' added the Gryphon, the squeaking of the tail, and ending with the distant sobs of the earth. Let me see--how IS it to make.",
    ];

    private const WARNING = [
        "IS a Caucus-race?' said Alice; 'I can't go no lower,' said the Dodo, 'the best way to explain the.",
        "Queen, in a solemn tone, 'For the Duchess. 'I make you grow shorter.' 'One side of the house of.",
        "There was a general clapping of hands at this: it was an old crab, HE was.' 'I never was so.",
        "Queen ordering off her unfortunate guests to execution--once more the shriek of the sort,' said.",
    ];

    private const RETAILER = [
        'adidas',
        'nike',
        'decathlon',
        'booking',
    ];

    private const CLIENTS = [
        'shoop',
        'pouch',
        'igraal',
        'pepper',
    ];

    // --global-secondary-indexes IndexName=client_id-retailer_id-index,KeySchema=['{"AttributeName"="client_id","KeyType"="HASH"}','{"AttributeName"="retailer_id","KeyType"="RANGE"}'],Projection="{ProjectionType=ALL}",ProvisionedThroughput='{"ReadCapacityUnits"="10","WriteCapacityUnits"="10"}' --provisioned-throughput ReadCapacityUnits=5,WriteCapacityUnits=4
    private array $schema = [
        'AttributeDefinitions'  =>
            [
                [
                    'AttributeName' => 'client_id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'commission_id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'retailer_id',
                    'AttributeType' => 'S',
                ],
            ],
        'TableName'             => null,
        'KeySchema'             => [
            [
                'AttributeName' => 'client_id',
                'KeyType'       => 'HASH'
            ],
            [
                'AttributeName' => 'commission_id',
                'KeyType'       => 'RANGE'
            ],
        ],
        'GlobalSecondaryIndexes' => [
            [
                'IndexName' => 'client_id-retailer_id-index',
                'KeySchema' => [
                    [
                        'AttributeName' => 'client_id',
                        'KeyType' => 'HASH'
                    ],
                    [
                        'AttributeName' => 'retailer_id',
                        'KeyType' => 'RANGE'
                    ]
                ],
                'Projection' => [
                    'ProjectionType' => 'ALL'
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 10,
                    'WriteCapacityUnits' => 10
                ]
            ]
        ],
        'ProvisionedThroughput' => [
            'ReadCapacityUnits'  => 5,
            'WriteCapacityUnits' => 4
        ],
        'Projection' => [
            'ProjectionType' => 'ALL'
        ],
    ];

    public function setUp(): void
    {
        // Reset table
        if ($this->tableExist($this->tableName)) {
            $this->deleteTable($this->tableName);
        }

        // Create new one
        $this->schema['TableName'] = $this->tableName;
        $this->createSchema($this->tableName, $this->schema);
        $qb = $this->getQueryBuilder($this->tableName)->batchWriteItems();
        $items = $this->getFixtures();
        foreach ($items as $item) {
            $qb->put($item);
        }
        $q = $qb->getQuery();

        $this->db()->batchWriteItem($q);
    }

    private function getFixtures(): array
    {
        $now = Carbon::now();
        $fixtures = [];
        for ($i = 0; $i < 4; $i++) {
            if ($i !== 0) {
                $now->addWeek();
            }
            $startDate = $now->addDay();
            $endDate = $now->addWeek();
            $commission = [
                "commission_id" => self::COMMISSION_ID[$i],
                "description" => self::DESCRIPTION[$i],
                "incoming" => [
                    "type" => "percent",
                    "from" => 5,
                    "to" => ""
                ],
                "outgoing" => [
                    "from" => 5,
                    "type" => "percent"
                ],
                "startDate" => $startDate->format(DateTimeInterface::ATOM),
                "endDate" => $endDate->format(DateTimeInterface::ATOM),
                "status" => "",
                "warning" => self::WARNING[$i],
                "retailer_id" => self::RETAILER[$i],
                "client_id" => self::CLIENTS[$i],
            ];
            $fixtures[] = $commission;
        }
        return $fixtures;
    }
    private static function getCommission(
        string $id,
        string $retailerId,
        string $clientId,
        int $startDays,
        int $endDays,
        array $incoming = [
            'type' => 'percent',
            'from' => '',
            'to' => '',
        ],
        array $outgoing = [
            'from' => '',
            'to' => '',
        ],
    ): array {
        $now = Carbon::now();
        $startDate = $now->addDays($startDays);
        $endDate = $now->addDays($endDays);
        return [
            "commission_id" => $id,
            "description" => self::DESCRIPTION[0],
            "incoming" => $incoming,
            "outgoing" => $outgoing,
            "startDate" => $startDate->format(DateTimeInterface::ATOM),
            "endDate" => $endDate->format(DateTimeInterface::ATOM),
            "status" => "",
            "warning" => self::WARNING[0],
            "retailer_id" => $retailerId,
            "client_id" => $clientId,
        ];
    }

    public function tearDown(): void
    {

        if ($this->tableExist($this->tableName)) {
            $this->deleteTable($this->tableName);
        }
    }

    public function testCommissionsHasBeenCreated()
    {
        $result = $this->scanTable($this->tableName);
        $items = $this->getFixtures();
        $this->assertTrue(count($result) === count($items));
        // Assert $result has $items keys
        $itemsKeys = array_keys($items[0]);
        foreach ($itemsKeys as $key) {
            $this->assertArrayHasKey($key, $items[0]);
        }
    }

    public function testDeleteCommissions()
    {
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            // Those two are required --because of KeySchema
            ->delete([
                'commission_id' => self::COMMISSION_ID[0],
                'client_id' => self::CLIENTS[0],
            ])
            ->delete([
                'commission_id' => self::COMMISSION_ID[1],
                'client_id' => self::CLIENTS[1],
            ])
            ->getQuery();
        $this->db()->batchWriteItem($q);
        $result = $this->scanTable($this->tableName);
        // expect 2 remaining commissions
        $this->assertCount(2, $result);
    }

    public function testDeleteAndCreateOneCommission()
    {
        $faker = Factory::create();
        $commission = self::getCommission(
            id: $faker->uuid(),
            retailerId: 'mango',
            clientId: 'igraal',
            startDays: 1,
            endDays: 8,
            incoming: [
                'type' => 'percent',
                'from' => 10,
                'to' => '',
            ],
            outgoing: [
                'from' => 6,
                'to' => '',
            ]
        );
        $q = $this->getQueryBuilder($this->tableName)->batchWriteItems()
            ->delete([
                'commission_id' => self::COMMISSION_ID[2],
                'client_id' => self::CLIENTS[2],
            ])
            ->delete([
                'commission_id' => self::COMMISSION_ID[3],
                'client_id' => self::CLIENTS[3],
            ])
            ->put($commission)
            ->getQuery();
        $this->db()->batchWriteItem($q);
        $result = $this->scanTable($this->tableName);
        // We should have 3 items
        $this->assertCount(3, $result);
        // Is our new object inserted ?
        $commissionResult = [];
        foreach ($result as $item) {
            if ($item['commission_id'] === $commission['commission_id']
                && $item['retailer_id'] === $commission['retailer_id']
                && $item['client_id'] === $commission['client_id']
            ) {
                $commissionResult = $item;
                break;
            }
        }
        $this->assertEqualsCanonicalizing($commission, $commissionResult);
    }

    public function testGetCommissionByCommissionId()
    {

        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->eq('commission_id', self::COMMISSION_ID[0])
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(1, $result);
        $this->assertEquals(['S' => self::COMMISSION_ID[0]], $result[0]['commission_id']);
    }


    public function testGetCommissionsByCommissionIdOrClientId()
    {

        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->andEq('commission_id', self::COMMISSION_ID[0])
            ->orEq('client_id', self::CLIENTS[1])
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        $this->assertContains(self::COMMISSION_ID[0], [$result[0]['commission_id']['S'], $result[1]['commission_id']['S'],]);
    }


    public function testGetCommissionsUsingAttributesNames()
    {

        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(
                [
                    '#description' => 'description',
                ],
            )
            ->contains('#description', 'Alice didn\'t think that')
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(1, $result);
        $this->assertEquals(['S' => self::DESCRIPTION[2]], $result[0]['description']);
    }

    public function testGetCommissionsUsingAttributesNamesAndFilters()
    {

        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->withAttributeNames(
                [
                    '#retailerId' => 'retailer_id',
                    '#status' => 'status',
                ],
            )
            ->eq('#status', '')
            ->andContains('#retailerId', 'i') // 3 retailers contain 'i'
            ->notEq('#retailerId', self::RETAILER[0]) // Remove 'adidas'
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result);
        // We must have nike and booking
        $this->assertContains(self::RETAILER[1], [$result[0]['retailer_id']['S'], $result[1]['retailer_id']['S'],]);
        $this->assertContains(self::RETAILER[3], [$result[0]['retailer_id']['S'], $result[1]['retailer_id']['S'],]);
    }

    public function testGetActiveCommissionsInMoreThan1Month(): void
    {
        $now = Carbon::now();
        $q = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->gtEq('startDate', $now->addMonth()->format(DateTimeInterface::ATOM))
            ->getQuery();
        $result = $this->db()->scan($q)['Items'];
        $this->assertCount(2, $result); // We must have 2 commissions
    }

    public function testUpdateCommission(): void
    {
        $qRetailer0 = $this->getQueryBuilder($this->tableName)
            ->scan()
            ->eq('commission_id', self::COMMISSION_ID[0])
            ->getQuery();
        $result = $this->db()->scan($qRetailer0)['Items'];
        $this->assertCount(1, $result);
        $this->assertSame(self::COMMISSION_ID[0], $result[0]['commission_id']['S']);
        $q = $this->getUpdateBuilder($this->tableName)
            ->buildUpdateQuery([
                'commission_id' => self::COMMISSION_ID[0],
                'client_id' => self::CLIENTS[0],
            ], [
                "description" => self::DESCRIPTION[3],
                "status" => "updated",
                "warning" => self::WARNING[3],
            ])->getQuery();
        $this->db()->updateItem($q);
        $result = $this->db()->scan($qRetailer0)['Items'];
        $this->assertCount(1, $result);
        $this->assertSame(self::COMMISSION_ID[0], $result[0]['commission_id']['S']);
        $this->assertSame(self::CLIENTS[0], $result[0]['client_id']['S']);
        $this->assertSame(self::DESCRIPTION[3], $result[0]['description']['S']);
        $this->assertSame('updated', $result[0]['status']['S']);
        $this->assertSame(self::WARNING[3], $result[0]['warning']['S']);
    }
}
