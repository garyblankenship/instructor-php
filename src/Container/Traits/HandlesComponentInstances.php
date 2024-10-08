<?php

namespace Cognesy\Instructor\Container\Traits;

use Cognesy\Instructor\Container\Exceptions\NotFoundException;
use Cognesy\Instructor\Events\Container\ExistingInstanceReturned;
use Cognesy\Instructor\Events\Container\FreshInstanceForced;
use Cognesy\Instructor\Events\Container\NewInstanceReturned;
use Cognesy\Instructor\Events\Container\ReferenceResolutionRequested;

trait HandlesComponentInstances
{
    /** @var object[] array of component instances */
    private array $instances = [];

    /**
     * Get a component configuration for provided name (recommended: class or interface)
     */
    static public function for(string $name) : mixed {
        return self::instance()->get($name);
    }

    /**
     * Get a component instance
     *
     * @throws NotFoundException
     */
    public function get(string $id) : mixed {
        return $this->resolveReference($id);
    }

    /**
     * Resolve a component reference and return existing or fresh instance
     */
    private function resolveReference(string $componentName, bool $fresh = false) : mixed
    {
        $this->emit(new ReferenceResolutionRequested($componentName, $fresh));
        if (!$this->has($componentName)) {
            throw new NotFoundException('Component ' . $componentName . ' is not defined');
        }
        // if asked for fresh, return new component instance
        if ($fresh) {
            $this->emit(new FreshInstanceForced($componentName));
            return $this->getConfig($componentName)?->get();
        }
        // otherwise first check in instances
        if (isset($this->instances[$componentName])) {
            $this->emit(new ExistingInstanceReturned($componentName));
            return $this->instances[$componentName];
        }
        $this->preventDependencyCycles($componentName);
        $this->instances[$componentName] = $this->getConfig($componentName)?->get();
        $this->emit(new NewInstanceReturned($componentName));
        return $this->instances[$componentName];
    }
}