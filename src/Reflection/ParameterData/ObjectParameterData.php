<?php
namespace Cognesy\Instructor\Reflection\ParameterData;

use Cognesy\Instructor\Schema\FCObject;
use Cognesy\Instructor\Reflection\ClassData;
use Cognesy\Instructor\Reflection\Enums\PhpType;
use Cognesy\Instructor\Reflection\TypeDefs\TypeDef;
use Exception;
use ReflectionClass;
use ReflectionParameter;

class ObjectParameterData extends ParameterData {
    public ?ClassData $classData = null;

    protected function getParameterData(ReflectionParameter $parameter) : void {
        parent::getParameterData($parameter);
        // Get the class data for the parameter
        $type = $parameter->getType();
        if (!$type) {
            throw new Exception('Parameter type is not defined');
        }
        $class = new ReflectionClass($type->getName());
        $this->classData = new ClassData($class);
    }

    public function toStruct() : FCObject {
        return $this->classData->toStruct($this->name, $this->description);
    }

    public static function asArrayItem(TypeDef $typeDef) : ObjectParameterData {
        $itemType = new ObjectParameterData(null);
        $itemType->name = 'items';
        $itemType->type = PhpType::OBJECT;
        $className = $typeDef->className;
        $class = new ReflectionClass($className);
        $itemType->classData = new ClassData($class);
        return $itemType;
    }
}
