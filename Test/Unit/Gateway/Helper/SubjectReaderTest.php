<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Test\Unit\Gateway\Helper;

use InvalidArgumentException;
use TNW\AuthorizeCim\Gateway\Helper\SubjectReader;

/**
 * Test SubjectReader
 */
class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readTransaction
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Response object does not exist
     */
    public function testReadTransactionWithException()
    {
        $this->subjectReader->readTransaction([]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readTransaction
     */
    public function testReadTransaction()
    {
        $object = new \StdClass;
        static::assertEquals($object, $this->subjectReader->readTransaction(['object' => $object]));
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Response does not exist
     */
    public function testReadResponseObjectWithException()
    {
        $this->subjectReader->readResponseObject([]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Response object does not exist.
     */
    public function testReadResponseObjectWithExceptionObject()
    {
        $this->subjectReader->readResponseObject(['response' => []]);
    }

    /**
     * @covers \TNW\AuthorizeCim\Gateway\Helper\SubjectReader::readResponseObject
     */
    public function testReadResponseObject()
    {
        $object = new \StdClass;
        static::assertEquals($object, $this->subjectReader->readResponseObject(['response' => ['object' => $object]]));
    }
}
