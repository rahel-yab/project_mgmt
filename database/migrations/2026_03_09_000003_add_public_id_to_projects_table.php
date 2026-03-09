<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('public_id', 26)->nullable()->after('id');
        });

        Project::query()
            ->whereNull('public_id')
            ->get()
            ->each(function (Project $project): void {
                $project->update(['public_id' => (string) Str::ulid()]);
            });

        Schema::table('projects', function (Blueprint $table): void {
            $table->unique('public_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
