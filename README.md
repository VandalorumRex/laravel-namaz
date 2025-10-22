## Description (Описание)

Библиотека для поиска времени намаза

## Installation (Установка)

git clone https://github.com/VandalorumRex/laravel-namaz.git

## Execution (Выполнение)

cd laravel-namaz

php artisan namaz {timeZone}, {longitude}, {latitude}, {date} all

Пример для Казани:

php artisan namaz 3 49.080916 55.827486 2025-10-22 all

выведет:
- Array
- (
    - [fajr] => 04:59:52
    - [sunrise] => 06:29:52
    - [zenith] => 11:28:07
    - [zuhr] => 11:28:07
    - [asr_shafii] => 13:51:45
    - [asr] => 14:31:00
    - [maghreb] => 16:26:21
    - [isha] => 18:08:56
- )

Вместо аргумента `all` можно использовать ключи из этого массива (fajr, sunrise и т.д.) выведет время только за этот намаз (отрезок суток)

## P.S

Во время создания библиотеки использвался сайт https://namaz.today/calculation