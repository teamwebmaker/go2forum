<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;

abstract class DatabaseTruncationTestCase extends TestCase
{
    use DatabaseTruncation;

    protected function beforeTruncatingDatabase(): void
    {
        // In-memory SQLite connections are recreated between some Pest test
        // classes even though Laravel's static migration flag remains set.
        if (! Schema::hasTable('migrations')) {
            RefreshDatabaseState::$migrated = false;
        }
    }
}
