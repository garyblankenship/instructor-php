<?php
namespace Cognesy\Instructor\Reflection;

use Cognesy\Instructor\Schema\FCObject;
use Cognesy\Instructor\Reflection\Factories\PropertyDataFactory;
use Cognesy\Instructor\Reflection\PropertyData\PropertyData;
use ReflectionClass;

class ClassData {
    public string $name = '';
    public string $description = '';
    /** @var PropertyData[] */
    public array $properties = [];

    public function __construct(ReflectionClass $class) {
        $this->getClassData($class);
    }

    private function getClassData(ReflectionClass $class) : void {
        $this->name = $class->getName();
        $this->description = Utils\DescriptionUtils::getClassDescription($class);
        $this->properties = $this->getProperties($class);
    }

    /**
     * @return PropertyData[]
     */
    private function getProperties(ReflectionClass $class) : array {
        $classProperties = $class->getProperties();
        $properties = [];
        foreach ($classProperties as $property) {
            $properties[] = PropertyDataFactory::make($property);
        }
        return $properties;
    }

    public function toStruct(string $parentName = '', string $parentDescription = '') : FCObject {
        $fcObject = new FCObject();
        $fcObject->name = $parentName;
        $fcObject->description = '';
        if ($parentDescription !== '') {
            $fcObject->description .= $parentDescription . "\n";
        }
        $fcObject->description .= $this->description;
        foreach($this->properties as $property) {
            $fcObject->properties[] = $property->toStruct();
            if (!$property->isNullable) {
                $fcObject->required[] = $property->name;
            }
        }
        return $fcObject;
    }
}
