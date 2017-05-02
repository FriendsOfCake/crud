<?php
namespace Crud\Test\TestCase\Integration\JsonApi;

use Crud\Test\TestCase\Integration\JsonApiBaseTestCase;

class DefaultAddActionIntegrationTest extends JsonApiBaseTestCase
{
    /**
     * Make sure successful POST requests return HTTP Status Code 201.
     *
     * @link http://jsonapi.org/format/#crud-creating-responses-201
     * @link https://github.com/FriendsOfCake/crud/issues/496
     * @return void
     */
    public function testSuccessfulPostReturnsStatusCode201()
    {
        $postData = [
            'data' => [
                'type' => 'countries',
                'attributes' => [
                    'code' => 'NZ',
                    'name' => 'New Zealand',
                    'currency_id' => 1,
                    'national_capital_id' => 3
                ]
            ]
        ];

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json'
            ],
            'input' => json_encode($postData)
        ]);

        $this->post('/countries');

        $this->assertResponseOk();
        $this->_assertJsonApiResponseHeaders();
        $this->assertResponseCode(201);
        $this->assertResponseNotEmpty();
    }
}
