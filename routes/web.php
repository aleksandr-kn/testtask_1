<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Простейший роутинг
if ($uri === '/rules_edit' && $method === 'GET') { // Страница правил для агенства
    require_once BASE_PATH . '/app/controllers/RuleController.php';
    (new RuleController())->agencyRulesList();
} elseif ($uri === '/' && $method === 'GET') { // Страница проверки отеля на соотвествие правилам агенства
    require_once BASE_PATH . '/app/controllers/RuleController.php';
    (new RuleController())->matchHotel();
} elseif ($uri === '/rules' && $method === 'GET') { // Страница списка агенств для удобной навигации
    require_once BASE_PATH . '/app/controllers/RuleController.php';
    (new RuleController())->rulesList();
} elseif ($uri === '/rules/store' && $method === 'POST') { // Метод для добавления правила агенству
    require_once BASE_PATH . '/app/controllers/RuleController.php';
    (new RuleController())->store();
} elseif ($uri === '/rules/delete' && $method === 'POST') { // Метод для удаления правила агенству
    require_once BASE_PATH . '/app/controllers/RuleController.php';
    (new RuleController())->delete();
} else {
    http_response_code(404);
    echo "404 Not Found";
}
