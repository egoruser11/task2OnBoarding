<?php


namespace App\Http\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class kommoService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.kommo.base_url');
        $this->accessToken = config('services.kommo.access_token');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 15,
        ]);
    }

    public function refreshTokens()
    {
        try {
            $response = $this->client->post('/oauth2/access_token', [
                'form_params' => [
                    'client_id' => config('services.kommo.client_id'),
                    'client_secret' => config('services.kommo.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => config('services.kommo.refresh_token'),
                    'redirect_uri' => config('services.kommo.redirect_uri'),
                ]
            ]);
            $tokens = json_decode($response->getBody()->getContents(), true);
            // Здесь нужно обновить токены в .env
            return $tokens;
        } catch (GuzzleException $e) {
            Log::error('AmoCRM Token Refresh Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteContactCustomFields(array $fieldIds): array
    {
        $results = [];
        foreach ($fieldIds as $fieldId) {
            try {
                $response = $this->client->delete("/api/v4/leads/custom_fields/{$fieldId}");

                if ($response->getStatusCode() === 204) {
                    $results[$fieldId] = [
                        'success' => true,
                        'message' => "Поле {$fieldId} успешно удалено"
                    ];
                } else {
                    $results[$fieldId] = [
                        'success' => false,
                        'message' => "Ошибка при удалении поля {$fieldId}",
                        'status' => $response->getStatusCode()
                    ];
                }
            } catch (GuzzleException $e) {
                $results[$fieldId] = [
                    'success' => false,
                    'message' => "Ошибка: " . $e->getMessage()
                ];
                Log::error("Error deleting field {$fieldId}: " . $e->getMessage());
            }
        }
        return $results;
    }

    public function getPhones()
    {
        try {
            $response = $this->client->get('/api/v4/contacts', [
                'query' => [
                    'with' => 'leads,contacts' // Добавляем необходимые связи
                ]
            ]);
            $contacts = json_decode($response->getBody()->getContents(), true);
            $result = [];
            foreach ($contacts['_embedded']['contacts'] ?? [] as $contact) {
                $phones = [];
                foreach ($contact['custom_fields_values'] ?? [] as $field) {
                    if ($field['field_code'] === 'PHONE') {
                        $phones = array_column($field['values'], 'value');
                    }
                }
                $result[] = [
                    'phones' => $phones
                ];
            }
            $phonesDouble = array_column($result, 'phones');
            $result = [];
            foreach ($phonesDouble as $phones) {
                if (count($phones) > 1) {
                    foreach ($phones as $phone) {
                        $result[] = $phone;
                    }
                }
                if (count($phones) == 1) {
                    $result[] = $phones[0];
                };
            }
            return $result;

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            Log::error('Ошибка при получении контактов: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCustomFieldsNames(string $entity, array $query = [])
    {
        try {
            $response = $this->client->get("/api/v4/{$entity}/custom_fields");
            $fields = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200) {
                return array_column($fields['_embedded']['custom_fields'] ?? [], 'id');
            } else {
                Log::error('Failed to fetch custom fields: ' . json_encode($fields));
                return [];
            }

        } catch (GuzzleException $e) {
            Log::error('Error fetching custom fields: ' . $e->getMessage());
            return [];
        }
    }

    private function findFeildId($entity, $name)
    {
        try {
            $response = $this->client->get("/api/v4/{$entity}/custom_fields");
            $fields = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200) {
                $names = array_column($fields['_embedded']['custom_fields'] ?? [], 'name');
                $ids = array_column($fields['_embedded']['custom_fields'] ?? [], 'id');
                $fieldsMap = array_combine($names, $ids);
                return $fieldsMap[$name];
            } else {
                Log::error('Failed to fetch custom fields: ' . json_encode($fields));
                return [];
            }

        } catch (GuzzleException $e) {
            Log::error('Error fetching custom fields: ' . $e->getMessage());
            return [];
        }
    }

    public function getAccountUsers()
    {
        try {
            $response = $this->client->get('/api/v4/users', [
                'query' => [
                    'with' => 'roles',
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            Log::error('Ошибка при получении пользователей: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getContacts()
    {
        try {
            $response = $this->client->get('/api/v4/contacts', [
                'query' => [
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            Log::error('Ошибка при получении пользователей: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createLeadCloseDateTimeField()
    {
        $fieldData = [
            'name' => 'Date of end lead',
            'type' => 'text',
            'is_required' => false,
            'is_visible' => true,
            'enums' => null,
            'sort' => 500,
            'code' => 'CLOSE_DATETIME',
        ];
        try {
            $response = $this->client->post('/api/v4/leads/custom_fields', [
                'json' => [$fieldData]
            ]);
            $responseData = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                $fieldId = $responseData['_embedded']['custom_fields'][0]['id'] ?? null;
                return "поле с id {$fieldId} успешно создано";
            }
            return "ошибка при создании поля";
        } catch (GuzzleException $e) {
            Log::error('Error creating lead close datetime field: ' . $e->getMessage());
            return "not ok";
        }
    }

    public function createLeadWithContact(string $leadName, int $contactId)
    {
        $response = $this->client->get("/api/v4/contacts/{$contactId}", [
            'query' => [
                'with' => 'responsible_user'
            ]
        ]);
        $contactData = json_decode($response->getBody(), true);

        $endTime = now()->addDays(4);
        if ($endTime->dayOfWeek !== Carbon::SATURDAY && $endTime->dayOfWeek !== Carbon::SUNDAY) {
            if ($endTime->hour > 18 || $endTime->hour < 9) {
                $endTime->addDay()->setTime(9, 0);
            }
        }
        if ($endTime->dayOfWeek === Carbon::SATURDAY) {
            $endTime->addDays(2)->setTime(9, 0);
        } elseif ($endTime->dayOfWeek === Carbon::SUNDAY) {
            $endTime->addDay()->setTime(9, 0);
        }
        if ($endTime->dayOfWeek === Carbon::SATURDAY || $endTime->dayOfWeek === Carbon::SUNDAY) {
            $endTime->next(Carbon::MONDAY)->setTime(9, 0);
        }
        try {
            $closedAt = $endTime->format('Y-m-d H:i:s');
            if ($closedAt === false) {
                throw new \InvalidArgumentException("Invalid end time format");
            }
            $dealData = [
                [
                    'name' => $leadName,
                    'responsible_user_id' => $contactData['responsible_user_id'],
                    '_embedded' => [
                        'contacts' => [
                            [
                                'id' => $contactId
                            ]
                        ]
                    ],
                    'custom_fields_values' => [
                        [
                            'field_id' => $this->findFeildId('leads','Date of end lead'),
                            'values' => [
                                [
                                    'value' => $closedAt,
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $response = $this->client->post('/api/v4/leads', [
                'json' => $dealData
            ]);

            $responseData = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return [
                    'success' => true,
                    'lead_id' => $responseData['_embedded']['leads'][0]['id'] ?? null,
                    'message' => 'Deal successfully created'
                ];
            } else {
                Log::error('Failed to create deal: ' . json_encode($responseData));
                return [
                    'success' => false,
                    'message' => 'Failed to create deal',
                    'response' => $responseData
                ];
            }
        } catch (GuzzleException $e) {
            Log::error('Error creating deal: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creating deal: ' . $e->getMessage()
            ];
        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function createCustomFieldMaleForContacts()
    {
        $data = [
            'name' => 'Male',
            'type' => 'multiselect',
            'is_required' => false,
            'sort' => 500,
            'enums' => [
                ['value' => 'мужской'],
                ['value' => 'женский']
            ]
        ];
        try {
            $response = $this->client->post('/api/v4/contacts/custom_fields', [
                'json' => $data
            ]);
            $responseData = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return "Custom field '{$data['name']}' successfully created";
            } else {
                Log::error('Failed to create custom field: ' . json_encode($responseData));
                return "Failed to create custom field";
            }

        } catch (GuzzleException $e) {
            Log::error('Error creating custom field: ' . $e->getMessage());
            return "Error creating custom field";
        }
    }

    public function getLastId(string $entity): ?int
    {
        try {
            $response = $this->client->get("api/v4/{$entity}", [
                'query' => [
                    'order' => 'created_at,desc',
                    'limit' => 1,
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['_embedded'][$entity][0]['id'] ?? null;
        } catch (GuzzleException $e) {
            Log::error('Error fetching last contact: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getRandomUserId()
    {
        $usersData = $this->getAccountUsers();
        $userIds = array_column($usersData['_embedded']['users'] ?? [], 'id');
        return $userIds[array_rand($userIds)];
    }

    public function storeContact(array $data)
    {
        $contactData = [
            [
                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'responsible_user_id' => (int)$this->getRandomUserId(),
                'custom_fields_values' => [
                    [
                        'field_code' => 'EMAIL',
                        'values' => [
                            [
                                'value' => (string)$data['email'],
                                'enum_code' => 'WORK'
                            ]
                        ]
                    ],
                    [
                        'field_code' => 'PHONE',
                        'values' => [
                            [
                                'value' => (string)$data['phone'],
                                'enum_code' => 'WORK'
                            ]
                        ]
                    ],
                    [
                        'field_id' => $this->findFeildId('contacts','Age'),
                        'values' => [
                            [
                                'value' => (string)$data['age']
                            ]
                        ]
                    ],
                    [
                        'field_id' => $this->findFeildId('contacts','Male'),
                        'values' => [
                            [
                                'value' => (string)$data['male']
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $phones = $this->getPhones();
        if (in_array($data['phone'], $phones)) {
            return "Phone is not unique";
        }
        try {
            $response = $this->client->post('/api/v4/contacts', [
                'json' => $contactData,
            ]);
            $responseData = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return "Contact successfully created. ID: " . ($responseData['_embedded']['contacts'][0]['id'] ?? 'unknown');
            } else {
                Log::error('Failed to create contact: ' . json_encode($responseData));
                return "Failed to create contact";
            }

        } catch (GuzzleException $e) {
            Log::error('Error creating contact: ' . $e->getMessage());
            return "Error creating contact: " . $e->getMessage();
        }
    }


    public function createCustomFieldAgeForContacts()
    {
        $data = [
            'name' => 'Age',
            'type' => 'text',
            'is_required' => false,
            'sort' => 500,
        ];
        try {
            $response = $this->client->post('/api/v4/contacts/custom_fields', [
                'json' => $data
            ]);
            $responseData = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                return "Custom field '{$data['name']}' successfully created";
            } else {
                Log::error('Failed to create custom field: ' . json_encode($responseData));
                return "Failed to create custom field";
            }

        } catch (GuzzleException $e) {
            Log::error('Error creating custom field: ' . $e->getMessage());
            return "Error creating custom field";
        }
    }

    public function fieldExists(string $name, string $entity)
    {
        $result = $this->getCustomFieldsNames($entity);
        if (in_array($name, $result)) {
            return true;
        }
        return false;
    }

    public function createCatalog(string $name = "Основной каталог"): ?int
    {
        try {
            $response = $this->client->post('/api/v4/catalogs', [
                'json' => [
                    [
                        'name' => $name,
                        'type' => 'products'
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['_embedded']['catalogs'][0]['id'] ?? null;

        } catch (GuzzleException $e) {
            Log::error('Catalog creation failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getOrCreateCatalogId(): ?int
    {
        try {
            $response = $this->client->get('/api/v4/catalogs');
            $data = json_decode($response->getBody(), true);
            if (!empty($data['_embedded']['catalogs'])) {
                return $data['_embedded']['catalogs'][0]['id'];
            }
            return $this->createCatalog();

        } catch (GuzzleException $e) {
            Log::error('Failed to get catalogs: ' . $e->getMessage());
            return null;
        }
    }

    public function attachProductsToLead(int $leadId, array $products): array
    {
        $catalogId = $this->getOrCreateCatalogId();
        if (!$catalogId) {
            return ['success' => false, 'message' => 'Не удалось получить каталог'];
        }
        $createdElements = [];
        foreach ($products as $product) {
            try {
                $response = $this->client->post("/api/v4/catalogs/{$catalogId}/elements", [
                    'json' => [
                        [
                            'name' => $product['name'] ?? 'Товар без названия',
                            'custom_fields_values' => [

                            ]
                        ]
                    ]
                ]);
                $responseData = json_decode($response->getBody(), true);
                $createdElements[] = [
                    'id' => $responseData['_embedded']['elements'][0]['id'],
                    'quantity' => $product['quantity'] ?? 1
                ];

            } catch (GuzzleException $e) {
                Log::error('Ошибка создания товара: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Ошибка создания товара: ' . $e->getMessage()
                ];
            }
        }
        try {
            $response = $this->client->patch("/api/v4/leads/{$leadId}", [
                'json' => [
                    '_embedded' => [
                        'catalog_elements' => array_map(function ($element) use ($catalogId) {
                            return [
                                'id' => $element['id'],
                                'metadata' => [
                                    'quantity' => $element['quantity'],
                                    'catalog_id' => $catalogId
                                ]
                            ];
                        }, $createdElements)
                    ]
                ]
            ]);
            $responseData = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'message' => 'Товары успешно прикреплены',
                'data' => $responseData
            ];

        } catch (GuzzleException $e) {
            Log::error('Ошибка прикрепления товаров: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ошибка прикрепления товаров: ' . $e->getMessage()
            ];
        }
    }

    public function getContactWithCustomFields(int $contactId)
    {
        try {
            $response = $this->client->get("/api/v4/contacts/{$contactId}", [
                'query' => ['with' => 'custom_fields']
            ]);

            $contactData = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                return $contactData['custom_fields_values'] ?? []; // Возвращаем массив кастомных полей
            } else {
                Log::error("Ошибка при получении контакта: " . json_encode($contactData));
                return [];
            }
        } catch (GuzzleException $e) {
            Log::error("Ошибка API: " . $e->getMessage());
            return [];
        }
    }

}
