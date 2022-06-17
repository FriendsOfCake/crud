<?php
namespace Crud\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BlogsFixture extends TestFixture
{
    public array $records = [
        ['name' => '1st post', 'body' => '1st post body'],
        ['name' => '2nd post', 'body' => '2nd post body'],
        ['name' => '3rd post', 'body' => '3rd post body'],
        ['name' => '4th post', 'body' => '4th post body'],
        ['name' => '5th post', 'body' => '5th post body'],
    ];
}
