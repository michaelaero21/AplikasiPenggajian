<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransportDanPulsaToSlipGajisTable extends Migration
{
    public function up()
    {
        Schema::table('slip_gajis', function (Blueprint $table) {
            if (Schema::hasColumn('slip_gajis', 'potongan')) {
                $table->dropColumn('potongan');
            }

            if (!Schema::hasColumn('slip_gajis', 'tunjangan_sewa')) {
                $table->decimal('tunjangan_sewa', 15, 2)->default(0)->after('tunjangan_pulsa');
            }
            if (!Schema::hasColumn('slip_gajis', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('thr');
            }
            if (!Schema::hasColumn('slip_gajis', 'insentif')) {
                $table->decimal('insentif', 15, 2)->default(0)->after('asuransi');
            }
        });
    }

    public function down()
    {
        Schema::table('slip_gajis', function (Blueprint $table) {
            if (!Schema::hasColumn('slip_gajis', 'potongan')) {
                $table->decimal('potongan', 15, 2)->default(0)->after('total');
            }

            if (Schema::hasColumn('slip_gajis', 'tunjangan_sewa')) {
                $table->dropColumn('tunjangan_sewa');
            }
            if (Schema::hasColumn('slip_gajis', 'asuransi')) {
                $table->dropColumn('asuransi');
            }
            if (Schema::hasColumn('slip_gajis', 'insentif')) {
                $table->dropColumn('insentif');
            }
        });
    }
}
