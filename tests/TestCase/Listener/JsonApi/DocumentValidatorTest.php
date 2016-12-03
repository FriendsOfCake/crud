<?php
namespace Crud\Test\TestCase\Listener\JsonApi;

use Crud\Listener\JsonApi\DocumentValidator;
use Crud\TestSuite\TestCase;
use stdClass;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DocumentValidatorTest extends TestCase
{
    /**
     * @var DocumentValidator
     */
    protected $_validator;

    /**
     * Create a DocumentValidator instance for every test with disabled
     * aboutLinks config option.
     */
    public function setUp()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);

        $listenerConfig = [
            'docValidatorAboutLinks' => false
        ];
        $this->setProtectedProperty('_config', $listenerConfig, $this->_validator);
    }

    /**
     * tearDown.
     */
    public function tearDown()
    {
        unset($this->_validator);
    }

    /**
     * _validateCreateDocument()
     *
     * @expectedException \Crud\Error\Exception\ValidationException
     * @expectedExceptionMessage A validation error occurred
     */
    public function testValidateCreateDocument()
    {
        // assert success
        $document = [
            'data' => [
                'type' => 'must-be-string',
                'id' => 'd0b31ee1-4637-48c9-b9ef-fcefbb83d86f'
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertNull($this->_validator->validateCreateDocument());

        // assert exception
        $document['data']['id'] = 'not-a-valid-uuid';
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->_validator->validateCreateDocument();
    }

    /**
     * _validateUpdateDocument()
     *
     * @expectedException \Crud\Error\Exception\ValidationException
     * @expectedExceptionMessage A validation error occurred
     */
    public function testValidateUpdateDocument()
    {
        // assert success
        $document = [
            'data' => [
                'type' => 'must-be-string',
                'id' => 'must-be-string',
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertNull($this->_validator->validateUpdateDocument());

        // assert exception
        $document['data']['id'] = null;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->_validator->validateUpdateDocument();
    }

    /**
     * _documentMustHavePrimaryData()
     *
     * @expectedException \Crud\Error\Exception\ValidationException
     * @expectedExceptionMessage A validation error occurred
     */
    public function testDocumentMustHavePrimaryData()
    {
        // assert success
        $document = [
            'data' => []
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_documentMustHavePrimaryData', [], $this->_validator));

        // assert exception
        $document = [];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->callProtectedMethod('_documentMustHavePrimaryData', [], $this->_validator);
    }

    /**
     * _primaryDataMustHaveType()
     *
     * @return void
     */
    public function testPrimaryDataMustHaveType()
    {
        // assert success
        $document = [
            'data' => [
                'type' => 'must-be-string'
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMustHaveType', [], $this->_validator));

        // assert false for non-string
        $document['data']['type'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_primaryDataMustHaveType', [], $this->_validator));

        // assert exception
        $document = [];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->callProtectedMethod('_primaryDataMustHaveType', [], $this->_validator);
    }

    /**
     * _primaryDataMayHaveUiId()
     *
     * @return void
     */
    public function testPrimaryDataMayHaveUuid()
    {
        // assert success
        $document = [
            'data' => [
                'id' => 'edd28c99-216b-4ef7-a806-d865aca14f17'
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveUuid', [], $this->_validator));

        // assert false for non-string
        $document['data']['id'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_primaryDataMayHaveUuid', [], $this->_validator));

        // assert exception
        $document = [];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->callProtectedMethod('_primaryDataMayHaveUuid', [], $this->_validator);
    }

    /**
     * _primaryDataMustHaveId()
     *
     * @return void
     */
    public function testPrimaryDataMustHaveId()
    {
        // assert success
        $document = [
            'data' => [
                'type' => 'must-be-string',
                'id' => 'must-be-string',
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMustHaveId', [], $this->_validator));

        // assert false for non-string
        $document['data']['id'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_primaryDataMustHaveId', [], $this->_validator));

        // assert exception
        $document = [];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->callProtectedMethod('_primaryDataMustHaveId', [], $this->_validator);
    }

    /**
     * _primaryDataMayHaveRelationShips()
     *
     * @return void
     */
    public function testPrimaryDataMayHaveRelationships()
    {
        // assert pass if relationships node with valid members exists
        $document = [
            'data' => [
                'type' => 'countries',
                'relationships' => [
                    'cultures' => [
                        'data' => [
                            [
                                'type' => 'cultures',
                                'id' => '1'
                            ]
                        ]
                    ],
                    'currency' => [
                        'data' => [
                            'type' => 'currencies',
                            'id' => '2'
                        ]
                    ]
                ]
            ]
        ];

        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert foreach loop continues if belongsTo relation has empty/null data node
        $document['data']['relationships']['currency']['data'] = null;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert foreach loop continues if belongsTo relation does not have data node
        unset($document['data']['relationships']['currency']['data']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert foreach loop continues if hasMany relation has empty/null data node
        $document['data']['relationships']['cultures']['data'] = null;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert foreach loop continues if hasMany relation does not have data node
        unset($document['data']['relationships']['cultures']['data']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert non-pass if `relationships` exists but has no members
        $document['data']['relationships'] = [];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));

        // assert pass if `relationships` does not exist at all
        unset($document['data']['relationships']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_primaryDataMayHaveRelationships', [], $this->_validator));
    }

    /**
     * _relationshipMustHaveData()
     *
     * @return void
     */
    public function testRelationshipMustHaveData()
    {
        $document = [
            'data' => [
                'type' => 'countries',
                'relationships' => [
                    'cultures' => [
                        'data' => [
                            [
                                'type' => 'cultures',
                                'id' => '1'
                            ]
                        ]
                    ],
                    'currency' => [
                        'data' => [
                            'type' => 'currencies',
                            'id' => '2'
                        ]
                    ]
                ]
            ]
        ];

        // assert success when belongsTo relation has data node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipMustHaveData', ['data.relationships.currency'], $this->_validator));

        // assert fail when belongsTo relation is missing data node
        unset($document['data']['relationships']['currency']['data']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipMustHaveData', ['data.relationships.currency'], $this->_validator));

        // assert success when hasMany relation has data node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipMustHaveData', ['data.relationships.cultures'], $this->_validator));

        // assert fail when hasMany relation is missing data node
        unset($document['data']['relationships']['cultures']['data']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipMustHaveData', ['data.relationships.cultures'], $this->_validator));
    }

    /**
     * _relationshipDataMustHaveType()
     *
     * @return void
     */
    public function testRelationshipDataMustHaveType()
    {
        $document = [
            'data' => [
                'type' => 'countries',
                'relationships' => [
                    'cultures' => [
                        'data' => [
                            [
                                'type' => 'cultures',
                                'id' => '1'
                            ]
                        ]
                    ],
                    'currency' => [
                        'data' => [
                            'type' => 'currencies',
                            'id' => '2'
                        ]
                    ]
                ]
            ]
        ];

        // assert success when belongsTo relationship data has valid type node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipDataMustHaveType', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert fail when belongsTo relationship data has type node but it's value is not a string
        $document['data']['relationships']['currency']['data']['type'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveType', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert fail when belongsTo relationship data does not have type node
        unset($document['data']['relationships']['currency']['data']['type']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveType', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert success when all hasMany relationships have data with valid type node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipDataMustHaveType', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));

        // assert fail when one of the hasMany relationships has data with type node but it's value is not a string
        $document['data']['relationships']['cultures']['data'][0]['type'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveType', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));

        // assert fail when one of the hasMany relationships has data without type node
        unset($document['data']['relationships']['cultures']['data'][0]['type']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveType', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));
    }

    /**
     * _relationshipDataMustHaveId()
     *
     * @return void
     */
    public function testRelationshipDataMustHaveId()
    {
        $document = [
            'data' => [
                'type' => 'countries',
                'relationships' => [
                    'cultures' => [
                        'data' => [
                            [
                                'type' => 'cultures',
                                'id' => '1'
                            ]
                        ]
                    ],
                    'currency' => [
                        'data' => [
                            'type' => 'currencies',
                            'id' => '2'
                        ]
                    ]
                ]
            ]
        ];

        // assert success when belongsTo relationship data has valid id node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipDataMustHaveId', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert fail when belongsTo relationship data has id node but it's value is not a string
        $document['data']['relationships']['currency']['data']['id'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveId', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert fail when belongsTo relationship data does not have id node
        unset($document['data']['relationships']['currency']['data']['id']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveId', ['currency', 'data.relationships.currency'], $this->_validator));

        // assert success when all hasMany relationships have data with valid id node
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_relationshipDataMustHaveId', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));

        // assert fail when one of the hasMany relationships has data with id node but it's value is not a string
        $document['data']['relationships']['cultures']['data'][0]['id'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveId', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));

        // assert fail when one of the hasMany relationships has data without id node
        unset($document['data']['relationships']['cultures']['data'][0]['id']);
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_relationshipDataMustHaveId', ['cultures', 'data.relationships.cultures.data.0'], $this->_validator));
    }

    /**
     * _isString()
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage Document member 'dummy.path' does not exist
     * @return void
     */
    public function testIsString()
    {
        // assert success
        $document = [
            'data' => 'some-string'
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_isString', ['data'], $this->_validator));

        // assert false for non-string
        $document['data'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_isString', ['data'], $this->_validator));

        // assert exception
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_isString', ['dummy.path'], $this->_validator));
    }

    /**
     * _isString()
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage Document member 'dummy.path' does not exist
     * @return void
     */
    public function testIsUuid()
    {
        // assert success
        $document = [
            'data' => 'edd28c99-216b-4ef7-a806-d865aca14f17'
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertTrue($this->callProtectedMethod('_isUuid', ['data'], $this->_validator));

        // assert false for non-string
        $document['data'] = 123;
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_isUuid', ['data'], $this->_validator));

        // assert exception
        $this->setProtectedProperty('_document', $document, $this->_validator);
        $this->assertFalse($this->callProtectedMethod('_isUuid', ['dummy.path'], $this->_validator));
    }

    /**
     * _hasProperty()
     *
     * @return void
     */
    public function testHasProperty()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);
        $document = [
            'data' => [
                'attributes' => [
                    'name' => 'bravo-kernel',
                    'test-null' => null,
                    'test-true' => true,
                    'test-false' => false
                ],
                'sub-array' => [
                    [
                        'name' => 'bravo-kernel',
                        'test-null' => null,
                        'test-true' => true,
                        'test-false' => false
                    ]
                ]
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);

        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.attributes'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.attributes.name'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.attributes.test-null'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.attributes.test-true'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.attributes.test-false'], $this->_validator));

        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.sub-array.0'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.sub-array.0.name'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.sub-array.0.test-null'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.sub-array.0.test-true'], $this->_validator));
        $this->assertTrue($this->callProtectedMethod('_hasProperty', ['data.sub-array.0.test-false'], $this->_validator));

        $this->assertFalse($this->callProtectedMethod('_hasProperty', ['fake-root'], $this->_validator));
        $this->assertFalse($this->callProtectedMethod('_hasProperty', ['data.fake-sub'], $this->_validator));
        $this->assertFalse($this->callProtectedMethod('_hasProperty', ['data.attributes.fake-sub'], $this->_validator));
    }

    /**
     * _getProperty()
     *
     * @expectedException \Crud\Error\Exception\CrudException
     * @expectedExceptionMessage Error retrieving a value for non-existing JSON API document property 'path.does.not.exist'
     */
    public function testGetProperty()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);
        $document = [
            'data' => [
                'attributes' => [
                    'name' => 'bravo-kernel'
                ]
            ]
        ];
        $this->setProtectedProperty('_document', $document, $this->_validator);

        //assert test single and multi-level lookups
        $expected = [
           'name' => 'bravo-kernel'
        ];
        $this->assertEquals($expected, $this->callProtectedMethod('_getProperty', ['data.attributes'], $this->_validator));
        $this->assertEquals('bravo-kernel', $this->callProtectedMethod('_getProperty', ['data.attributes.name'], $this->_validator));

        // assert using a path object works as well
        $obj = new stdClass();
        $obj->dotted = 'data.attributes.name';
        $obj->toKey = 'data.attributes';
        $obj->key = 'name';
        $this->assertEquals('bravo-kernel', $this->callProtectedMethod('_getProperty', [$obj], $this->_validator));

        // assert exception
        $this->callProtectedMethod('_getProperty', ['path.does.not.exist'], $this->_validator);
    }

    /**
     * _getPathObject()
     *
     * @return void
     */
    public function testGetPathObject()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);

        // assert existing object is returned as-if if passed as argument
        $obj = new stdClass();
        $obj->dotted = 'my.path';
        $obj->toKey = 'my';
        $obj->key = 'path';

        $this->assertEquals($obj, $this->callProtectedMethod('_getPathObject', [$obj], $this->_validator));

        // assert object is created with expected properties
        $obj = $this->callProtectedMethod('_getPathObject', ['data'], $this->_validator);

        $this->assertTrue(is_a($obj, 'stdClass'));
        $this->assertObjectHasAttribute('dotted', $obj);
        $this->assertObjectHasAttribute('toKey', $obj);
        $this->assertObjectHasAttribute('key', $obj);

        // assert single-level path
        $this->assertEquals('data', $obj->dotted);
        $this->assertEquals('', $obj->toKey);
        $this->assertEquals('data', $obj->key);

        // assert two-level path
        $obj = $this->callProtectedMethod('_getPathObject', ['data.attributes'], $this->_validator);

        $this->assertEquals('data.attributes', $obj->dotted);
        $this->assertEquals('data', $obj->toKey);
        $this->assertEquals('attributes', $obj->key);

        // assert three-level path
        $obj = $this->callProtectedMethod('_getPathObject', ['data.attributes.name'], $this->_validator);

        $this->assertEquals('data.attributes.name', $obj->dotted);
        $this->assertEquals('data.attributes', $obj->toKey);
        $this->assertEquals('name', $obj->key);
    }

    /**
     * _getAboutLink()
     *
     * @return void
     */
    public function testGetAboutLink()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);

        // assert no link is created if disabled in listener config
        $listenerConfig = [
            'docValidatorAboutLinks' => false
        ];

        $this->setProtectedProperty('_config', $listenerConfig, $this->_validator);
        $this->assertNull($this->callProtectedMethod('_getAboutLink', ['http://www.friendsofcake.com'], $this->_validator));

        // assert link is created when enabled in listener config
        $listenerConfig = [
            'docValidatorAboutLinks' => true
        ];

        $this->setProtectedProperty('_config', $listenerConfig, $this->_validator);
        $result = $this->callProtectedMethod('_getAboutLink', ['http://www.friendsofcake.com'], $this->_validator);

        $this->assertTrue(is_a($result, '\Neomerx\JsonApi\Document\Link'));
    }

    /**
     * Make sure _getErrorCollectionEntity() generates the expected entity
     * with NeoMerx ErrorCollection.
     *
     * @return void
     */
    public function testGetErrorCollectionEntity()
    {
        $this->_validator = new DocumentValidator([], []);
        $this->setReflectionClassInstance($this->_validator);

        $result = $this->callProtectedMethod('_getErrorCollectionEntity', [], $this->_validator);
        $this->assertInstanceOf('\Cake\ORM\Entity', $result);

        $errors = $result->errors();
        $this->assertArrayHasKey('CrudJsonApiListener', $errors);
        $this->assertArrayHasKey('NeoMerxErrorCollection', $errors['CrudJsonApiListener']);
        $this->assertInstanceOf('\Neomerx\JsonApi\Exceptions\ErrorCollection', $errors['CrudJsonApiListener']['NeoMerxErrorCollection']);
    }
}
