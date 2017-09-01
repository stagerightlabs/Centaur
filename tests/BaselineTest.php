<?php

namespace Centaur\Tests;

use DB;
use Centaur\Tests\TestCase;

class BaselineTest extends TestCase
{
    public function testDatabaseExistance()
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@admin.com']);
    }

    public function testDatabaseExistanceVersionTwo()
    {
        DB::table('users')->truncate();
        $this->assertDatabaseMissing('users', ['email' => 'admin@admin.com']);
    }
}
