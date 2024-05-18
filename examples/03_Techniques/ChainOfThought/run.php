# Chain of Thought

This approach to "chain of thought" improves data quality, by eliciting LLM reasoning to
self-explain approach to generating the response.

> With Instructor you can achieve a 'modular' CoT, where multiple explanations
> can be generated by LLM for different parts of the response, driving a more
> granular control and improvement of the response.


```php
<?php
$loader = require 'vendor/autoload.php';
$loader->add('Cognesy\\Instructor\\', __DIR__.'../../src/');

use Cognesy\Instructor\Instructor;

class Employee {
    /** Think step by step to determine the correct year of employment. */
    public string $chainOfThought;
    public int $yearOfEmployment;
}

$text = 'He was working here for 5 years. Now, in 2019, he is a manager.';

$employee = (new Instructor)->respond(
    [['role' => 'user', 'content' => $text]],
    Employee::class
);

dump($employee);

assert($employee->yearOfEmployment === 2014);
?>
```
