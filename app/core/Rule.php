<?php

class Rule
{
    public int $id;
    public int $agencyId;
    public string $name;
    public array $conditions;
    public string $message;
    public string $agencyName = '';
    public bool $isActive;

    const CONDITION_MAP = [
        'stars' => 'stars',
        'is_black' => ['agencyOptions', '*', 'is_black'],
        'is_recomend' => ['agencyOptions', '*', 'is_recomend'],
        'discount_percent' => ['agreements', '*', 'discount_percent'],
        'comission_percent' => ['agreements', '*', 'comission_percent'],
        'is_default' => ['agreements', '*', 'is_default'],
        'country_id' => ['city', 'country', 'id'],
        'city_id' => ['city', 'id'],
        'company_id' => ['agreements', '*', 'company', 'id'],
    ];

    public function __construct(array $data)
    {
        $this->id = !empty($data['id']) ? (int)$data['id'] : 0;
        $this->agencyId = (int)$data['agency_id'];
        $this->name = $data['name'];
        if (is_string($data['conditions'])) {
            $this->conditions = json_decode($data['conditions'], true) ?? [];
        } else {
            $this->conditions = $data['conditions'] ?? [];
        }
        $this->message = $data['message'];
        $this->isActive = (bool)$data['is_active'];
    }

    /**
     * Проверка соответствия условиям правила для конкретного отеля.
     */
    public function matches(Hotel $hotel): bool
    {
        foreach ($this->conditions as $cond) {
            $field = $cond['field'] ?? null;
            $operator = $cond['operator'] ?? null;
            $value = $cond['value'] ?? null;

            if (!$field || !$operator) continue;
            if (!isset(self::CONDITION_MAP[$field])) return false;

            $path = self::CONDITION_MAP[$field];

            // если путь содержит '*', и это agencyOptions или agreements
            if (is_array($path) && in_array('*', $path)) {
                $base = $path[0];

                $items = match ($base) {
                    'agencyOptions' => $hotel->getAgencyOptions(),
                    'agreements' => $hotel->getAgreements(),
                    default => [],
                };

                if (!is_array($items) || empty($items)) return false;

                $filteredItems = array_filter($items, function ($item) {
                    return isset($item['agency_id']) && $item['agency_id'] == $this->agencyId;
                });

                if (empty($filteredItems)) return false;

                $values = [];
                foreach ($filteredItems as $item) {
                    $subValue = $this->extractHotelValue($item, array_slice($path, 2)); // пропускаем base и '*'
                    if ($subValue !== null) {
                        $values[] = $subValue;
                    }
                }

                if (empty($values)) return false;

                $matched = false;
                foreach ($values as $val) {
                    if ($this->compare($val, $operator, $value)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) return false;

            } else {
                // обычная проверка — извлекаем значение через extractHotelValue, передавая объект
                $hotelValue = $this->extractHotelValueFromObject($hotel, $path);
                if ($hotelValue === null) return false;

                if (!$this->compare($hotelValue, $operator, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function extractHotelValueFromObject(object $obj, array|string $path)
    {
        if (is_string($path)) {
            return $obj->$path ?? null;
        }

        $current = $obj;

        foreach ($path as $segment) {
            if (is_array($current)) {
                if (!isset($current[$segment])) return null;
                $current = $current[$segment];
            } elseif (is_object($current)) {
                // сначала пробуем как геттер
                $getter = 'get' . ucfirst($segment);
                if (method_exists($current, $getter)) {
                    $current = $current->$getter();
                } elseif (property_exists($current, $segment)) {
                    $current = $current->$segment;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $current;
    }

    private function compare($actual, string $operator, $expected): bool
    {
        switch ($operator) {
            case '=':
            case '==':
                return $actual == $expected;
            case '!=':
                return $actual != $expected;
            case '>':
                return $actual > $expected;
            case '>=':
                return $actual >= $expected;
            case '<':
                return $actual < $expected;
            case '<=':
                return $actual <= $expected;
            case 'in':
                if (!is_array($expected)) {
                    $expected = explode(',', $expected); // строка в массив
                }
                return in_array($actual, $expected);
            case 'not in':
                if (!is_array($expected)) {
                    $expected = explode(',', $expected);
                }
                return !in_array($actual, $expected);
            default:
                return false;
        }
    }

    private function extractHotelValue(array $hotel, $path) {
        if (is_string($path)) {
            return $hotel[$path] ?? null;
        }

        $current = $hotel;
        foreach ($path as $segment) {
            if ($segment === '*') {
                // массив — вернуть все найденные значения
                if (!is_array($current)) return null;
                $values = [];
                foreach ($current as $item) {
                    $subValue = $this->extractHotelValue($item, array_slice($path, array_search('*', $path) + 1));
                    if ($subValue !== null) $values[] = $subValue;
                }
                return $values;
            } else {
                if (!isset($current[$segment])) return null;
                $current = $current[$segment];
            }
        }
        return $current;
    }

    /**
     * Возвращает сообщение, если правило не выполняется.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Геттер для id агенства
     * @return int
     */
    public function getAgencyId(): int
    {
        return $this->agencyId;
    }

    public function getAgencyName(): string
    {
        return $this->agencyName;
    }
}
