<?php

namespace Tests;

use App\Support\Content;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Content::flushMemo();
        $this->withoutVite();
    }
}
