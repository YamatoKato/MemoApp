<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memo_tags', function (Blueprint $table) {
            //メモとタグを繋ぐ中間テーブル。１つのメモに複数のタグがつくとき(数不明)に使う。
            $table->unsignedBigInteger('memo_id');
            $table->unsignedBigInteger('tag_id');

            //ここも外部キー制約
            $table->foreign('memo_id')->references('id')->on('memos');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memo_tags');
    }
}
