<?php

class ChecklistTemplateTest extends TestCase
{

    /**
     * /checklists/templates [GET]
     */
    public function testShouldReturnAllChecklistTemplates()
    {
        $header = ['HTTP_Authorization' => env('BEARER_TOKEN_TEST')];
        $this->get('checklists/templates', $header);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'meta' => ['count', 'total'],
            'links' => ['first', 'last', 'next', 'prev'],
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'checklist' => [
                        'id',
                        'task_id',
                        'due',
                        'description',
                        'due_interval',
                        'due_unit'
                    ],
                    'items' => [
                        '*' => [
                            'id',
                            'task_id',
                            'urgency',
                            'due',
                            'description',
                            'due_interval',
                            'due_unit'
                        ]
                    ]
                ]
            ],
        ]);
    }

    /**
     * /checklists/templates [POST]
     */
    public function testShouldCreateChecklistTemplates()
    {
        $header = ['HTTP_Authorization' => env('BEARER_TOKEN_TEST')];
        $payload = "{
            \"data\": {
                \"attributes\": {
                    \"name\": \"foo template\",
                    \"checklist\": {
                        \"description\": \"my checklist\",
                        \"due_interval\": 3,
                        \"due_unit\": \"hour\"
                    },
                    \"items\": [
                        {
                            \"description\": \"my foo item\",
                            \"urgency\": 2,
                            \"due_interval\": 40,
                            \"due_unit\": \"minute\"
                        },
                        {
                            \"description\": \"my bar item\",
                            \"urgency\": 3,
                            \"due_interval\": 30,
                            \"due_unit\": \"minute\"
                        }
                    ]
                }
            }
        }";
        $payload = json_decode($payload, true);
        $this->post('checklists/templates', $payload, $header);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'attributes' => [
                    'name',
                    'checklist' => ['description', 'due_interval', 'due_unit'],
                    'items' => [
                        '*' => [
                            'description', 'urgency', 'due_interval', 'due_unit'
                        ]
                    ]
                ],
            ]
        ]);
    }

    /**
     * /checklists/templates/{templateId} [GET]
     */
    public function testShouldReturnChecklistTemplates()
    {
        $header = ['HTTP_Authorization' => env('BEARER_TOKEN_TEST')];
        $this->get('checklists/templates/1', $header);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'type',
            'id',
            'attributes' => [
                'id',
                'name',
                'checklist' => ['id', 'task_id', 'due', 'description', 'due_interval', 'due_unit'],
                'items' => [
                    '*' => ['id', 'task_id', 'urgency', 'due', 'description', 'due_interval', 'due_unit']
                ]
            ],
            'links' => ['self']
        ]);
    }

    /**
     * /checklists/templates/{templateId} [PATCH]
     */
    public function testShouldUpdateChecklistTemplates()
    {
        $header = ['HTTP_Authorization' => env('BEARER_TOKEN_TEST')];
        $payload = "{
            \"data\": {
                \"attributes\": {
                    \"name\": \"foo template update\",
                    \"checklist\": {
                        \"description\": \"my checklist update\",
                        \"due_interval\": 3,
                        \"due_unit\": \"hour\"
                    },
                    \"items\": [
                        {
                            \"description\": \"my foo item update\",
                            \"urgency\": 2,
                            \"due_interval\": 40,
                            \"due_unit\": \"minute\"
                        },
                        {
                            \"description\": \"my bar item update\",
                            \"urgency\": 3,
                            \"due_interval\": 30,
                            \"due_unit\": \"minute\"
                        }
                    ]
                }
            }
        }";
        $payload = json_decode($payload, true);
        $this->patch('checklists/templates/1', $payload, $header);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'attributes' => [
                    'name',
                    'checklist' => ['description', 'due_interval', 'due_unit'],
                    'items' => [
                        '*' => [
                            'description', 'urgency', 'due_interval', 'due_unit'
                        ]
                    ]
                ],
            ]
        ]);
    }

    /**
     * /checklists/templates/{templateId} [DELETE]
     */
    public function testShouldDeleteChecklistTemplates()
    {
        $header = ['HTTP_Authorization' => env('BEARER_TOKEN_TEST')];
        $this->delete('checklists/templates/1', [], $header);
        $this->seeStatusCode(204);
    }
}