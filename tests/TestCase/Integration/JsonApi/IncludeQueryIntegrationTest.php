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
            // assert single-word associations
            'include currency belongsTo plural' => [
                '/countries/1?include=currencies',
                'get_country_include_currency.json'
            ],
            'include currency belongsTo singular' => [
                '/countries/1?include=currency',
                'get_country_include_currency.json'
            ],
            'include culture hasMany' => [
                '/countries/1?include=cultures',
                'get_country_include_culture.json'
            ],
            'include currency and culture' => [
                '/countries/1?include=currencies,cultures',
                'get_country_include_currency_and_culture.json'
            ],
            'include currency and deep countries' => [
                '/countries/1?include=currencies.countries',
                'get_country_include_currency_and_countries.json'
            ],
            // assert multi-word associations
            'include national-capital belongsTo singular' => [
                '/countries/1?include=national-capital',
                'get_country_include_national-capital.json'
            ],
            'include national-capital belongsTo plural' => [
                '/countries/1?include=national-capitals',
                'get_country_include_national-capital.json'
            ],
            'include national-cities hasMany' => [
                '/countries/1?include=national-cities',
                'get_country_include_national-cities.json'
            ],
            // assert all of the above in a single request
            'include all supported associations (singular belongsTo)' => [
                '/countries/1?include=currency,cultures,national-capital,national-cities',
                'get_country_include_all_supported_associations.json'
            ],
            'include all supported associations (plural belongsTo)' => [
                '/countries/1?include=currencies,cultures,national-capitals,national-cities',
                'get_country_include_all_supported_associations.json'
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
        $this->assertResponseEquals($this->_getExpected($expectedFile));
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
        $this->assertResponseEquals($this->_getExpected('get_country_include_currency_and_culture.json'));
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
