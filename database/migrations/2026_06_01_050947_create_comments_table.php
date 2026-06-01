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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('item_uid', 50); // Referensi ke uid di testimonials / teacher_messages
            $table->string('item_type', 30); // 'testimonial' atau 'teacher_message'
            $table->string('author', 150);
            $table->text('comment');
            $table->dateTime('date');
            $table->string('status', 20)->default('approved'); // approved, pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
?>
