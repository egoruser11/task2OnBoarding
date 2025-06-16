@inject('kommoService', 'App\Http\Services\KommoService')
    <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ссылки на сущности amoCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .entity-link {
            display: block;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-decoration: none;
            color: #212529;
            transition: all 0.2s;
        }
        .entity-link:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Ссылки на сущности amoCRM</h1>

    <div class="row">
        <div class="col-md-6">
            <!-- Ссылка на контакт -->
            <a href="{{ $kommoService->getBaseUrl() }}/api/v4/contacts/{{ $result['contact_id'] }}"
               class="entity-link"
               target="_blank">
                <h5>Контакт</h5>
                <p class="mb-0">ID: {{ $result['contact_id'] }}</p>
            </a>

            <!-- Ссылка на сделку 1 -->
            <a href="{{ $kommoService->getBaseUrl() }}/api/v4/leads/{{ $result['leads_ids'][0] }}"
               class="entity-link"
               target="_blank">
                <h5>Сделка 1</h5>
                <p class="mb-0">ID: {{ $result['leads_ids'][0] }}</p>
            </a>
        </div>

        <div class="col-md-6">
            <!-- Ссылка на сделку 2 -->
            <a href="{{ $kommoService->getBaseUrl() }}/api/v4/leads/{{ $result['leads_ids'][1] }}"
               class="entity-link"
               target="_blank">
                <h5>Сделка 2</h5>
                <p class="mb-0">ID: {{ $result['leads_ids'][1] }}</p>
            </a>

            <!-- Ссылка на каталог -->
            <a href="{{ $kommoService->getBaseUrl() }}/api/v4/catalogs/{{ $result['catalog_id'] }}"
               class="entity-link"
               target="_blank">
                <h5>Каталог</h5>
                <p class="mb-0">ID: {{ $result['catalog_id'] }}</p>
            </a>
        </div>
    </div>
</div>
</body>
</html>
