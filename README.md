# Pjonaszik Aws DynamoQb Query Builder
Pjonaszik Amazon DynamoDb query builder.

## Installation
```shell
composer require pjonaszik/dynamodb-query-builder
```

## Tests
```shell
composer pintest
```

## How To

### 1) Create a "base" repository that other classes will extend from.
  - Inject an instance of DynamoDbClient, here: ```PjonaszikDynamoDbClient``` return an instance of DynamoDbClient (see 2nd snippet example)

```php
# BaseRepository
declare(strict_types=1);

namespace App\Repository;

use App\Service\PjonaszikDynamoDbClient;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Pjonaszik\DynamoDb\QueryBuilder\QueryBuilder;
use Pjonaszik\DynamoDb\QueryBuilder\UpdateBuilder;

abstract class BaseRepository
{
    protected string $tableName;
    protected DynamoDbClient $client;
    protected readonly Marshaler $marshaler;
    public function __construct(
        private readonly PjonaszikDynamoDbClient $dbClient,
    )
    {
        $this->client = $this->dbClient->getClient();
        $this->marshaler = new Marshaler();
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder(
            $this->tableName,
        );
    }

    /**
     * @return UpdateBuilder
     */
    protected function getUpdateBuilder(): UpdateBuilder
    {
        return new UpdateBuilder(
            $this->tableName,
        );
    }

    /**
     * @param array $query
     * @return array
     */
    public function fetch(array $query): array
    {
//        if (!array_key_exists('ReturnValues', $query)) {
//            $query['ReturnValues'] = 'ALL_NEW'; // NONE | ALL_OLD | UPDATED_OLD | ALL_NEW | UPDATED_NEW
//        }
        $result = $this->client->scan($query)['Items'];
        $total = count($result);
        if ($total > 0) {
            $results = [];
            if ($total === 1) {
                $results[] = $this->marshaler->unmarshalItem($result[0]);
                return $results;
            }

            for ($i = 0; $i < $total; $i++) {
                $results[] = $this->marshaler->unmarshalItem($result[$i]);
            }
            return $results;
        }
        return [];
    }

    /**
     * @param array $query
     * @return void
     */
    public function save(array $query): void
    {
//        if (!array_key_exists('ReturnValues', $query)) {
//            $query['ReturnValues'] = 'ALL_NEW'; // NONE | ALL_OLD | UPDATED_OLD | ALL_NEW | UPDATED_NEW
//        }
        $this->client->batchWriteItem($query);
    }

    /**
     * @param array $query
     * @return void
     */
    public function update(array $query): void
    {
//        if (!array_key_exists('ReturnValues', $query)) {
//            $query['ReturnValues'] = 'ALL_NEW'; // NONE | ALL_OLD | UPDATED_OLD | ALL_NEW | UPDATED_NEW
//        }
        $this->client->updateItem($query);
    }
}
```
```php
# PjonaszikDynamoDbClient
declare(strict_types=1);

namespace App\Service;

use App\Exception\DynamoDBConnectionException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Sdk;

class PjonaszikDynamoDbClient
{
    protected string $tableName;
    public function __construct(
        private readonly ?string $region,
        private readonly ?string $version,
        private readonly ?string $endpoint,
        private readonly ?string $key = null,
        private readonly ?string $secret = null,
    )
    {
    }

    public function getClient(): DynamoDbClient
    {
        /**
         * @var Sdk $sdk
         */
        try {
            $sdk = new Sdk(
                [
                    'region'      => $this->region,
                    'version'     => $this->version,
                    'endpoint'    => $this->endpoint,
                    'credentials' => $this->key && $this->secret
                        ? [
                            'key' => $this->key,
                            'secret' => $this->secret,
                        ]
                        : false,
                ]
            );
        } catch (DynamoDBConnectionException) {}
        return $sdk->createDynamoDb();
    }
}
```
  - BaseRepository methods explanations
    - ```getQueryBuilder```: It is a helper to initiate query builder in order to fetch data. Internally, it will build a ready to use query depending on your needs; chain this with the following:
      - ```scan```: If you are performing a query to ``GET`` data based on specific criteria
      - ```batchWriteItems```: If you intend to perform modification operations over data; ```PUT, DELETE```: With this, you can build a query that puts and deletes at once many items.
    - ```getUpdateBuilder```: Because the dynamodb client method ```batchWriteItem```doesn't support the ``update`` operation (see the Note on https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchWriteItem.html) - we have to perform this request separately.
      - ```buildUpdateQuery```: This accepts 2 parameters:
        - ``keys``: attributes defined in the keySchema when creating the dynamodb table, ```["id"=>1], ["commission_id"=>"xxx","client_id"=>"xxx"]```
        - ```values```: array of attribute=value to update.
    - Then comes the rest of the methods which are just helpers to perform CRUD ops and un/marshall data.
      - ```fetch```: To fetch data => Read or Get data 
      - ```save```: To Add/Put or Delete data
      - ```update```: To Update a specific item

### 2) Create your repository and extends it from BaseRepository 
```php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Commission;
use App\Exception\CommissionException;
use Carbon\Carbon;
use Exception;

class CommissionRepository extends BaseRepository
{
    protected string $tableName = 'commissions';

    /**
     * @throws CommissionException
     */
    public function findAll(?array $filters): array
    {
        $now = Carbon::now();
        try {
            // First we build the query
            $query = $this
                ->getQueryBuilder()
                // Call scan before filters etc... it's not calling the Dynamodb db scan action, but prepare the tool for query builder
                ->scan();
            if ($filters) {
                foreach ($filters as $key => $value) {
                    // Detect date ... (is Active commission for example ?
                    if (in_array($key, ['startDate', 'endDate'], true)) {
                        if ($key === 'startDate') $query->lt($key, $now->format(\DateTimeInterface::ATOM));
                        if ($key === 'endDate') $query->gt($key, $now->format(\DateTimeInterface::ATOM));
                    } else {
                        $query->andEq($key, $value);
                    }
                }
            } else {
                $query->contains('description', 'Dinah');
            }
            // Active commissions
//            $query
//                ->lt('startDate', $now->format(\DateTimeInterface::ATOM))
//                ->gt('endDate', $now->format(\DateTimeInterface::ATOM));
            $query = $query
//                ->eq('status', '') // /!\ - Status is reserved keyword ??
                ->getQuery(); // Always call this to generate the query to pass to the client
            // then we pass that query to the client.
//            dd($query);
            $result = $this->fetch($query); // <-- appropriated method for this action
            $data = [];
            foreach ($result as $item) {
                $data[] = new Commission(
                    $item['commission_id'],
                    $item['description'],
                    ...
                );
            }
            return $data;
        } catch (Exception $e) {
            throw new CommissionException($e->getMessage());
        }
    }

    public function store(Commission $commission): void
    {
        $query = $this->getQueryBuilder()
            ->batchWriteItems() // Call this method to prepare for dynamodb batchWriteItem action
            ->put((array) $commission)
            ->getQuery()
        ;
        $this->save($query); // <-- appropriated method for this action
    }

    public function find(string $id): ?Commission
    {

        $query = $this->getQueryBuilder()
            ->scan()
            ->eq('commission_id', $id)
            ->getQuery();
        $data = $this->fetch($query); // <-- appropriated method for this action
        if (!$data) {
            return null;
        }
        return new Commission(
            $data['commission_id'],
            $data['description'],
            ...
        );
    }

    public function destroy(array $data): void
    {
        $query = $this->getQueryBuilder()
            ->batchWriteItems()
            ->delete($data)
            ->getQuery()
        ;
        $this->save($query); // <-- appropriated method for this action
    }

    public function edit(array $keys, $values): void
    {
        $query = $this->getUpdateBuilder()
            ->buildUpdateQuery($keys, $values)
            ->getQuery()
        ;
        $this->update($query); // <-- appropriated method for this action
    }
}
```
/!\ - Don't forget to specify the ```tableName```.
Methods here are just basic crud operations usually performed in a repository. The most important thing here is to call appropriated method for each action.

Also, dump your queries to see how they look like before sending them to the client/BaseRepository.

Below are examples of each call (Refer to tests as well for more):

### 3) Examples of query and dumps:
- Get a commission by commission_id
```php
$query = $this->getQueryBuilder()
            ->scan()
            ->eq('commission_id', 'ee9d21c9-117c-5033-a6d2-02c99ee9aacc')
            ->getQuery();
            // $results = $this->fetch($query);
            
dd($query);
===== Dump ====
[
  "TableName" => "commissions-test"
  "FilterExpression" => "(commission_id = :c9b52420)"
  "ExpressionAttributeValues" => [
    ":c9b52420" => [
      "S" => "ee9d21c9-117c-5033-a6d2-02c99ee9aacc"
    ]
  ]
]
```
- Delete 2 commissions & add another one
```php
$query = $this->getQueryBuilder()->batchWriteItems()
            ->delete([
                'commission_id' => '3af3c9c4-929a-5ecd-97d4-860aa79a4304',
                'client_id' => 'igraal',
            ])
            ->delete([
                'commission_id' => '1e991fd1-1780-5fc3-93a0-b7b065f3de2f',
                'client_id' => 'shoop',
            ])
            ->put([
               'commission_id' => '940e0a8b-6c51-36f0-9527-3d75608d2874',
               'client_id' => 'pouch',
               'description' => 'Lorem Ipsum'
            ])
            ->getQuery();
            // $results = $this->save($query);

dd($query);
===== Dump ====
[
  "RequestItems" => [
    "commissions-test" => [
      [
        "DeleteRequest" => [
          "Key" => [
            "commission_id" => [
              "S" => "3af3c9c4-929a-5ecd-97d4-860aa79a4304"
            ]
            "client_id" => [
              "S" => "igraal"
            ]
          ]
        ]
      ]
      [
        "DeleteRequest" => [
          "Key" => [
            "commission_id" => [
              "S" => "1e991fd1-1780-5fc3-93a0-b7b065f3de2f"
            ]
            "client_id" => [
              "S" => "shoop"
            ]
          ]
        ]
      ]
      [
        "PutRequest" => [
          "Item" => [
            "commission_id" => [
              "S" => "940e0a8b-6c51-36f0-9527-3d75608d2874"
            ]
            "description" => [
              "S" => "Lorem Ipsum"
            ]
            "client_id" => [
              "S" => "pouch"
            ]
          ]
        ]
      ]
    ]
  ]
]
```
- Update a commission
```php
$query = $this->getUpdateBuilder($this->tableName)
            ->buildUpdateQuery(
                [
                    'commission_id' => 'ee9d21c9-117c-5033-a6d2-02c99ee9aacc',
                    'client_id' => 'shoop',
                ],
                [
                    "description" => 'Lorem tatum',
                    "status" => "updated", // empty previously
                ]
            )
            ->getQuery();
            // $this->save($query); <-- Void: unless you specify it: See the BaseRepository possible return values

dd($query);
===== Dump ====
[
  "TableName" => "commissions-test"
  "Key" => [
    "commission_id" => [
      "S" => "ee9d21c9-117c-5033-a6d2-02c99ee9aacc"
    ]
    "client_id" => [
      "S" => "shoop"
    ]
  ]
  "UpdateExpression" => "set description = :description, #status = :status"
  "ExpressionAttributeValues" => [
    ":description" => [
      "S" => "Lorem tatum"
    ]
    ":status" => [
      "S" => "updated"
    ]
  ]
  "ExpressionAttributeNames" => [
    "#status" => "status"
  ]
]
```
