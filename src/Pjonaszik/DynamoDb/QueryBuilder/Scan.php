<?php

declare(strict_types=1);

namespace Pjonaszik\DynamoDb\QueryBuilder;

use BadMethodCallException;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\BeginsWithExpression;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\ContainsExpression;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\ExpressionCollection;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\Factory;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\GenericExpression;
use Pjonaszik\DynamoDb\QueryBuilder\Expression\InExpression;
use ReflectionException;

/**
 * Class Scan
 *
 * @method Scan andNotEq(string $key, string|int $value) Add "Or Not Equal" condition to query.
 * @method Scan andEq(string $key, string|int $value) Add "And Equal" condition to query.
 * @method Scan orEq(string $key, string|int $value) Add "Or Equal" condition to query.
 * @method Scan andContains(string $key, string|int $value) Add "And Contains" condition to query.
 * @method Scan orContains(string $key, string|int $value) Add "Or Contains" condition to query.
 * @method Scan andBeginsWith(string $key, string|int $value) Add "And Begins With" condition to query.
 * @method Scan orBeginsWith(string $key, string|int $value) Add "Or Begins With" condition to query.
 * @method Scan andIn(string $key, string|int $value) Add "And In" condition to query.
 * @method Scan orIn(string $key, string[]|int[] $value) Add "Or In" condition to query.
 *
 * @package Pjonaszik\DynamoDb\QueryBuilder
 */
class Scan extends AbstractQueryBuilder
{
    protected const OPERATOR_AND = 'and';
    //    protected const OPERATOR_OR = 'or';

    /**
     * @var array
     */
    protected array $expressionAttributeNames = [];

    /**
     * @var ExpressionCollection
     */
    protected ExpressionCollection $expressions;

    /**
     * Scan constructor.
     *
     * @param string $tableName
     * @param Factory|null $expressionFactory
     */
    public function __construct(
        protected string $tableName,
        protected ?Factory $expressionFactory = null,
    ) {
        parent::__construct($this->tableName);
        $this->expressions = new ExpressionCollection();

        if ($this->expressionFactory === null) {
            $this->expressionFactory = new Factory(
                $this->marshaler
            );
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {

        if (preg_match('/(or|and)(.*)/', $name, $m)) {

            $arguments[] = $m[1];
            return call_user_func_array(
                [$this, lcfirst($m[2])],
                $arguments
            );
        }

        throw new BadMethodCallException(
            sprintf(
                'Bad method call "%s"',
                htmlspecialchars($name)
            )
        );
    }

    /**
     * @return ExpressionCollection
     */
    public function getExpressions(): ExpressionCollection
    {
        return $this->expressions;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function withAttributeNames(array $attributes): static
    {

        $this->expressionAttributeNames = $attributes;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
    public function eq(string $key, mixed $value, string $operator = self::OPERATOR_AND): static
    {

        $this->expressions->addExpression(
            $this->expressionFactory->getExpression(
                [
                    'expression' => 'Eq',
                    'key'        => $key,
                    'value'      => $value,
                    'operator'   => $operator
                ]
            )
        );

        return $this;
    }

    /**
     * @param string $key
     * @param float|int|string $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
  public function gt(string $key, float|int|string $value, string $operator = self::OPERATOR_AND): static
  {

      $this->expressions->addExpression(
          $this->expressionFactory->getExpression(
              [
              'expression' => 'Gt',
              'key'        => $key,
              'value'      => $value,
              'operator'   => $operator
        ]
          )
      );

      return $this;
  }

    /**
     * @param string $key
     * @param float|int|string $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
  public function lt(string $key, float|int|string $value, string $operator = self::OPERATOR_AND): static
  {

      $this->expressions->addExpression(
          $this->expressionFactory->getExpression(
              [
              'expression' => 'Lt',
              'key'        => $key,
              'value'      => $value,
              'operator'   => $operator
        ]
          )
      );

      return $this;
  }

    /**
     * @param string $key
     * @param float|int|string $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
  public function gtEq(string $key, float|int|string $value, string $operator = self::OPERATOR_AND): static
  {

      $this->expressions->addExpression(
          $this->expressionFactory->getExpression(
              [
              'expression' => 'GtEq',
              'key'        => $key,
              'value'      => $value,
              'operator'   => $operator
        ]
          )
      );

      return $this;
  }

    /**
     * @param string $key
     * @param float|int|string $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
  public function ltEq(string $key, float|int|string $value, string $operator = self::OPERATOR_AND): static
  {

      $this->expressions->addExpression(
          $this->expressionFactory->getExpression(
              [
              'expression' => 'LtEq',
              'key'        => $key,
              'value'      => $value,
              'operator'   => $operator
        ]
          )
      );

      return $this;
  }

    /**
     * @param string $key
     * @param float|int|string $value
     * @param string $operator
     *
     * @return $this
     * @throws QueryBuilderException|ReflectionException
     */
    public function notEq(string $key, float|int|string $value, string $operator = self::OPERATOR_AND): static
    {

        $this->expressions->addExpression(
            $this->expressionFactory->getExpression(
                [
                    'expression' => 'NotEq',
                    'key'        => $key,
                    'value'      => $value,
                    'operator'   => $operator
                ]
            )
        );

        return $this;
    }

    /**
     * @param string $key
     * @param int|float|string  $value
     * @param string $operator
     *
     * @return $this
     */
    public function contains(string $key, int|float|string $value, string $operator = self::OPERATOR_AND): static
    {

        $expression = new ContainsExpression($this->marshaler);
        $expression->setKey($key)
            ->setValue($value)
            ->setOperator($operator);

        $this->expressions->addExpression($expression);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param string $operator
     *
     * @return $this
     */
    public function beginsWith(string $key, mixed $value, string $operator = self::OPERATOR_AND): static
    {

        $expression = new BeginsWithExpression($this->marshaler);
        $expression->setKey($key)
            ->setValue($value)
            ->setOperator($operator);

        $this->expressions->addExpression($expression);

        return $this;
    }

    /**
     * @param string $key
     * @param array  $values
     * @param string $operator
     *
     * @return $this
     */
    public function in(string $key, array $values, string $operator = self::OPERATOR_AND): static
    {

        $expression = new InExpression($this->marshaler);
        $expression->setKey($key)
            ->setValue($values)
            ->setOperator($operator);

        $this->expressions->addExpression($expression);

        return $this;
    }

    /**
     * @param Scan   $qb
     * @param string $operator
     *
     * @return $this
     */
    public function subQuery(Scan $qb, string $operator = self::OPERATOR_AND): static
    {
        $this->expressions->addExpression(
            new GenericExpression(
                $qb->getExpressions()->getExpressionString(),
                $qb->getExpressions()->getValue(),
                $operator
            )
        );

        return $this;
    }

    /**
     * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_Query.html
     * @param array|null $queryRequestSyntax
     * @return array
     */
    public function getQuery(?array $queryRequestSyntax = null): array
    {

        $query = [
            'TableName'                 => $this->tableName,
            'FilterExpression'          => $this->expressions->getExpressionString(),
            'ExpressionAttributeValues' => $this->expressions->getValue()
        ];

        if (count($this->expressionAttributeNames) > 0) {
            $query += [
                'ExpressionAttributeNames' => $this->expressionAttributeNames
            ];
        }

        // IndexName
        if ($this->indexName) {
            $query += ['IndexName' => $this->indexName];
        }

        if ($queryRequestSyntax) {
            $query += $queryRequestSyntax;
        }

        return $query;
    }
}
