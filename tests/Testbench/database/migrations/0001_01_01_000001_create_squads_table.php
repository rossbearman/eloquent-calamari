<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RossBearman\Sqids\Tests\Testbench\Models\Ocean;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('location');
            $table->string('slug')->unique();
            $table->foreignIdFor(Ocean::class)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squads');
    }
};
