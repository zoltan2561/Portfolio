<?php

namespace App\Services\Randi;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class InviteStore
{
    public static function secret(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    public function connection(): Connection
    {
        $path = config('database.connections.randi.database');
        if ($path !== ':memory:') {
            self::assertPrivatePath($path);
        }
        $db = DB::connection('randi');
        if ((int) $db->selectOne('PRAGMA foreign_keys')->foreign_keys !== 1 || (int) $db->selectOne('PRAGMA busy_timeout')->timeout !== 5000) {
            throw new RuntimeException('Randi SQLite configuration invalid.');
        }

        return $db;
    }

    public static function assertPrivatePath(string $path): void
    {
        if (! preg_match('~^(?:[A-Za-z]:[\\\\/]|/)~', $path)) {
            throw new RuntimeException('RANDI_DB_PATH must be absolute.');
        }
        $ancestor = $path;
        while (! file_exists($ancestor) && dirname($ancestor) !== $ancestor) {
            $ancestor = dirname($ancestor);
        }
        $resolved = str_replace('\\', '/', realpath($ancestor) ?: $ancestor);
        $public = str_replace('\\', '/', realpath(public_path()));
        if (str_contains(str_replace('\\', '/', $path), '/../') || strcasecmp($resolved, $public) === 0 || str_starts_with(strtolower($resolved).'/', strtolower($public).'/')) {
            throw new RuntimeException('Randi database must be outside public/.');
        }
    }

    public function find(string $token): ?object
    {
        return $this->connection()->table('date_invites')->where('token_hash', hash('sha256', $token))->first();
    }

    public function response(int $id): ?object
    {
        return $this->connection()->table('date_responses')->where('invite_id', $id)->first();
    }

    public function available(?object $invite): bool
    {
        return $invite && ! $invite->revoked_at && $invite->expires_at > CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');
    }

    public function create(array $data): array
    {
        $token = self::secret();
        $now = CarbonImmutable::now('UTC');
        $id = $this->connection()->table('date_invites')->insertGetId([
            'token_hash' => hash('sha256', $token),
            'recipient_name' => $data['recipient_name'] ?? null,
            'sender_name' => $data['sender_name'],
            'intro_message' => $data['intro_message'] ?? null,
            'expires_at' => $now->addDays((int) $data['expires_days'])->format('Y-m-d H:i:s'),
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return ['id' => $id, 'token' => $token, 'link' => rtrim(config('randi.base_url'), '/').'/randi/'.$token];
    }

    public function submit(string $token, string $key, array $input, ResponseValidator $validator): object
    {
        // Explicit BEGIN IMMEDIATE also works on PHP 8.2/8.3. Lock before reading
        // state, so two requests cannot both observe an unanswered invitation.
        return $this->locked(function (Connection $db) use ($token, $key, $input, $validator) {
            $invite = $db->table('date_invites')->where('token_hash', hash('sha256', $token))->first();
            if (! $this->available($invite)) {
                throw new RandiConflict('Ez a meghívó most nem elérhető. Kérj Zolitól egy új linket. 💌', 410);
            }
            $keyHash = hash('sha256', $key);
            $previous = $db->table('date_responses')->where('invite_id', $invite->id)->first();
            if ($previous) {
                if (hash_equals($previous->submission_key_hash, $keyHash) && hash_equals($previous->payload_hash, $this->payloadHash($validator->retryPayload($input)))) {
                    return $previous;
                }
                throw new RandiConflict('Erre a meghívóra már érkezett válasz.');
            }
            $payload = $validator->normalize($input);
            $id = $db->table('date_responses')->insertGetId($payload + [
                'invite_id' => $invite->id,
                'submission_key_hash' => $keyHash,
                'payload_hash' => $this->payloadHash($payload),
                'submitted_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'),
            ]);

            return $db->table('date_responses')->where('id', $id)->first();
        });
    }

    private function payloadHash(array $data): string
    {
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function locked(callable $callback): mixed
    {
        $db = $this->connection();
        $pdo = $db->getPdo();
        $pdo->exec('BEGIN IMMEDIATE');
        try {
            $result = $callback($db);
            $pdo->exec('COMMIT');

            return $result;
        } catch (Throwable $error) {
            $pdo->exec('ROLLBACK');
            throw $error;
        }
    }

    public function listing(): LengthAwarePaginator
    {
        return $this->connection()->table('date_invites')->orderByDesc('id')->paginate(20);
    }
}
