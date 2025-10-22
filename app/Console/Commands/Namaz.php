<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Lib\Namaz as NamazLib;

class Namaz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'namaz {args*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Сервис Namaz
     *
     * @var NamazLib
     */
    protected NamazLib $service;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //print_r($this->arguments());
        /** @var array{command: string, args: array<string>} $arguments */
        $arguments = $this->arguments();
        $timeZone = (int)$arguments['args'][0];
        $longitude = (float)$arguments['args'][1];
        $latitude = (float)$arguments['args'][2];
        $date = $arguments['args'][3] ?? '0';
        $this->service = new NamazLib($timeZone, $longitude, $latitude, $date);
        if (isset($arguments['args'][6])) {
            $result = $this->service->{$arguments['args'][4]}($arguments['args'][5], $arguments['args'][6]);
        } elseif (isset($arguments['args'][5])) {
            $result = $this->service->{$arguments['args'][4]}($arguments['args'][5]);
        } else {
            $result = $this->service->{$arguments['args'][4]}();
        }
        print_r($result);

        return 0;
    }
}
