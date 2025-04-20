<?php

class HotelRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Получить все связанные данные об отеле по ID.
     */
    public function getHotelById(int $hotelId): ?array
    {
        // 1. Основная информация
        $stmt = $this->pdo->prepare("
            SELECT 
                hotels.id,
                hotels.name,
                hotels.stars,
                cities.id AS city_id,
                cities.name AS city_name,
                countries.id AS country_id,
                countries.name AS country_name
            FROM hotels
            JOIN cities ON hotels.city_id = cities.id
            JOIN countries ON cities.country_id = countries.id
            WHERE hotels.id = :hotel_id
        ");
        $stmt->execute(['hotel_id' => $hotelId]);
        $hotelData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hotelData) {
            return null;
        }

        $hotel = [
            'id' => $hotelData['id'],
            'name' => $hotelData['name'],
            'stars' => $hotelData['stars'],
            'city' => [
                'id' => $hotelData['city_id'],
                'name' => $hotelData['city_name'],
                'country' => [
                    'id' => $hotelData['country_id'],
                    'name' => $hotelData['country_name'],
                ],
            ],
            'agency_options' => [],
            'agreements' => [],
        ];

        // 2. Опции агентств
        $stmt = $this->pdo->prepare("
            SELECT id, agency_id, percent, is_black, is_recomend, is_white
            FROM agency_hotel_options
            WHERE hotel_id = :hotel_id
        ");
        $stmt->execute(['hotel_id' => $hotelId]);
        $agencyOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($agencyOptions as $option) {
            $hotel['agency_options'][] = $option;
        }

        // 3. Соглашения
        $stmt = $this->pdo->prepare("
            SELECT 
                hotel_agreements.id,
                hotel_agreements.discount_percent,
                hotel_agreements.comission_percent,
                hotel_agreements.is_default,
                hotel_agreements.vat_percent,
                hotel_agreements.vat1_percent,
                hotel_agreements.vat1_value,
                hotel_agreements.date_from,
                hotel_agreements.date_to,
                hotel_agreements.is_cash_payment,
                companies.id AS company_id,
                companies.name AS company_name
            FROM hotel_agreements
            LEFT JOIN companies ON hotel_agreements.company_id = companies.id
            WHERE hotel_agreements.hotel_id = :hotel_id
        ");
        $stmt->execute(['hotel_id' => $hotelId]);
        $agreements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($agreements as $agreement) {
            $hotel['agreements'][] = [
                'id' => $agreement['id'],
                'company' => [
                    'id' => $agreement['company_id'],
                    'name' => $agreement['company_name'],
                ],
                'discount_percent' => $agreement['discount_percent'],
                'comission_percent' => $agreement['comission_percent'],
                'is_default' => $agreement['is_default'],
                'vat_percent' => $agreement['vat_percent'],
                'vat1_percent' => $agreement['vat1_percent'],
                'vat1_value' => $agreement['vat1_value'],
                'date_from' => $agreement['date_from'],
                'date_to' => $agreement['date_to'],
                'is_cash_payment' => $agreement['is_cash_payment'],
            ];
        }

        return $hotel;
    }
}
