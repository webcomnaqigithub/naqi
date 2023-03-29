<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp;
use App\Models\Yamamah;
use Log;


class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message,$number;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message,$number)
    {
        //
        $this->message = $message;
        $this->number = $number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $mobilenumber = (string)((int)($this->number));
            $client=new \GuzzleHttp\Client();

            $phone_number = $mobilenumber;
            if(substr( $mobilenumber, 0, 3 ) !== "966"){
                $phone_number = '966'.$mobilenumber;
            }

            $response=$client->get('https://api.goinfinito.me/unified/v2/send?clientid=NaqiWaterymm6hleghwodz1i&clientpassword=2jfjta1ax2bv8kruxgvj25cx44bl5yt7&from=Naqi_Water&to='.$phone_number.'&text='.$this->message);
        }catch (\Exception $e){
            \Log::info('error while sending message to '.$this->number);
        }

    }
}
