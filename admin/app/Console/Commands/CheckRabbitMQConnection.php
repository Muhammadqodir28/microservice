<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class CheckRabbitMQConnection extends Command
{
    protected $signature = 'rabbitmq:check';

    protected $description = 'Check RabbitMQ connection';

    public function handle(): int
    {
        $this->info('Check RabbitMQ connection...');

        try {
            $connection = Queue::connection('rabbitmq');

            if (method_exists($connection, 'getChannel')) {
                $connection->getChannel();
            } else {
                $connection->size();
            }

            $this->info('Done');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Connection failed');
            $this->line('Text: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
