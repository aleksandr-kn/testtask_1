<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление правилами</title>
    <script>
        function sendRequest(url, formData, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                }
            };
            xhr.send(formData);
        }

        function addRule() {
            const formData = new FormData();

            // Забираем простые поля
            formData.append('agency_id', document.querySelector('[name="agency_id"]').value);
            formData.append('name', document.querySelector('[name="name"]').value);
            formData.append('message', document.querySelector('[name="message"]').value);
            formData.append('is_active', document.querySelector('[name="is_active"]').checked ? 1 : 0);

            // Условия — вручную собираем каждый блок .condition
            const conditions = document.querySelectorAll('.condition');
            conditions.forEach((cond, index) => {
                const field = cond.querySelector('[name="conditions[][field]"]')?.value;
                const operator = cond.querySelector('[name="conditions[][operator]"]')?.value;
                const value = cond.querySelector('[name="conditions[][value]"]')?.value;

                formData.append(`conditions[${index}][field]`, field);
                formData.append(`conditions[${index}][operator]`, operator);
                formData.append(`conditions[${index}][value]`, value);
            });

            // Send the data to the appropriate URL
            sendRequest('/rules/store', formData, function (response) {
                if (response.success) {
                    alert(response.success);
                    location.reload();
                } else {
                    alert(response.error || 'Ошибка сохранения');
                }
            });
        }

        function deleteRule(ruleId) {
            const formData = new FormData();
            formData.append('rule_id', ruleId);

            // Send the delete request to the correct URL
            sendRequest('/rules/delete', formData, function (response) {
                if (response.success) {
                    alert(response.success);
                    location.reload();
                } else {
                    alert(response.error);
                }
            });
        }

        // Клонирование блока условия
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('add-condition').addEventListener('click', function () {
                const container = document.getElementById('conditions-container');
                const original = document.querySelector('.condition');
                const clone = original.cloneNode(true);

                // Очистка значений в новом блоке
                clone.querySelectorAll('input, select').forEach(el => {
                    if (el.tagName === 'INPUT') el.value = '';
                    if (el.tagName === 'SELECT') el.selectedIndex = 0;
                });

                container.appendChild(clone);
            });
        });
    </script>
</head>
<body>

<a href="/rules">Назад к списку агенств</a>

<h1>Добавить правило для агентства: <?= htmlspecialchars($agencyName) ?> (ID: <?= $agencyId ?>)</h1>

<form onsubmit="event.preventDefault(); addRule();">
    <!-- Скрытое поле с ID агентства -->
    <input type="hidden" name="agency_id" value="<?= htmlspecialchars($agencyId) ?>">

    <label>Название: <input type="text" name="name" required></label><br><br>
    <label>Сообщение для менеджера:</label><br>
    <textarea name="message" rows="4" cols="50"></textarea><br><br>

    <h3>Условия:</h3>
    <div id="conditions-container">
        <div class="condition">
            <label>Поле:
                <select name="conditions[][field]" required>
                    <option value="is_black">Черный список</option>
                    <option value="is_recomend">Рекомендованный отель</option>
                    <option value="stars">Звездность отеля</option>
                    <option value="discount_percent">Скидка</option>
                    <option value="comission_percent">Комиссия</option>
                    <option value="is_default">По умолчанию</option>
                    <option value="country_id">Страна отеля</option>
                    <option value="city_id">Город отеля</option>
                    <option value="company_id">Компания</option>
                </select>
            </label><br>

            <label>Оператор:
                <select name="conditions[][operator]" required>
                    <option value="!=">не равно</option>
                    <option value="=">равно</option>
                    <option value=">">больше</option>
                    <option value=">=">больше или равно</option>
                    <option value="<">меньше</option>
                    <option value="<=">меньше или равно</option>
                    <option value="in">включая</option>
                    <option value="not in">не включительно</option>
                </select>
            </label><br>

            <label>Значение:
                <input type="text" name="conditions[][value]" required>
            </label><br><br>
        </div>
    </div>

    <button type="button" id="add-condition">Добавить условие</button><br><br>

    <label><input type="checkbox" name="is_active" checked> Активно</label><br><br>

    <button type="submit">Сохранить</button>
</form>

<hr>

<h2>Правила для агентства</h2>
<?php if (!empty($rules)): ?>
    <ul>
        <?php foreach ($rules as $rule): ?>
            <li>
                <strong><?= htmlspecialchars($rule->name) ?></strong><br>
                Условия: <pre><?= htmlspecialchars(json_encode($rule->conditions, JSON_PRETTY_PRINT)) ?></pre>
                Сообщение: <?= htmlspecialchars($rule->message) ?><br>
                <button onclick="deleteRule(<?= $rule->id ?>)">Удалить</button>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Список правил для агенства пока пуст</p>
<?php endif;?>

</body>
</html>
