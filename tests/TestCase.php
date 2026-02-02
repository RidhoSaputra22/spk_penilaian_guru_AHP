<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF verification for tests
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::except(['*']);
    }
}
