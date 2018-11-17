<?php

namespace Pgraph\Tests\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use PHPUnit\Framework\TestCase;
use Pgraph\GraphQL\SchemaFactory;
use GraphQL\Type\Definition\ObjectType;


class SchemaFactoryTest extends TestCase
{
    use GraphQLTestCaseTrait;

    /**
     * Test a creation of a schema factory instance.
     *
     * @return SchemaFactory
     */
    public function testCreateInstance()
    {
        $factory = new SchemaFactory($this->registry());
        $this->assertInstanceOf(SchemaFactory::class, $factory);

        return $factory;
    }

    /**
     * @depends testCreateInstance
     *
     * @param SchemaFactory $factory
     * @return void
     */
    public function testCreateFromSchemaConfig(SchemaFactory $factory)
    {
        $config = new SchemaConfig();
        $config->setTypeLoader($this->registry())
               ->setQuery($queryType = $this->mockQueryType()); 

        $schema = $factory->createFromSchemaConfig($config);
        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertSame($config, $schema->getConfig());

        $queryTypeInSchema = $schema->getQueryType();
        $this->assertSame($queryType, $queryTypeInSchema);
    }

    /**
     * @depends testCreateInstance
     *
     * @param SchemaFactory $factory
     * @return void
     */
    public function testCreateFromParameters(SchemaFactory $factory)
    {
        $queryType = $this->mockQueryType();
        $schema = $factory->create($queryType, null, [], [], $this->registry());

        $this->assertSame($queryType, $schema->getQueryType());
        $this->assertInternalType('null', $schema->getMutationType());
    }

    /**
     * Mocks a simple query object type for testing
     *
     * @return ObjectType
     */
    protected function mockQueryType()
    {
        return new ObjectType([
            'name' => 'Query',
            'fields' => [
                'stub' => [
                    'type' => $this->registry()->int(),
                    'resolve' => function () {
                        return 999;
                    }
                ]
            ]
        ]);
    }
}
