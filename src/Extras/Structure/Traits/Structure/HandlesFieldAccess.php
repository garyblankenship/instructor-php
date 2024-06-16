<?php
namespace Cognesy\Instructor\Extras\Structure\Traits\Structure;

use Cognesy\Instructor\Extras\Structure\Field;
use Cognesy\Instructor\Schema\Data\TypeDetails;

trait HandlesFieldAccess
{
    /** @var \Cognesy\Instructor\Extras\Structure\Field[] */
    protected array $fields = [];

    public function has(string $field) : bool {
        return isset($this->fields[$field]);
    }

    public function actsAsScalar() : bool {
        return count($this->fields) === 1;
    }

    public function field(string $name) : Field {
        if (!$this->has($name)) {
            throw new \Exception("Field `$name` not found in structure.");
        }
        return $this->fields[$name];
    }

    /** @return \Cognesy\Instructor\Extras\Structure\Field[] */
    public function fields() : array {
        return $this->fields;
    }

    /** @return string[] */
    public function fieldNames() : array {
        return array_keys($this->fields);
    }

    /** @return mixed[] */
    public function fieldValues() : array {
        $args = [];
        foreach ($this->fields as $field) {
            $args[$field->name()] = $field->get();
        }
        return $args;
    }

    public function get(string $field) : mixed {
        return $this->field($field)->get();
    }

    public function set(string $field, mixed $value) : void {
        $this->field($field)->set($value);
    }

    public function typeDetails(string $field) : TypeDetails {
        return $this->field($field)->typeDetails();
    }

    public function count() : int {
        return count($this->fields);
    }

    public function asScalar() : mixed {
        if (!$this->actsAsScalar()) {
            throw new \Exception("Cannot convert structure to scalar - it has more than one field.");
        }
        return $this->get($this->firstKey());
    }

    private function firstKey() : string {
        return array_key_first($this->fields);
    }

    public function __get(string $field) : mixed {
// TODO: this feels hacky, but it is useful - let's figure it out later
// for structures with a single field return it for whatever field is requested
// it is hacky indeed, but it helps to use structures with signatures - we basically
// treat structure as a scalar
//        if ($this->actsAsScalar()) {
//            return $this->get($this->scalarKey());
//        }
        return $this->get($field);
    }

    public function __set(string $field, mixed $value) {
        $this->set($field, $value);
    }

    public function __isset(string $field) : bool {
        return $this->has($field);
    }
}