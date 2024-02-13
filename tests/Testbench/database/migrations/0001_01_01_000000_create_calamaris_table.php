<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RossBearman\Sqids\Tests\Testbench\Models\Calamari;
use RossBearman\Sqids\Tests\Testbench\Models\Squad;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calamaris', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignIdFor(Calamari::class, 'parent_id')->nullable();
            $table->foreignIdFor(Squad::class)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calamaris');
    }
};
