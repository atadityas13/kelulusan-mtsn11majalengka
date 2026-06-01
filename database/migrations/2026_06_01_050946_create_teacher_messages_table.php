<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_messages', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 50)->unique(); // tm-xxxx
            $table->string('name', 150);
            $table->text('message');
            $table->integer('likes')->default(0);
            $table->dateTime('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_messages');
    }
};
?>
