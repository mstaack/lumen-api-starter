<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ParagonIE\Paseto\Keys\SymmetricKey;

class CreateAuthenticationKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:generate-paseto-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new authentication key for paseto';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \TypeError
     */
    public function handle()
    {
        $key = (new SymmetricKey(random_bytes(32)))->encode();

        if (file_exists($envFilePath = $this->getPathToEnvFile()) === false) {
            $this->info("Could not find env file! Key: $key");
        }

        if ($this->updateEnvFile($envFilePath, $key)) {
            $this->info("File .env updated with key: $key");
        }
    }

    /**
     * @return string
     */
    private function getPathToEnvFile()
    {
        return base_path('.env');
    }

    private function updateEnvFile($path, $key)
    {
        if (file_exists($path)) {

            $oldContent = file_get_contents($path);
            $search = 'PASETO_AUTH_KEY=' . env('PASETO_AUTH_KEY');

            if (!str_contains($oldContent, $search)) {
                $search = 'PASETO_AUTH_KEY=';
            }

            $newContent = str_replace($search, 'PASETO_AUTH_KEY=' . $key, $oldContent);

            return file_put_contents($path, $newContent);
        }

        return false;
    }
}
