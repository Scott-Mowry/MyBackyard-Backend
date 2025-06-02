<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropDatabase extends Command
{
    protected $signature = 'db:drop {name}';
    protected $description = 'Drop a database';

    public function handle()
    {
        $name = $this->argument('name');

        $host = env('DB_HOST', '127.0.0.1');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $connection = new \mysqli($host, $username, $password);

        if ($connection->connect_error) {
            $this->error('Connection failed: ' . $connection->connect_error);
            return;
        }

        $sql = "DROP DATABASE `$name`";

        if ($connection->query($sql) === TRUE) {
            $this->info("Database '$name' dropped successfully.");
        } else {
            $this->error('Error dropping database: ' . $connection->error);
        }

        $connection->close();
    }
}
