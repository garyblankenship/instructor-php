<?php

namespace Cognesy\Instructor\Extras\Structure\Traits;

use Cognesy\Instructor\Extras\Structure\Field;
use Cognesy\Instructor\Extras\Structure\Structure;
use Cognesy\Instructor\Schema\Data\TypeDetails;
use Cognesy\Instructor\Schema\Factories\TypeDetailsFactory;

trait HandlesFieldDefinitions
{
    private TypeDetailsFactory $typeDetailsFactory;

    static public function int(string $name, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->scalarType(TypeDetails::PHP_INT);
        return new Field($name, $description, $type);
    }

    static public function string(string $name, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->scalarType(TypeDetails::PHP_STRING);
        return new Field($name, $description, $type);
    }

    static public function float(string $name, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->scalarType(TypeDetails::PHP_FLOAT);
        return new Field($name, $description, $type);
    }

    static public function bool(string $name, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->scalarType(TypeDetails::PHP_BOOL);
        return new Field($name, $description, $type);
    }

    static public function enum(string $name, string $enumClass, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->enumType($enumClass);
        return new Field($name, $description, $type);
    }

    static public function object(string $name, string $class, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->objectType($class);
        return new Field($name, $description, $type);
    }

    static public function structure(string $name, array|callable $fields, string $description = '') : self {
        $factory = new TypeDetailsFactory();
        $type = $factory->objectType(Structure::class);
        $result = new Field($name, $description, $type);
        $result->value = Structure::define($name, $fields, $description);
        return $result;
    }
}