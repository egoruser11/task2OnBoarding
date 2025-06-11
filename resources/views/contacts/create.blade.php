<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма создания контакта</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .required:after {
            content: " *";
            color: red;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<h1>Форма создания контакта</h1>
<form action="{{ route('contacts.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="first_name" class="required">Имя</label>
        <input type="text" id="first_name" name="first_name" required maxlength="255">
    </div>

    <div class="form-group">
        <label for="last_name" class="required">Фамилия</label>
        <input type="text" id="last_name" name="last_name" required maxlength="255">
    </div>

    <div class="form-group">
        <label for="email" class="required">Email</label>
        <input type="email" id="email" name="email" required maxlength="255">
    </div>

    <div class="form-group">
        <label for="phone" class="required">Телефон</label>
        <input type="text" id="phone" name="phone" required maxlength="255">
    </div>

    <div class="form-group">
        <label for="age" class="required">Возраст</label>
        <input type="number" id="age" name="age" required min="1" max="100">
    </div>

    <div class="form-group">
        <label for="male" class="required">Пол</label>
        <select id="male" name="male" required>
            <option value="">-- Выберите пол --</option>
            <option value="мужской">Мужской</option>
            <option value="женский">Женский</option>
        </select>
    </div>

    <button type="submit">Отправить</button>
</form>
</body>
</html>
