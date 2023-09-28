<?php
declare(strict_types=1);

return [
    [
        'table' => 'blogs',
        'columns' => [
            'id' => 'integer',
            'name' => ['type' => 'string', 'length' => 255],
            'body' => 'text',
            'is_active' => ['type' => 'boolean', 'default' => true, 'null' => false],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
    [
        'table' => 'users',
        'columns' => [
            'id' => ['type' => 'uuid'],
            'is_active' => ['type' => 'boolean', 'default' => true, 'null' => false],
            'username' => ['type' => 'string', 'length' => 255, 'null' => false],
        ] ,
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'number_trees',
        'columns' => [
            'id' => [
                'type' => 'integer',
            ],
            'name' => [
                'type' => 'string',
                'null' => false,
            ],
            'parent_id' => 'integer',
            'lft' => [
                'type' => 'integer',
            ],
            'rght' => [
                'type' => 'integer',
            ],
            'depth' => [
                'type' => 'integer',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
    [
        'table' => 'posts',
        'columns' => [
            'id' => [
                'type' => 'integer',
            ],
            'author_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'title' => [
                'type' => 'string',
                'null' => false,
            ],
            'body' => 'text',
            'published' => [
                'type' => 'string',
                'length' => 1,
                'default' => 'N',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
];
