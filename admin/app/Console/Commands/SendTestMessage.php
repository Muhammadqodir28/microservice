<?php

namespace App\Console\Commands;

use App\Jobs\TestRabbitMQJob;
use Illuminate\Console\Command;

class SendTestMessage extends Command
{
    protected $signature = 'rabbitmq:send';

    protected $description = 'Send test message';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $payload = ['message' => 'Hello from microservice Admin'];

        $this->info('Sending message to RabbitMQ...');

        TestRabbitMQJob::dispatch($payload);

        $this->info('Message sent successfully!');
        return Command::SUCCESS;
    }
}
