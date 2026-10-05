<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'randi';

    public function up(): void
    {
        DB::connection('randi')->unprepared(<<<'SQL'
            CREATE TABLE date_invites (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                token_hash TEXT NOT NULL UNIQUE CHECK(length(token_hash) = 64),
                recipient_name TEXT CHECK(length(recipient_name) <= 80),
                sender_name TEXT NOT NULL DEFAULT 'Zoli' CHECK(length(sender_name) BETWEEN 1 AND 80),
                intro_message TEXT CHECK(length(intro_message) <= 240),
                expires_at TEXT NOT NULL,
                revoked_at TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE date_responses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invite_id INTEGER NOT NULL UNIQUE REFERENCES date_invites(id) ON DELETE CASCADE,
                decision TEXT NOT NULL CHECK(decision IN ('accepted', 'declined')),
                date_mode TEXT CHECK(date_mode IN ('specific_date', 'discuss_later')),
                preferred_date TEXT,
                time_window TEXT CHECK(time_window IN ('afternoon', 'early_evening', 'evening', 'flexible')),
                activity TEXT CHECK(activity IN ('coffee_walk', 'dinner', 'cinema', 'mini_trip', 'bowling', 'surprise', 'custom')),
                custom_activity TEXT CHECK(length(custom_activity) <= 200),
                note TEXT CHECK(length(note) <= 280),
                submission_key_hash TEXT NOT NULL UNIQUE,
                payload_hash TEXT NOT NULL,
                submitted_at TEXT NOT NULL,
                CHECK(
                    (decision = 'declined' AND date_mode IS NULL AND preferred_date IS NULL AND time_window IS NULL AND activity IS NULL AND custom_activity IS NULL AND note IS NULL)
                    OR (decision = 'accepted' AND date_mode IS NOT NULL AND activity IS NOT NULL
                        AND ((date_mode = 'discuss_later' AND preferred_date IS NULL AND time_window IS NULL)
                            OR (date_mode = 'specific_date' AND preferred_date IS NOT NULL AND time_window IS NOT NULL))
                        AND ((activity = 'custom' AND custom_activity IS NOT NULL AND length(custom_activity) > 0)
                            OR (activity != 'custom' AND custom_activity IS NULL)))
                )
            );
            SQL);
    }

    public function down(): void
    {
        DB::connection('randi')->unprepared('DROP TABLE IF EXISTS date_responses; DROP TABLE IF EXISTS date_invites;');
    }
};
