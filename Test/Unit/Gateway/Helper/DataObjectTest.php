<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Helper;

use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use TNW\AuthorizeCim\Gateway\Helper\DataObject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test SubjectReader
 */
class DataObjectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectFactory|MockObject
     */
    private $objectFactory;

    /**
     * @var TypeProcessor|MockObject
     */
    private $typeProcessor;

    /**
     * @var MethodsMap|MockObject
     */
    private $methodsMap;

    /**
     * @var DataObject
     */
    private $dataObject;

    protected function setUp()
    {
        $this->objectFactory = $this->getMockBuilder(ObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typeProcessor = $this->getMockBuilder(TypeProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->methodsMap = $this->getMockBuilder(MethodsMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObject = new DataObject(
            $this->objectFactory,
            $this->typeProcessor,
            $this->methodsMap
        );
    }

    /**
     * @covers DataObject::populateWithArray()
     */
    public function testPopulateWithArray()
    {
        $data = [
            'string' => 'string',
            'float' => 23.43,
            'array' => [
                [
                    'string' => 'string',
                    'float' => 23.43,
                ]
            ],
            'object' => [
                'string' => 'string',
                'float' => 23.43,
            ],
        ];

        $dataObject = new class {
            public $string;
            public $float;
            public $array;
            public $object;

            public function setString($string)
            {
                $this->string = $string;
            }

            public function setFloat($float)
            {
                $this->float = $float;
            }

            public function setArray($array)
            {
                $this->array = $array;
            }

            public function setObject($object)
            {
                $this->object = $object;
            }
        };

        $otherType = clone $dataObject;
        $otherType->string = 'string';
        $otherType->float = 23.43;

        $otherTypeTwo = clone $dataObject;
        $otherTypeTwo->string = 'string';
        $otherTypeTwo->float = 23.43;

        $expected = clone $dataObject;
        $expected->string = 'string';
        $expected->float = 23.43;
        $expected->array = [$otherType];
        $expected->object = $otherTypeTwo;

        $this->methodsMap->method('getMethodReturnType')
            ->willReturnMap([
                [\get_class($dataObject), 'getString', 'string'],
                [\get_class($dataObject), 'getFloat', 'float'],
                [\get_class($dataObject), 'getArray', 'OtherTypeClass[]'],
                [\get_class($dataObject), 'getObject', 'OtherTypeTwoClass'],
            ]);

        $this->typeProcessor->method('isTypeSimple')
            ->willReturnMap([
                ['string', true],
                ['float', true],
                ['OtherTypeClass[]', false],
                ['OtherTypeTwoClass', false],
            ]);

        $this->typeProcessor->method('isArrayType')
            ->willReturnMap([
                ['OtherTypeClass[]', true],
                ['OtherTypeTwoClass', false],
            ]);

        $this->typeProcessor->method('getArrayItemType')
            ->willReturnMap([
                ['OtherTypeClass[]', 'OtherTypeClass'],
            ]);

        $this->objectFactory->method('create')
            ->willReturnMap([
                ['OtherTypeClass', [], clone $dataObject],
                ['OtherTypeTwoClass', [], clone $dataObject],
            ]);

        $this->dataObject->populateWithArray($dataObject, $data);

        static::assertEquals($expected, $dataObject);
    }

    /**
     * @covers DataObject::populateWithObject()
     */
    public function testPopulateWithObject()
    {
        $expected = [
            'string' => 'string',
            'float' => 23.43,
            'array' => [
                'string' => 'string',
                'object' => [
                    'string' => 'string'
                ]
            ],
            'object' => [
                'string' => 'string'
            ],
        ];

        $dataObject = new class {
            public $subDataObject;

            public function getString()
            {
                return 'string';
            }

            public function getFloat()
            {
                return 23.43;
            }

            public function getNull()
            {
                return null;
            }

            public function getArray()
            {
                return [
                    'null' => null,
                    'string' => 'string',
                    'object' => $this->subDataObject
                ];
            }

            public function getObject()
            {
                return $this->subDataObject;
            }
        };

        $dataObject->subDataObject = new class {
            public function getString()
            {
                return 'string';
            }

            public function getNull()
            {
                return null;
            }
        };

        $actual = [];
        $this->dataObject->populateWithObject($actual, $dataObject);

        static::assertEquals($expected, $actual);
    }
}
