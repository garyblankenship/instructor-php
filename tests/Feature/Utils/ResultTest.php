<?php

use Cognesy\Instructor\Utils\Failure;
use Cognesy\Instructor\Utils\Result;
use Cognesy\Instructor\Utils\Success;

test('it creates a success result', function () {
    $result = Result::success(42);

    expect($result)->toBeInstanceOf(Success::class)
        ->and($result->isSuccess())->toBeTrue()
        ->and($result->isFailure())->toBeFalse()
        ->and($result->value())->toBe(42);
});

test('it creates a failure result', function () {
    $result = Result::failure('An error occurred');

    expect($result)->toBeInstanceOf(Failure::class)
        ->and($result->isSuccess())->toBeFalse()
        ->and($result->isFailure())->toBeTrue()
        ->and($result->errorValue())->toBe('An error occurred')
        ->and($result->errorMessage())->toBe('An error occurred');
});

test('it applies a success function', function () {
    $result = Result::success(21);
    $transformedResult = $result->apply(fn ($value) => $value * 2);

    expect($transformedResult)->toBeInstanceOf(Success::class)
        ->and($transformedResult->isSuccess())->toBeTrue()
        ->and($transformedResult->value())->toBe(42);
});

test('it applies a failure function', function () {
    $result = Result::failure('An error occurred');
    $transformedResult = $result->apply(fn ($value) => $value * 2);

    expect($transformedResult)->toBeInstanceOf(Failure::class)
        ->and($transformedResult->isFailure())->toBeTrue()
        ->and($transformedResult->errorValue())->toBe('An error occurred');
});

test('it recovers from a success result', function () {
    $result = Result::success(42);
    $transformedResult = $result->recover(fn ($error) => "Recovered from error: $error");

    expect($transformedResult)->toBeInstanceOf(Success::class)
        ->and($transformedResult->isSuccess())->toBeTrue()
        ->and($transformedResult->value())->toBe(42);
});

test('it recovers from a failure result', function () {
    $result = Result::failure('An error occurred');
    $transformedResult = $result->recover(fn ($error) => "Recovered from error: $error");

    expect($transformedResult)->toBeInstanceOf(Success::class)
        ->and($transformedResult->isSuccess())->toBeTrue()
        ->and($transformedResult->value())->toBe('Recovered from error: An error occurred');
});

test('it tries a success function', function () {
    $result = Result::try(fn () => 42);

    expect($result)->toBeInstanceOf(Success::class)
        ->and($result->isSuccess())->toBeTrue()
        ->and($result->value())->toBe(42);
});

test('it tries a failure function', function () {
    $result = Result::try(fn () => throw new Exception('An error occurred'));

    expect($result)->toBeInstanceOf(Failure::class)
        ->and($result->isFailure())->toBeTrue()
        ->and($result->errorValue())->toBeInstanceOf(Exception::class)
        ->and($result->errorMessage())->toBe('An error occurred');
});
