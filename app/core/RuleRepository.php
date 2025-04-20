<?php

class RuleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Загружает все активные правила с названием агентства
     */
    public function getAllActiveWithAgencyGrouped(): array
    {
        $stmt = $this->pdo->query("
            SELECT r.*, a.name as agency_name 
            FROM rules r
            JOIN agencies a ON r.agency_id = a.id
            WHERE r.is_active = 1
        ");

        $groupedRules = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rule = new Rule($row);
            $rule->agencyName = $row['agency_name']; // Добавим имя агентства
            $agencyId = $rule->getAgencyId();

            if (!isset($groupedRules[$agencyId])) {
                $groupedRules[$agencyId] = [];
            }

            $groupedRules[$agencyId][] = $rule;
        }

        return $groupedRules;
    }

    /**
     * Получить все активные правила для агентства.
     */
    public function getActiveRulesByAgency(int $agencyId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM rules WHERE agency_id = :agency_id AND is_active = 1");
        $stmt->execute(['agency_id' => $agencyId]);

        $rulesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rules = [];

        foreach ($rulesData as $ruleData) {
            $rules[] = new Rule($ruleData); // Преобразуем данные в объекты Rule
        }

        return $rules;
    }

    /**
     * Добавить новое правило.
     */
    public function addRule(Rule $rule): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO rules (agency_id, name, conditions, message, is_active)
            VALUES (:agency_id, :name, :conditions, :message, :is_active)
        ");
        $stmt->execute([
            'agency_id' => $rule->getAgencyId(),
            'name' => $rule->name,
            'conditions' => json_encode($rule->conditions),
            'message' => $rule->getMessage(),
            'is_active' => $rule->isActive ? 1 : 0,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Удалить правило по ID.
     */
    public function deleteRule(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM rules WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
