<?php

namespace Tests\Helpers;

trait ReflectionHelper
{
    private function getReflectionObject($object): \ReflectionObject
    {
        return new \ReflectionObject($object);
    }

    private function setPrivateProperty($object, string $propertyName, $value): self
    {
        $property = $this->getReflectionObject($object)->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        return $this;
    }

    private function getPrivateProperty($object, string $propertyName): mixed
    {
        $property = $this->getReflectionObject($object)->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function runPrivateMethod($object, string $methodName, array $arguments = []): mixed
    {
        $method = $this->getReflectionObject($object)->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}