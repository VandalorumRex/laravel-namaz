<?php

/**
 * Время расчёта намаза
 *
 * @author Mansur <mansur@halalcard.ru>
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

declare(strict_types=1);

namespace App\Lib;

/**
 * Description of Namaz
 *
 * @author Mansur
 */
class Namaz
{
    /**
     * Часовой пояс
     * @var int
     */
    private int $timeZone;

    /**
     * Долгота
     * @var float
     */
    private float $longitude;

    /**
     * Широта
     * @var float
     */
    private float $latitude;

    /**
     * Уравнение времени (EqT)
     * @var float
     */
    private float $eqt;

    /**
     * Полдень
     * @var float
     */
    private float $zenith;

    /**
     * Месяц
     * @var int
     */
    private int $month;

    /**
     * Склонение солнца (в радианах)
     * @var float
     */
    private float $d;

    public function __construct(int $timeZone, float $longitude, float $latitude, string $date = '0')
    {
        $this->timeZone = $timeZone;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        if ('0' == $date) {
            $date = date('Y-m-d');
        }
        list($year, $month, $day) = explode('-', $date);
        $this->month = (int)$month;
        //Юлианская дата
        $jd = gregoriantojd($this->month, (int)$day, (int)$year);
        //echo 'JD=' . $jd . PHP_EOL;
        //Количество полных дней и долей (со знаком плюс или минус) до эпохи J2000.0 (DN)
        $dn = $jd - 2451545.0;
        //echo 'DN=' . $dn . PHP_EOL;
        //Средняя аномалия солнца (G):
        $g = 357.529 + 0.98560028 * $dn;
        //Средняя долгота солнца (Q):
        $q = (280.459 + 0.98564736 * $dn); // % 360;
        //echo 'Q=' . $q . PHP_EOL;
        //Геоцентрическая видимая долгота эклиптики солнца (L) с учетом отклонения:
        $l = $q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(2 * deg2rad($g));
        //echo 'L=' . $l . ' ' . deg2rad($l) . PHP_EOL;
        //где все все константы (G, Q, L) — в градусах.
        //Расстояние солнца от Земли (R) в астрономических единицах (AU), может быть приближено к следующему значению
        $r = 1.00014 - 0.01671 * cos(deg2rad($g)) - 0.00014 * cos(2 * deg2rad($g));
        //echo 'R=' . $r . PHP_EOL;
        //Для начала вычислим среднюю наклонность эклиптики (E) в градусах:
        $e = 23.439 - 0.00000036 * $dn;
        //Прямое восхождение солнца(RA):
        $ra = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l))) / 15);
        //$ra = 328 / 90 * M_PI;
        //Склонение солнца (D):
        $this->d = asin(sin(deg2rad($e)) * sin(deg2rad($l)));
        //echo 'D=' . $this->d . ' ' . rad2deg($this->d) . PHP_EOL;
        //Уравнение времени (EqT):
        $this->eqt = $q / 15 - $ra;
        $this->zenith = 12 + $this->timeZone - $this->longitude / 15 - $this->eqt;
        //$this->dayHalf = $this->dayHalf(0.833);
    }

    /**
     * Возвращает списко всех намазов
     *
     * @return array<string, float|string>
     */
    public function all(): array
    {
        return [
            'fajr' => $this->fajr(),
            'sunrise' => $this->sunrise(),
            'zenith' => $this->zenith(),
            'zuhr' => $this->zuhr(),
            'asr_shafii' => $this->asr(1),
            'asr' => $this->asr(2),
            'maghreb' => $this->maghreb(),
            'isha' => $this->isha()
        ];
    }

    /**
     * Аср
     *
     * @param float $t
     * @param string $format
     * @return float|string
     */
    public function asr(float $t = 2, string $format = 'string'): float|string
    {
        $x = $t + tan(deg2rad($this->latitude) - $this->d);
        //echo $x . PHP_EOL;
        $arccot = Math::arccot($x);
        //echo $arccot . PHP_EOL;
        $asr = $this->zenith + rad2deg(acos((sin($arccot) - (sin(deg2rad($this->latitude)) * sin($this->d))) / (cos(deg2rad($this->latitude)) * cos($this->d)))) / 15;
        return 'string' == $format ? gmdate('H:i:s', (int)($asr * 3600)) : $asr;
    }

    /**
     * Ночной намаз
     *
     * @param float $angle
     * @param string $format
     * @return float|string
     */
    public function isha(float $angle = 15.0, string $format = 'string'): float|string
    {
        $isha = $this->zenith + $this->timeDiff($angle);
        return 'string' == $format ? gmdate('H:i:s', (int)($isha * 3600)) : $isha;
    }

    /**
     * Утренний намаз
     *
     * @param float $angle
     * @param string $format
     * @return float|string
     */
    public function fajr(float $angle = 16.0, string $format = 'string'): float|string
    {
        $fajr = (float)$this->sunrise('float') - 1.5; //$this->timeDiff($angle);
        return 'string' == $format ? gmdate('H:i:s', (int)($fajr * 3600)) : $fajr;
    }

    /**
     * Ахшам
     *
     * @param string $format
     * @return float|string
     */
    public function maghreb(string $format = 'string'): float|string
    {
        $maghreb = $this->zenith + $this->timeDiff(0.833);
        return 'string' == $format ? gmdate('H:i:s', (int)($maghreb * 3600)) : $maghreb;
    }

    /**
     * Кояш чыгу
     *
     * @param string $format
     * @return float|string
     */
    public function sunrise(string $format = 'string'): float|string
    {
        $sunrise = $this->zenith - $this->timeDiff(0.833);
        return 'string' == $format ? gmdate('H:i:s', (int)($sunrise * 3600)) : $sunrise;
    }

    /**
     * Временная разница (в градусах (часах))
     * @param float $a
     * @return float
     */
    public function timeDiff(float $a): float
    {
        return rad2deg(acos((-sin(deg2rad($a)) - sin(deg2rad($this->latitude)) * sin($this->d)) / (cos(deg2rad($this->latitude)) * cos($this->d))) / 15);
    }

    /**
     * Зенит
     *
     * @param string $format
     * @return float|string
     */
    public function zenith(string $format = 'string'): float|string
    {
        return 'string' == $format ? (string)gmdate('H:i:s', (int)($this->zenith * 3600)) : (float)$this->zenith;
    }

    /**
     * Зухр
     *
     * @param string $format
     * @return float|string
     */
    public function zuhr(string $format = 'string'): float|string
    {
        $diff = 0.0; //$this->month < 5 || $this->month > 8 ? 0.05 : 1 / 6;
        $zuhr = (float)$this->zenith('float') + $diff;
        return 'string' == $format ? gmdate('H:i:s', (int)($zuhr * 3600)) : $zuhr;
    }
}
