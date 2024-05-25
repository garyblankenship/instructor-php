<?php
namespace Tests\Feature\Extras;

use Cognesy\Instructor\Extras\Field\Field;
use Cognesy\Instructor\Extras\Signature\Attributes\InputField;
use Cognesy\Instructor\Extras\Signature\Attributes\OutputField;
use Cognesy\Instructor\Extras\Signature\Signature;
use Cognesy\Instructor\Extras\Structure\Structure;
use Cognesy\Instructor\Schema\Attributes\Description;
use Symfony\Component\Serializer\Attribute\Ignore;

it('creates signature from string', function () {
    $signature = Signature::fromString('name:string (description) -> age:int (description)');
    expect($signature->toString())->toBe('name:string (description) -> age:int (description)');
});

it('creates signature from structure', function () {
    $structure = Structure::define('test', [
        Field::structure('inputs', [
            Field::string('name', 'name description'),
        ]),
        Field::structure('outputs', [
            Field::int('age', 'age description'),
        ]),
    ]);
    $signature = Signature::fromStructure($structure);
    expect($signature->toString())->toBe('name:string (name description) -> age:int (age description)');
});

it('creates signature from separate structures', function () {
    $structure1 = Structure::define('inputs', [
        Field::string('name', 'name description'),
    ]);
    $structure2 = Structure::define('outputs', [
        Field::int('age', 'age description'),
    ]);
    $signature = Signature::fromStructures($structure1, $structure2);
    expect($signature->toString())->toBe('name:string (name description) -> age:int (age description)');
});

it('creates signature from classes', function () {
    class Input {
        public string $name;
    }
    class Output {
        public int $age;
    }
    $signature = Signature::fromClasses(Input::class, Output::class);
    expect($signature->toString())->toBe('name:string -> age:int');
});

it('creates signature from class metadata', function () {
    #[Description('Test description')]
    class TestSignature {
        #[InputField]
        public string $stringProperty;
        #[OutputField]
        public int $intProperty;
        #[Ignore]
        public bool $ignoredProperty;
    }
    $signature = Signature::fromClassMetadata(TestSignature::class);
    expect($signature->toString())->toBe('stringProperty:string -> intProperty:int');
});