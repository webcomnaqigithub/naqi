<?php

namespace App\Console\Commands;
use App\Models\Region;

use Illuminate\Console\Command;

class DelayedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delay:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications if we have delayed orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Word of the Day sent to All Users');
        Region::create(['arabicName'=> 'arabicName','englishName'=> 'englishName']);
    }
}
