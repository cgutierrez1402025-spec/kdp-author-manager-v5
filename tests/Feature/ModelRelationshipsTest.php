<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionNamedType;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_declared_model_relationships_can_be_resolved_against_seeded_data(): void
    {
        $this->seed();

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            $instance = new $class;
            if (! Schema::hasTable($instance->getTable())) {
                continue;
            }

            $model = $class::query()->first();
            if (! $model) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class || $method->getNumberOfRequiredParameters() > 0) {
                    continue;
                }

                $type = $method->getReturnType();
                if (! $type instanceof ReflectionNamedType || ! is_subclass_of($type->getName(), Relation::class)) {
                    continue;
                }

                $relation = $method->invoke($model);
                $this->assertInstanceOf(Relation::class, $relation, "{$class}::{$method->getName()}");
                $relation->getResults();
            }
        }
    }
}
