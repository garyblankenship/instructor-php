# General Tips for Prompt Engineering

The overarching theme of using Instructor for function calling is to make the models self-descriptive, modular, and flexible, while maintaining data integrity and ease of use.

- **Modularity**: Design self-contained components for reuse.
- **Self-Description**: Use PHPDoc comments or #[Description('')] attribute for clear field descriptions.
- **Optionality**: Use PHP's nullable types (e.g. ?int) for optional fields and set sensible defaults.
- **Standardization**: Employ enumerations for fields with a fixed set of values; include a fallback option.
- **Dynamic Data**: Use key-value pairs for arbitrary properties and limit list lengths.
- **Entity Relationships**: Define explicit identifiers and relationship fields.
- **Contextual Logic**: Optionally add a "chain of thought" field in reusable components for extra context.


## Utilize Nullable Attribute

!!! example

    Run example via CLI: ```php ./examples/OptionalFields/run.php```

Use PHP's nullable types by prefixing type name with question mark (?) and set a default value to prevent undesired defaults like empty strings.

```php
<?php

class UserDetail
{
    public int $age;
    public string $name;
    public ?Role $role = null; 
}
```


## Handling Errors Within Function Calls

You can create a wrapper class to hold either the result of an operation or an error message. This allows you to remain within a function call even if an error occurs, facilitating better error handling without breaking the code flow.

```php
<?php

class UserDetail
{
    public int $age;
    public string $name;
    public ?string $role = null;
}

class MaybeUser
{
    public ?UserDetail $result = null;
    public ?string $errorMessage = '';
    public bool $error = false;

    public function get(): ?UserDetail
    {
        return $this->error ? null : $this->result;
    }
}
```

With the `MaybeUser` class, you can either receive a `UserDetail` object in result or get an error message in 'errorMessage'.

> Original Instructor implementation in Python provides utility class Maybe making this pattern even easier. Such mechanism is not yet available in PHP version of Instructor.


## Tips for Enumerations

To prevent data misalignment, use Enums for standardized fields. Always include an "Other" option as a fallback so the model can signal uncertainty.

```php
<?php

use Cognesy\Instructor\Attributes\Description;

enum Role : string {
    case Principal = 'principal'
    case Teacher = 'teacher'
    case Student = 'student'
    case Other = 'other'
}

class UserDetail
{
    public int $age;
    public string $name;
    #[Description("Correctly assign one of the predefined roles to the user.")]
    public Role $role;
}
```

If you'd like to improve LLM inference performance, try reiterating the requirements in the field descriptions or in the docstrings.


## Reiterate Long Instructions

For complex attributes, it helps to reiterate the instructions in the field's description.

```php
<?php

use Cognesy\Instructor\Attributes\Description;

#[Description("Extract the role based on the following rules: <your rules go here>")]
class Role
{
    #[Description("Restate the instructions and rules to correctly determine the title.")]
    public string $instructions;
    public string $title;
}

class UserDetail
{
    public int $age;
    public string $name;
    public Role $role;
}
```

## Handle Arbitrary Properties

When you need to extract undefined attributes, use a list of key-value pairs.

```php
<?php

class Property
{
    public string $key;
    public string $value;
}

class UserDetail
{
    public int $age
    public string $name;
    /** @var Property[] */
    #[Description("Extract any other properties that might be relevant.")]
    public array $properties;
}
```


## Limiting the Length of Lists

When dealing with lists of attributes, especially arbitrary properties, it's crucial to manage the length. You can use prompting and enumeration to limit the list length, ensuring a manageable set of properties.

```php
<?php

use Cognesy\Instructor\Attributes\Description;

class Property
{
    #[Description("Monotonically increasing ID")]
    public string $index; 
    public int $age;
    public string $value;
}

class UserDetail
{
    public int $age
    public string $name;
    /** @var Property[] */
    #[Description("Numbered list of arbitrary extracted properties, should be less than 6")]
    public array $properties;
}
```


## Advanced Arbitrary Properties

For multiple users, aim to use consistent key names when extracting properties.

```php
<?php

use Cognesy\Instructor\Attributes\Description;

class UserDetail {
    public int $id;
    public string $key;
    public string $name;
}

class UserDetails
{
    #[Description("Extract information for multiple users. Use consistent key names for properties across users.")]
    /** @var UserDetail[] */
    public array $users;
}
```


## Defining Relationships Between Entities

In cases where relationships exist between entities, it's vital to define them explicitly in the model. The following example demonstrates how to define relationships between users by incorporating an id and a friends field:

```php
<?php

use Cognesy\Instructor\Attributes\Description;

class UserDetail
{
    #[Description("Unique identifier for each user.")]
    public int $id; 
    public int $age;
    public string $name;
    #[Description("Correct and complete list of friend IDs, representing relationships between users.")]
    /** @var int[] */
    public array $friends;
}

class UserRelationships
{
    #[Description("Collection of users, correctly capturing the relationships among them.")]
    /** @var UserDetail[] */
    public array $users;
}
```

## Modular Chain of Thought

!!! example

    Run example via CLI: ```php ./examples/ChainOfThought/run.php```


This approach to "chain of thought" improves data quality but can have modular components rather than global CoT.


```php
<?php

use Cognesy\Instructor\Attributes\Description;

class Role
{
    #[Description("Think step by step to determine the correct title.")]
    public string $chainOfThought = '';
    public string $title = '';
}

class UserDetail
{
    public int $age;
    public string $name;
    public Role $role;
}
```


## Reusing Components with Different Contexts

You can reuse the same component for different contexts within a model. In this example, the TimeRange component is used for both ```$workTime``` and ```$leisureTime```.


```php
<?php

use Cognesy\Instructor\Attributes\Description;

class TimeRange {
    #[Description("The start time in hours.")]
    public int $startTime;
    #[Description("The end time in hours.")]
    public int $endTime;
}

class UserDetail
{
    #[Description("Unique identifier for each user.")]
    public int $id;
    public int $age;
    public int $name;
    #[Description("Time range during which the user is working.")]
    public TimeRange $workTime;
    #[Description("Time range reserved for leisure activities.")]
    public TimeRange $leisureTime;
}
```

Sometimes, a component like TimeRange may require some context or additional logic to be used effectively. Employing a "chain of thought" field within the component can help in understanding or optimizing the time range allocations.


```php
<?php

class TimeRange
{
    #[Description("Step by step reasoning to get the correct time range")]
    public string $chainOfThought;
    #[Description("The start time in hours.")]
    public int $startTime;
    #[Description("The end time in hours.")]
    public int $endTime;
}
```