<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns that hold user-visible text, converted from single-language
     * strings into translatable JSON objects ({locale: value}).
     */
    protected array $tables = [
        'courses' => ['title' => 'string', 'description' => 'text'],
        'course_months' => ['name' => 'string'],
        'lessons' => ['title' => 'string', 'description' => 'text'],
        'quizzes' => ['title' => 'string', 'description' => 'text'],
        'assignments' => ['title' => 'string', 'description' => 'text'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $columns) {
            $this->wrapAsJson($table, $columns);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $columns) {
            $this->unwrapFromJson($table, $columns);
        }
    }

    protected function wrapAsJson(string $table, array $columns): void
    {
        // Existing rows hold plain strings — wrap each into {"ar": value}.
        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $columns) {
            $update = [];

            foreach (array_keys($columns) as $column) {
                $value = $row->{$column};

                if ($value === null || $this->isJson($value)) {
                    continue;
                }

                $update[$column] = json_encode(['ar' => $value]);
            }

            if ($update !== []) {
                DB::table($table)->where('id', $row->id)->update($update);
            }
        });

        Schema::table($table, function (Blueprint $table) use ($columns) {
            foreach (array_keys($columns) as $column) {
                $table->json($column)->nullable()->change();
            }
        });
    }

    protected function unwrapFromJson(string $table, array $columns): void
    {
        DB::table($table)->orderBy('id')->each(function ($row) use ($table, $columns) {
            $update = [];

            foreach ($columns as $column => $type) {
                $value = $row->{$column};

                if ($this->isJson($value)) {
                    $decoded = json_decode($value, true);
                    $update[$column] = is_array($decoded) ? ($decoded['ar'] ?? reset($decoded) ?? null) : $value;
                }
            }

            if ($update !== []) {
                DB::table($table)->where('id', $row->id)->update($update);
            }
        });

        Schema::table($table, function (Blueprint $table) use ($columns) {
            foreach ($columns as $column => $type) {
                $table->{$type}($column)->nullable()->change();
            }
        });
    }

    protected function isJson($value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
};
