<?php

$employees = collect([
    ['name' => 'John', 'city' => 'Dallas'],
    ['name' => 'Jane', 'city' => 'Austin'],
    ['name' => 'Jake', 'city' => 'Dallas'],
    ['name' => 'Jill', 'city' => 'Dallas'],
]);

$offices = collect([
    ['office' => 'Dallas HQ', 'city' => 'Dallas'],
    ['office' => 'Dallas South', 'city' => 'Dallas'],
    ['office' => 'Austin Branch', 'city' => 'Austin'],
]);

$employeesByCity = $employees->groupBy('city')
    ->map(function ($cityEmployees) {
        return $cityEmployees->pluck('name');
    });

$officesByCity = $offices->groupBy('city');

$output = $officesByCity
    ->map(fn($cityOffices, $city) => $cityOffices->mapWithKeys(
        fn($officeData) => [
            $officeData['office'] => $employeesByCity
                ->filter(fn($value, $key) => $key === $officeData['city'])
                ->first()
                ->toArray()
        ]
    ))
    ->toArray();
