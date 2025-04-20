<?php

require_once BASE_PATH . '/app/core/RuleRepository.php';
require_once BASE_PATH . '/app/core/Rule.php';
require_once BASE_PATH . '/app/core/HotelRepository.php';
require_once BASE_PATH . '/app/core/Hotel.php';

class RuleController
{
    private $pdo;
    private $ruleRepository;
    private $hotelRepository;

    public function __construct()
    {
        // Соединение с базой
        $this->pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
        // Репозиторий для взаимодействия с данными правил в базе
        $this->ruleRepository = new RuleRepository($this->pdo);
        $this->hotelRepository = new HotelRepository($this->pdo);
    }

    public function matchHotel()
    {
        $hotelId = $_GET['hotel_id'] ?? 0; // отель для которого делаем проверку

        // Загружаем все данные по отелю
        $hotelData = $this->hotelRepository->getHotelById((int)$hotelId);
        if (empty($hotelData)) {
            echo 'Отель не найден.';
            exit();
        }
        $hotel = new Hotel($hotelData);

        // Загружаем все активные правила
        $activeRulesGroupedByAgency = $this->ruleRepository->getAllActiveWithAgencyGrouped();

        // Массив для сообщений, которые должны быть выведены
        $messages = [];

        // Проходим по всем активным правилам
        foreach ($activeRulesGroupedByAgency as $agencyId => $rulesForAgency) {

            foreach ($rulesForAgency as $rule) {
                if ($rule->matches($hotel)) {
                    $messages[] = 'Агенство - (' . $rule->getAgencyName() . '), ' . 'Сообщение для менеджера - (' . $rule->getMessage() . ')';
                }
            }
        }

        require BASE_PATH . '/app/views/hotel/match_rules.php';
    }

    /**
     * Отображает форму добавления и список всех правил переданного агенства
     */
    public function agencyRulesList()
    {
        $agencyId = (int)($_GET['agency_id'] ?? 0);

        if ($agencyId === 0) {
            header("Location: /rules");
            exit;
        }

        $rules = $this->ruleRepository->getActiveRulesByAgency($agencyId);

        // Получаем имя агентства
        $stmt = $this->pdo->prepare("SELECT name FROM agencies WHERE id = ?");
        $stmt->execute([$agencyId]);
        $agency = $stmt->fetch(PDO::FETCH_ASSOC);
        $agencyName = $agency['name'] ?? "Агентство #$agencyId";

        require BASE_PATH . '/app/views/rules/agency_rules_list.php';
    }

    /**
     * Отображает страницу со всеми агенствами для добавления им правил
     */
    public function rulesList()
    {
        $pdo = new PDO('mysql:host=' . MYSQL_HOST . ';port=3306;dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
        $stmt = $pdo->query("SELECT id, name FROM agencies ORDER BY name ASC");
        $agencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require BASE_PATH . '/app/views/rules/agencies_list.php';
    }

    /**
     * Сохраняет новое правило в базе данных
     */
    public function store()
    {
        $data = $_POST;
        $data['conditions'] = $_POST['conditions'] ?? [];

        // Создание объекта Rule перед добавлением
        $rule = new Rule(
            [
                'id' => null,
                'agency_id' =>  $data['agency_id'],
                'name' =>  $data['name'],
                'message' =>  $data['message'],
                'conditions' =>  $data['conditions'],
                'is_active' =>  isset($data['is_active']) ? 1 : 0
            ]
        );

        try {
            $result = $this->ruleRepository->addRule($rule);

            // Если правило добавлено, возвращаем успех
            if ($result) {
                echo json_encode(['success' => 'Правило добавлено']);
            } else {
                echo json_encode(['error' => 'Ошибка при добавлении нового правила.']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => 'Ошибка при добавлении: ' . $e->getMessage()]);
        }
    }

    public function delete()
    {
        $id = (int)($_POST['rule_id'] ?? 0);
        if ($id > 0) {
            $this->ruleRepository->deleteRule($id);
            echo json_encode(['success' => 'Удалено']);
        } else {
            echo json_encode(['error' => 'Неверный ID']);
        }
    }
}
