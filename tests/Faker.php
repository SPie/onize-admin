<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;

trait Faker
{
    private ?Generator $faker = null;

    protected function getFaker(): Generator
    {
        if (!$this->faker) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}