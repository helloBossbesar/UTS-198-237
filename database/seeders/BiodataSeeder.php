<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Biodata;

class BiodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Biodata::create([
            'nama' => 'rizki eka prasodjo',
            'nim' => '12.11.5866',
            'deskripsi' => '12.11.5866 merupakan master dari uts ini dengan tugas membuat front-end dan mengirimkannya ke grup',
            'foto' => null,
        ]);

        Biodata::create([
            'nama' => 'andi agus salim',
            'nim' => '12.11.6018',
            'deskripsi' => 'dengan NIM 12.11.6018 merupakan pegawai dan mempunyai tugas mengerjakan back-end pada UTS ini',
            'foto' => null,
        ]);
    }
}
