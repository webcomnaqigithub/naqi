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

class FirebaseSendToTopic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $topic,$title,$body;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($topic,$title,$body)
    {
        //
        $this->topic = $topic;
        $this->body = $body;
        $this->title = $title;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notificationBuilder = new PayloadNotificationBuilder($this->title);
        $notificationBuilder->setBody($this->body)
                            ->setSound('default');

        $notification = $notificationBuilder->build();

        $topic = new Topics();
        $topic->topic($this->topic);

        $topicResponse = FCM::sendToTopic($topic, null, $notification, null);

        if($topicResponse->isSuccess()){
            return 'success';
        } else {
            return 'failed';
        }
        //
    }
}
