<?php
namespace Cognesy\Instructor\Reflection;

use Cognesy\Instructor\Schema\FCFunction;
use Cognesy\Instructor\Reflection\Factories\ParameterDataFactory;
use Cognesy\Instructor\Reflection\ParameterData\ParameterData;
use ReflectionFunction;

class FunctionData {
    public string $name;
    public string $description;
    /** @var ParameterData[] */
    public array $parameters;

    public function __construct(ReflectionFunction $function) {
        $this->getFunctionData($function);
    }

    public function getFunctionData(ReflectionFunction $function) : void {
        $this->name = $function->getName();
        $this->description = Utils\DescriptionUtils::getFunctionDescription($function);
        $this->parameters = $this->getParameters($function);
    }

    /**
     * @return ParameterData[]
     */
    public function getParameters(ReflectionFunction $function) : array {
        $classProperties = $function->getParameters();
        $parameters = [];
        foreach ($classProperties as $property) {
            $parameters[] = ParameterDataFactory::make($property);
        }
        return $parameters;
    }

    public function toStruct(string $parentName = '', string $parentDescription = '') : FCFunction {
        $fcFunction = new FCFunction();
        $fcFunction->name = $parentName ?: $this->name;
        $fcFunction->description = $parentDescription ?: $this->description;
        foreach($this->parameters as $property) {
            $fcFunction->parameters[] = $property->toStruct();
            if (!$property->nullable) {
                $fcFunction->required[] = $property->name;
            }
        }
        return $fcFunction;
    }
}