<?php

namespace App\Config;

class Helpers
{
    public static function getColorHex(string $colorName): string
    {
        $colorMap = [
            'Белый' => '#FFFFFF',
            'Чёрный' => '#000000',
            'Серый' => '#808080',
            'Красный' => '#FF0000',
            'Синий' => '#0000FF',
            'Зелёный' => '#008000',
            'Серебристый' => '#C0C0C0',
            'Золотистый' => '#FFD700',
            'Графитовый' => '#383838',
            'Бронзовый' => '#CD7F32',
            'Платиновый' => '#E5E4E2',
            'Голубой' => '#87CEEB',
            'Розовый' => '#FFC0CB',
            'Мятный' => '#98FB98',
            'Лавандовый' => '#E6E6FA',
            'Персиковый' => '#FFDAB9',
            'Хамелеон' => 'linear-gradient(90deg, #FF0000, #FFFF00, #00FF00, #00FFFF, #0000FF, #FF00FF)',
            'Карбоновый' => '#1C1C1C',
            'Жемчужный' => '#FDEEF4',
            'Ультрамарин' => '#120A8F',
            'Коралловый' => '#FF7F50',
            'Чёрно-белый' => 'linear-gradient(45deg, #000000 50%, #FFFFFF 50%)',
            'Красно-чёрный' => 'linear-gradient(45deg, #FF0000 50%, #000000 50%)',
            'Сине-серебристый' => 'linear-gradient(45deg, #0000FF 50%, #C0C0C0 50%)',
            'Оранжево-графитовый' => 'linear-gradient(45deg, #FFA500 50%, #383838 50%)'
        ];

        return $colorMap[$colorName] ?? '#CCCCCC';
    }
}
