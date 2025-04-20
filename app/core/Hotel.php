<?php

/**
 * Класс, содержащий все данные по отелю.
 */
class Hotel
{
    public int $id;
    public string $name;
    public int $stars;
    public array $city;
    public array $agencyOptions;
    public array $agreements;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->stars = $data['stars'] ?? 0;
        $this->city = $data['city'] ?? [];
        $this->agencyOptions = $data['agency_options'] ?? [];
        $this->agreements = $data['agreements'] ?? [];
    }

    /**
     * Получить ID отеля
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Получить название отеля
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Получить звёзды
     */
    public function getStars(): int
    {
        return $this->stars;
    }

    /**
     * Получить город
     */
    public function getCity(): array
    {
        return $this->city;
    }

    /**
     * Получить опции для агентств
     */
    public function getAgencyOptions(): array
    {
        return $this->agencyOptions;
    }

    /**
     * Получить договоры
     */
    public function getAgreements(): array
    {
        return $this->agreements;
    }
}
