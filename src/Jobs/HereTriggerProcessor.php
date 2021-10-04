<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Karamvirs\HereTrigger\HereTrigger;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class HereTriggerProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $trigger;
    
    private $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($trigger, $payload)
    {
        $this->trigger = $trigger;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(HereTrigger $service)
    {
        $service->process($this->trigger, $this->payload);
    }
}
