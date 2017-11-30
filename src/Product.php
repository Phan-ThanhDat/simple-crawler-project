<?php

/**
 * Class Products
 */
class Product {

    /**
     * Map columns with Excel file's columns
     */
    const COLUMNS_MAP = [
        'A' => 'number',
        'B' => 'name',
        'C' => 'manufacturer',
        'D' => 'bottle_size',
        'E' => 'price',
        'F' => 'price_per_liter',
        'U' => 'alcohol'
    ];

    const TABLE_NAME = 'products';

    public
        $id,
        $number,
        $name,
        $manufacturer,
        $bottle_size,
        $price,
        $price_per_liter,
        $alcohol,
        $timestamp,
        $quantity;

}
