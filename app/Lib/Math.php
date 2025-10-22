<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
declare(strict_types=1);

namespace App\Lib;

/**
 * Description of Math
 *
 * @author Mansur
 */
class Math
{
    /**
     * Арккотангенс
     *
     * @param float $x
     * @return float
     */
    public static function arccot(float $x): float
    {
        return acos($x / sqrt(1 + $x * $x));
    }
}
