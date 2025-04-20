<?php
/**
 * Единая точка входа для удобства, т.к.
 * для добавления/удаления правил иначе пришлось бы создавать много разных .php файлов
 */

// Настройка путей
define('BASE_PATH', realpath(__DIR__ . '/../'));

// Конфиг и простейший роутер
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/routes/web.php';
