<?php
/**
 * Copyright © 2018 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */
namespace TNW\AuthorizeCim\Gateway\Helper;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Reflection\TypeProcessor;

class DataObject
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    /**
     * @param ObjectFactory $objectFactory
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMapProcessor
     */
    public function __construct(
        ObjectFactory $objectFactory,
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMapProcessor
    ) {
        $this->objectFactory = $objectFactory;
        $this->typeProcessor = $typeProcessor;
        $this->methodsMapProcessor = $methodsMapProcessor;
    }

    /**
     * Populate data object using data in array format.
     *
     * @param $dataObject
     * @param array $data
     */
    public function populateWithArray($dataObject, array $data)
    {
        $dataObjectMethods = get_class_methods(get_class($dataObject));
        foreach ($data as $key => $value) {
            /* First, verify is there any setter for the key on the Service Data Object */
            $camelCaseKey = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $methodNames = array_intersect(['set' . $camelCaseKey], $dataObjectMethods);

            if (empty($methodNames)) {
                continue;
            }

            $methodName = array_values($methodNames)[0];
            $returnType = $this->methodsMapProcessor->getMethodReturnType(get_class($dataObject), "get{$camelCaseKey}");

            switch (true) {
                case $this->typeProcessor->isTypeSimple($returnType):
                    $dataObject->$methodName($value);
                    break;

                case $this->typeProcessor->isArrayType($returnType):
                    $type = $this->typeProcessor->getArrayItemType($returnType);
                    $objects = [];
                    foreach ($value as $arrayElementData) {
                        $object = $this->objectFactory->create($type, []);
                        $this->populateWithArray($object, $arrayElementData);
                        $objects[] = $object;
                    }

                    $dataObject->$methodName($objects);
                    break;

                default:
                    $object = $this->objectFactory->create($returnType, []);
                    $this->populateWithArray($object, $value);

                    $dataObject->$methodName($object);
                    break;
            }
        }
    }
}
