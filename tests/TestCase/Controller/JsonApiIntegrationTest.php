<?php
namespace Crud\TestCase\Controller;

use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

class JsonApiIntegrationTest extends IntegrationTestCase
{

    public $fixtures = [
        'plugin.crud.countries',
        'plugin.crud.currencies'
    ];

    /**
     * Set up required RESTful resource routes.
     */
    public function setUp()
    {
        Router::scope('/', function ($routes) {
            $routes->resources('Countries', [
                'inflect' => 'dasherize'
            ]);
        });
    }

    /**
     * Test most basic `index` action
     *
     * @return void
     */
    public function testGet()
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/vnd.api+json']
        ]);

        $this->get('/countries');

        $this->assertResponseOk();
        $this->_assertJsonApiResponse();
        $this->assertResponseCode(200);
        $this->assertResponseNotEmpty();
    }

    /**
     * Make sure HTTP Status Code 201 is returned after successfully
     * creating a new record using POST method.
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
                    'currency_id' => 1
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
        $this->_assertJsonApiResponse();
        $this->assertResponseCode(201);
        $this->assertResponseNotEmpty();
    }

    /**
     * Helper method to prevent repetition since all JSON API responses
     * must pass this test.
     */
    protected function _assertJsonApiResponse()
    {
        $this->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertContentType('application/vnd.api+json');
    }
}
