<?php

namespace Centaur\Tests;

use DB;
use Orchestra\Testbench\ApplicationTestCase;

class BaselineTest extends TestCase
{
    public function testDatabaseExistance()
    {
        $c = new ApplicationTestCase;

        $this->seeInDatabase('users', ['email' => 'admin@admin.com']);
    }

    public function testDatabaseExistanceVersionTwo()
    {
        DB::table('users')->truncate();
        $this->dontSeeInDatabase('users', ['email' => 'admin@admin.com']);
    }
}
