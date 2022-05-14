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
}