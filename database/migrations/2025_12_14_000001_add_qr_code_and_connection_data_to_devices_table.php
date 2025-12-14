<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCodeAndConnectionDataToDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->text('qr_code')->nullable()->after('webhook');
            $table->text('connection_data')->nullable()->after('qr_code');
            $table->timestamp('qr_generated_at')->nullable()->after('connection_data');
            $table->string('pairing_code', 20)->nullable()->after('qr_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'connection_data', 'qr_generated_at', 'pairing_code']);
        });
    }
}
