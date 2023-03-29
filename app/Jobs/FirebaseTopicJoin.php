<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;

use FCM;

class FirebaseTopicJoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $serverKey ='AAAAdmaZn8w:APA91bH1vKSimwo9W7_vjAaU7rywb1313uoJAjJTLU97UNZ4DZDAFKUI7CpxGf0wa4TEtHOERGkUHCA6DeRb3JRuGnYZw6O69KTXV0okJqXkqKbw0_CJPAxoCMkxX1MCLC7awIMZADt6';
    protected $token,$topicName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token,$topicName)
    {
        //
        $this->token = $token;
        $this->topicName = $topicName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $url = 'https://iid.googleapis.com/iid/v1/' . $this->token . '/rel/topics/' . $this->topicName;
            
                    $headers = array
                        ('Authorization:key='.$this->serverKey,
                        'Content-Type: application/json');
            
                    $ch = curl_init();
                    // browser token you can get it via ajax from client side
                    
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, array());
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $result = curl_exec($ch);
                } catch (\Exception $ex) {
                    return $ex;
                    Log::debug('{{{{ERR ADDTOTOPIC}}}' . $ex->getMessage());
                }
    }
}
