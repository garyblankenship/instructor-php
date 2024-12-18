<?php

namespace Cognesy\Instructor\Extras\ToolUse\Tools;

use Cognesy\Instructor\Extras\ToolUse\Contracts\CanAccessContext;
use Cognesy\Instructor\Extras\ToolUse\Traits\HandlesContext;

class UpdateContextVariable extends BaseTool implements CanAccessContext
{
    use HandlesContext;

    protected string $name = 'update_context_variable';
    protected string $description = 'Update a variable in the context';

    public function __construct() {
        $this->jsonSchema = $this->toJsonSchema();
    }

    public function __invoke(string $variableName, string $jsonValue): mixed {
        $this->context->withVariable($variableName, json_decode($jsonValue, true));
        return null;
    }
}