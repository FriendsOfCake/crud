<?php
namespace Crud\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Filesystem\File;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use Crud\Error\JsonApiExceptionRenderer;

class JsonApiIntegrationTest extends IntegrationTestCase
{
    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = [
        'plugin.crud.countries',
        'plugin.crud.currencies',
        'plugin.crud.cultures',
    ];

    /**
     * Path to directory holding json fixtures with trailing slash
     *
     * @var
     */
    protected $_JsonDir;

    /**
     * Set up required RESTful resource routes.
     */
    public function setUp()
    {
        Configure::write('Error.exceptionRenderer', JsonApiExceptionRenderer::class);

        Router::scope('/', function ($routes) {
            $routes->resources('Countries', [
                'inflect' => 'dasherize'
            ]);
            $routes->resources('Currencies', [
                'inflect' => 'dasherize'
            ]);
            $routes->resources('Cultures', [
                'inflect' => 'dasherize'
            ]);
        });

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/vnd.api+json'
            ]
        ]);

        // store path the the json fixtures
        $this->_JsonDir = Plugin::path('Crud') . 'tests' . DS . 'Fixture' . DS . 'JsonApi' . DS;
    }

    protected function _getExpected($file)
    {
        return trim((new File($this->_JsonDir . $file))->read());
    }

    protected function _getResponse()
    {
        $this->_response->getBody()->rewind();
        $response = $this->_response->getBody()->getContents();

        return $response;
    }

    /**
     * Test most basic `index` action
     *
     * @return void
     */
    public function testGet()
    {
        $this->get('/countries');

        $this->assertResponseOk();
        $this->_assertJsonApiResponse();
        $this->assertResponseCode(200);
        $this->assertResponseNotEmpty();
        $this->assertSame($this->_getExpected('get_countries_with_pagination.json'), $this->_getResponse());
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

    /**
     * @return array
     */
    public function viewProvider()
    {
        return [
            'no relations' => [
                '/countries/1',
                'get_country_no_relationships.json',
            ],
            'include culture' => [
                '/countries/1?include=cultures',
                'get_country_with_culture.json'
            ],
            'include currency plural' => [
                '/countries/1?include=currencies',
                'get_country_with_currency.json'
            ],
            'include currency singular' => [
                '/countries/1?include=currency',
                'get_country_with_currency.json'
            ],
            'include currency and culture' => [
                '/countries/1?include=currencies,cultures',
                'get_country_with_currency_and_culture.json'
            ],
            'include currency and deep countries' => [
                '/countries/1?include=currencies.countries',
                'get_country_with_currency_and_countries.json'
            ],
        ];
    }

    /**
     * @param string $url The endpoint to hit
     * @param string $expectedFile The file to find the expected result in
     * @return void
     * @dataProvider viewProvider
     */
    public function testView($url, $expectedFile)
    {
        $this->get($url);

        $this->assertResponseSuccess();
        $this->assertSame($this->_getExpected($expectedFile), $this->_getResponse());
    }

    /**
     * @return void
     */
    public function testViewWithContain()
    {
        EventManager::instance()
            ->on('Crud.beforeFind', function (Event $event) {
                $event->subject()->query->contain([
                    'Currencies',
                    'Cultures',
                ]);
            });
        $this->get('/countries/1');

        $this->assertResponseSuccess();
        $this->assertSame($this->_getExpected('get_country_with_currency_and_culture.json'), $this->_getResponse());
    }

    /**
     * @return void
     */
    public function testViewInvalidInclude()
    {
        $this->get('/countries/1?include=donkey');

        $this->assertResponseError();
    }
}
