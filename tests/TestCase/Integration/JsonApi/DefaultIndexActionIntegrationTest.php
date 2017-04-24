<?php
namespace Crud\Test\TestCase\Integration\JsonApi;

use Crud\Test\TestCase\Integration\JsonApiBaseTestCase;

class DefaultIndexActionIntegrationTest extends JsonApiBaseTestCase
{
    /**
     * Test most basic `index` action
     *
     * @return void
     */
    public function testGet()
    {
        $this->get('/countries');

        $this->assertResponseOk();
        $this->_assertJsonApiResponseHeaders();
        $this->assertResponseCode(200);
        $this->assertResponseNotEmpty();
        $this->assertResponseEquals($this->_getExpected('default_get_countries_with_pagination.json'));
    }
}
