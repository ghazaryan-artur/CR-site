<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->id();
            $table->string('page_title', 100);
            $table->text('page_description');
            $table->string('banner_title', 100);
            $table->string('banner_text');
            $table->string('banner_slug');
            $table->string('banner_slug_text');
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->string('image', 50);
            $table->string('image_alt');
            $table->string('image_title');
            $table->boolean('publish_status');
            $table->boolean('trending_status');
            $table->boolean('main_status');
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
        Schema::dropIfExists('blogs');
    }
}
