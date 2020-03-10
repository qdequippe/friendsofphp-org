<?php

declare(strict_types=1);

namespace Fop\Hydrator;

use DateTimeInterface;
use Fop\ValueObject\Location;
use Location\Coordinate;
use Nette\Utils\DateTime;
use ReflectionClass;
use ReflectionParameter;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class ArrayToValueObjectHydrator
{
    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    public function __construct(StringFormatConverter $stringFormatConverter)
    {
        $this->stringFormatConverter = $stringFormatConverter;
    }

    /**
     * @param class-string $class
     * @param mixed[] $data
     */
    public function hydrateArrayToValueObject(array $data, string $class): object
    {
        $parameterReflections = $this->getConstructorParameterReflections($class);

        $arguments = [];
        foreach ($parameterReflections as $parameterReflection) {
            $key = $this->stringFormatConverter->camelCaseToUnderscore($parameterReflection->name);

            $value = $data[$key] ?? null;
            if ($this->isDateTimeParameter($parameterReflection) && $value !== null) {
                $value = DateTime::from($value);
            }

            if ($this->isLocationParameter($parameterReflection)) {
                $coordinate = new Coordinate($data['latitude'], $data['longitude']);
                $value = new Location($data['city'], $data['country'], $coordinate);
            }

            $arguments[] = $value;
        }

        return new $class(...$arguments);
    }

    /**
     * @param class-string $class
     * @param mixed[][] $datas
     * @return object[]
     */
    public function hydrateArraysToValueObject(array $datas, string $class): array
    {
        $objects = [];
        foreach ($datas as $data) {
            $objects[] = $this->hydrateArrayToValueObject($data, $class);
        }

        return $objects;
    }

    /**
     * @param class-string $class
     * @return ReflectionParameter[]
     */
    private function getConstructorParameterReflections(string $class): array
    {
        $classReflection = new ReflectionClass($class);

        $constructorMethod = $classReflection->getConstructor();
        if ($constructorMethod === null) {
            return [];
        }

        return $constructorMethod->getParameters();
    }

    private function isDateTimeParameter(ReflectionParameter $reflectionParameter): bool
    {
        return $this->isParameterOfType($reflectionParameter, DateTimeInterface::class);
    }

    private function isLocationParameter(ReflectionParameter $reflectionParameter): bool
    {
        return $this->isParameterOfType($reflectionParameter, Location::class);
    }

    private function isParameterOfType(ReflectionParameter $reflectionParameter, string $type): bool
    {
        if ($reflectionParameter->hasType() === false) {
            return false;
        }

        $parameterTypeName = $reflectionParameter->getType()->getName();

        return is_a($parameterTypeName, $type, true);
    }
}
