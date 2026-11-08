<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddProxyListeningPortToNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            // Cek dulu kolomnya udah ada belum
            if (!Schema::hasColumn('nodes', 'proxy_listening_port')) {
                // Kita apus 'after', jadi dia bakal nambah di urutan terakhir. Lebih aman.
                $table->integer('proxy_listening_port')->default(80);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            if (Schema::hasColumn('nodes', 'proxy_listening_port')) {
                $table->dropColumn('proxy_listening_port');
            }
        });
    }
}