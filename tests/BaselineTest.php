<?php

namespace Centaur\Tests;

use DB;

class BaselineTest extends TestCase
{
    public function testDatabaseExistance()
    {
        $this->seeInDatabase('users', ['email' => 'admin@admin.com']);
    }

    public function testDatabaseExistanceVersionTwo()
    {
        DB::table('users')->truncate();
        $this->dontSeeInDatabase('users', ['email' => 'admin@admin.com']);
    }
}
