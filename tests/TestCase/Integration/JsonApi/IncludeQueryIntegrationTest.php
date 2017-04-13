<?php
namespace Crud\Test\TestCase\Integration\JsonApi;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Crud\Test\TestCase\Integration\JsonApiBaseTestCase;

class IncludeQueryIntegrationTest extends JsonApiBaseTestCase
{
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
                'get_country_include_culture.json'
            ],
            'include currency plural' => [
                '/countries/1?include=currencies',
                'get_country_include_currency.json'
            ],
            'include currency singular' => [
                '/countries/1?include=currency',
                'get_country_include_currency.json'
            ],
            'include currency and culture' => [
                '/countries/1?include=currencies,cultures',
                'get_country_include_currency_and_culture.json'
            ],
            'include currency and deep countries' => [
                '/countries/1?include=currencies.countries',
                'get_country_include_currency_and_countries.json'
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
        $this->_assertJsonApiResponseHeaders();

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
        $this->assertSame($this->_getExpected('get_country_include_currency_and_culture.json'), $this->_getResponse());
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
