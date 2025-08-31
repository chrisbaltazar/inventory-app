<?php

namespace App\DataFixtures\Factory;

use Faker\Factory;
use Faker\Generator;

abstract class AbstractFactory
{
    static private Generator $faker;

    public static function faker(): Generator
    {
        if (!isset(self::$faker)) {
            self::$faker = Factory::create();
        }

        return self::$faker;
    }
}