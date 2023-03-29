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
use FCM;

class SendGoogleNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token,$title,$body,$order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token,$title,$body,$order=null)
    {
        //
        $this->token = $token;
        $this->body = $body;
        $this->title = $title;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // get message using type and language

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($this->title);
        $notificationBuilder->setBody($this->body)
                            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();

        $extraData['test']='test';
      
        $extraData['order_id']=@$this->order->id;
        $extraData['status']=@$this->order->status;

        $dataBuilder->addData($extraData);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // return $token;
        $downstreamResponse = FCM::sendTo($this->token, $option, $notification, $data);
        // $downstreamResponse->numberSuccess();

        if($downstreamResponse->numberSuccess()>0){
            return 'success';
        } else {
            return 'failed';
        }
    }
}
