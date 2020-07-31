<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsRelatedBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs_related_blogs', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->id();
            $table->foreignId('blog_id')
                    ->constrained()
                    ->onDelete('cascade');
            $table->foreignId('related_blog_id')
                    ->references('id')
                    ->on('blogs')
                    ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blogs_related_blogs');
    }
}
