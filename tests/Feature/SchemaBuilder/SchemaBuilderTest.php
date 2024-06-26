<?php
namespace Tests;

use Cognesy\Instructor\Schema\Data\Schema\ObjectSchema;
use Cognesy\Instructor\Schema\Utils\SchemaBuilder;

it('creates Schema object from JSON Schema array - scalar props', function ($jsonSchema) {
    $schema = (new SchemaBuilder)->fromArray($jsonSchema);
    expect($schema)->toBeInstanceOf(ObjectSchema::class);

    expect($schema->properties['stringProperty']->name)->toBe('stringProperty');
    expect($schema->properties['stringProperty']->description)->toBe('String property');
    expect($schema->properties['stringProperty']->type->type)->toBe('string');
    expect($schema->properties['integerProperty']->name)->toBe('integerProperty');
    expect($schema->properties['integerProperty']->description)->toBe('Integer property');
    expect($schema->properties['integerProperty']->type->type)->toBe('int');
    expect($schema->properties['boolProperty']->name)->toBe('boolProperty');
    expect($schema->properties['boolProperty']->description)->toBe('Boolean property');
    expect($schema->properties['boolProperty']->type->type)->toBe('bool');
    expect($schema->properties['floatProperty']->name)->toBe('floatProperty');
    expect($schema->properties['floatProperty']->description)->toBe('Float property');
    expect($schema->properties['floatProperty']->type->type)->toBe('float');

    expect($schema->required)->toBe(['stringProperty', 'integerProperty', 'boolProperty', 'floatProperty', 'enumProperty', 'objectProperty', 'arrayProperty', 'arrayObjectProperty', 'arrayEnumProperty']);
})->with('schema_builder_json');

it('creates Schema object from JSON Schema array - enum props', function ($jsonSchema) {
    $schema = (new SchemaBuilder)->fromArray($jsonSchema);
    expect($schema)->toBeInstanceOf(ObjectSchema::class);

    expect($schema->properties['enumProperty']->name)->toBe('enumProperty');
    expect($schema->properties['enumProperty']->description)->toBe('Enum property');
    expect($schema->properties['enumProperty']->type->type)->toBe('enum');
    expect($schema->properties['enumProperty']->type->class)->toBe('Tests\Examples\SchemaBuilder\TestEnum');
    expect($schema->properties['enumProperty']->type->enumType)->toBe('string');
    expect($schema->properties['enumProperty']->type->enumValues)->toBe(['one', 'two', 'three']);

    expect($schema->required)->toBe(['stringProperty', 'integerProperty', 'boolProperty', 'floatProperty', 'enumProperty', 'objectProperty', 'arrayProperty', 'arrayObjectProperty', 'arrayEnumProperty']);
})->with('schema_builder_json');


it('creates Schema object from JSON Schema array - object props', function ($jsonSchema) {
    $schema = (new SchemaBuilder)->fromArray($jsonSchema);
    expect($schema)->toBeInstanceOf(ObjectSchema::class);

    expect($schema->properties['objectProperty']->name)->toBe('objectProperty');
    expect($schema->properties['objectProperty']->description)->toBe('Object property');
    expect($schema->properties['objectProperty']->type->type)->toBe('object');
    expect($schema->properties['objectProperty']->type->class)->toBe('Tests\Examples\SchemaBuilder\TestNestedObject');
    expect($schema->properties['objectProperty']->properties['nestedStringProperty']->name)->toBe('nestedStringProperty');
    expect($schema->properties['objectProperty']->properties['nestedStringProperty']->type->type)->toBe('string');
    expect($schema->properties['objectProperty']->properties['nestedObjectProperty']->name)->toBe('nestedObjectProperty');
    expect($schema->properties['objectProperty']->properties['nestedObjectProperty']->type->type)->toBe('object');
    expect($schema->properties['objectProperty']->properties['nestedObjectProperty']->type->class)->toBe('Tests\Examples\SchemaBuilder\TestDoubleNestedObject');
    expect($schema->properties['objectProperty']->properties['nestedObjectProperty']->properties['nestedNestedStringProperty']->name)->toBe('nestedNestedStringProperty');
    expect($schema->properties['objectProperty']->properties['nestedObjectProperty']->properties['nestedNestedStringProperty']->type->type)->toBe('string');

    expect($schema->required)->toBe(['stringProperty', 'integerProperty', 'boolProperty', 'floatProperty', 'enumProperty', 'objectProperty', 'arrayProperty', 'arrayObjectProperty', 'arrayEnumProperty']);
})->with('schema_builder_json');


it('creates Schema object from JSON Schema array - array props', function ($jsonSchema) {
    $schema = (new SchemaBuilder)->fromArray($jsonSchema);
    expect($schema)->toBeInstanceOf(ObjectSchema::class);

    expect($schema->properties['arrayProperty']->name)->toBe('arrayProperty');
    expect($schema->properties['arrayProperty']->description)->toBe('Array property');
    expect($schema->properties['arrayProperty']->type->type)->toBe('array');
    expect($schema->properties['arrayProperty']->nestedItemSchema->type->type)->toBe('string');
    expect($schema->properties['arrayProperty']->nestedItemSchema->type->class)->toBe(null);
    expect($schema->properties['arrayObjectProperty']->name)->toBe('arrayObjectProperty');
    expect($schema->properties['arrayObjectProperty']->description)->toBe('Array of objects property');
    expect($schema->properties['arrayObjectProperty']->type->type)->toBe('array');
    expect($schema->properties['arrayObjectProperty']->nestedItemSchema->type->type)->toBe('object');
    expect($schema->properties['arrayObjectProperty']->nestedItemSchema->type->class)->toBe('Tests\Examples\SchemaBuilder\TestNestedObject');
    expect($schema->properties['arrayObjectProperty']->nestedItemSchema->properties['nestedStringProperty']->name)->toBe('nestedStringProperty');
    expect($schema->properties['arrayObjectProperty']->nestedItemSchema->properties['nestedStringProperty']->type->type)->toBe('string');
    expect($schema->properties['arrayEnumProperty']->name)->toBe('arrayEnumProperty');
    expect($schema->properties['arrayEnumProperty']->description)->toBe('Array of enum property');
    expect($schema->properties['arrayEnumProperty']->type->type)->toBe('array');
    expect($schema->properties['arrayEnumProperty']->nestedItemSchema->type->type)->toBe('enum');
    expect($schema->properties['arrayEnumProperty']->nestedItemSchema->type->class)->toBe('Tests\Examples\SchemaBuilder\TestEnum');
    expect($schema->properties['arrayEnumProperty']->nestedItemSchema->type->enumType)->toBe('string');
    expect($schema->properties['arrayEnumProperty']->nestedItemSchema->type->enumValues)->toBe(['one', 'two', 'three']);

    expect($schema->required)->toBe(['stringProperty', 'integerProperty', 'boolProperty', 'floatProperty', 'enumProperty', 'objectProperty', 'arrayProperty', 'arrayObjectProperty', 'arrayEnumProperty']);
})->with('schema_builder_json');
