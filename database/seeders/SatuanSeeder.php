<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data satuan umum untuk inventory management
        $satuans = [
            ['nama_satuan' => 'PCS', 'status' => 1],
            ['nama_satuan' => 'BOX', 'status' => 1],
            ['nama_satuan' => 'KG', 'status' => 1],
            ['nama_satuan' => 'LITER', 'status' => 1],
            ['nama_satuan' => 'PACK', 'status' => 1],
            ['nama_satuan' => 'UNIT', 'status' => 1],
            ['nama_satuan' => 'KARTON', 'status' => 1],
            ['nama_satuan' => 'LUSIN', 'status' => 1],
            ['nama_satuan' => 'GROSS', 'status' => 1],
            ['nama_satuan' => 'METER', 'status' => 1],
            ['nama_satuan' => 'ROLL', 'status' => 1],
            ['nama_satuan' => 'BOTOL', 'status' => 1],
            ['nama_satuan' => 'KALENG', 'status' => 1],
            ['nama_satuan' => 'SET', 'status' => 1],
            ['nama_satuan' => 'PASANG', 'status' => 0],
        ];

        echo "🌱 Seeding satuan...\n";

        foreach ($satuans as $satuan) {
            // Cek apakah satuan sudah ada
            $exists = DB::select("
                SELECT COUNT(*) as total
                FROM satuan
                WHERE nama_satuan = ?
            ", [$satuan['nama_satuan']]);

            if ($exists[0]->total == 0) {
                DB::insert("
                    INSERT INTO satuan (nama_satuan, status)
                    VALUES (?, ?)
                ", [
                    $satuan['nama_satuan'],
                    $satuan['status']
                ]);

                $statusText = $satuan['status'] == 1 ? '✅ Aktif' : '❌ Nonaktif';
                echo "   ✓ {$satuan['nama_satuan']} ($statusText)\n";
            } else {
                echo "   ⊖ {$satuan['nama_satuan']} (sudah ada, skip)\n";
            }
        }

        echo "✅ Seeding satuan selesai!\n\n";
    }
}
