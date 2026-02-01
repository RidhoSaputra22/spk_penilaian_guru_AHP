<?php

namespace Database\Seeders;

use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        $admin = User::where('email', 'admin@demo.local')->first();

        if (!$institution || !$admin) {
            return; // run DemoUsersSeeder first
        }

        // Avoid duplicating
        if (CriteriaSet::where('institution_id', $institution->id)->where('name', 'Kriteria Kinerja Guru')->exists()) {
            return;
        }

        DB::transaction(function () use ($institution, $admin) {
            $set = CriteriaSet::create([
                'institution_id' => $institution->id,
                'name' => 'Kriteria Kinerja Guru',
                'version' => 1,
                'is_active' => true,
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $goal = CriteriaNode::create([
                'criteria_set_id' => $set->id,
                'parent_id' => null,
                'node_type' => 'goal',
                'code' => 'G1',
                'name' => 'Penilaian Kinerja Guru',
                'description' => 'Tujuan penilaian',
                'sort_order' => 0,
                'is_active' => true,
                'meta' => ['demo' => true],
            ]);

            $criteria = [
                ['C1', 'Pedagogik', [
                    ['SC1.1', 'Perencanaan Pembelajaran', ['I1.1.1', 'RPP lengkap', 'I1.1.2', 'Tujuan pembelajaran jelas']],
                    ['SC1.2', 'Pelaksanaan Pembelajaran', ['I1.2.1', 'Metode bervariasi', 'I1.2.2', 'Pengelolaan kelas']],
                ]],
                ['C2', 'Kepribadian', [
                    ['SC2.1', 'Integritas', ['I2.1.1', 'Disiplin', 'I2.1.2', 'Keteladanan']],
                    ['SC2.2', 'Etika', ['I2.2.1', 'Sikap profesional', 'I2.2.2', 'Tanggung jawab']],
                ]],
                ['C3', 'Sosial', [
                    ['SC3.1', 'Komunikasi', ['I3.1.1', 'Komunikasi dengan siswa', 'I3.1.2', 'Komunikasi dengan kolega']],
                    ['SC3.2', 'Kolaborasi', ['I3.2.1', 'Kerja tim', 'I3.2.2', 'Partisipasi kegiatan']],
                ]],
                ['C4', 'Profesional', [
                    ['SC4.1', 'Penguasaan Materi', ['I4.1.1', 'Materi sesuai kurikulum', 'I4.1.2', 'Contoh relevan']],
                    ['SC4.2', 'Pengembangan Diri', ['I4.2.1', 'Pelatihan/sertifikasi', 'I4.2.2', 'Inovasi pembelajaran']],
                ]],
            ];

            $sort = 1;
            foreach ($criteria as [$cCode, $cName, $subs]) {
                $cNode = CriteriaNode::create([
                    'criteria_set_id' => $set->id,
                    'parent_id' => $goal->id,
                    'node_type' => 'criteria',
                    'code' => $cCode,
                    'name' => $cName,
                    'description' => null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                    'meta' => ['demo' => true],
                ]);

                $subSort = 1;
                foreach ($subs as [$scCode, $scName, $ind]) {
                    $scNode = CriteriaNode::create([
                        'criteria_set_id' => $set->id,
                        'parent_id' => $cNode->id,
                        'node_type' => 'subcriteria',
                        'code' => $scCode,
                        'name' => $scName,
                        'description' => null,
                        'sort_order' => $subSort++,
                        'is_active' => true,
                        'meta' => ['demo' => true],
                    ]);

                    // each subcriteria has 2 indicators: (code1, name1, code2, name2)
                    for ($k = 0; $k < count($ind); $k += 2) {
                        CriteriaNode::create([
                            'criteria_set_id' => $set->id,
                            'parent_id' => $scNode->id,
                            'node_type' => 'indicator',
                            'code' => $ind[$k],
                            'name' => $ind[$k + 1],
                            'description' => null,
                            'sort_order' => ($k / 2) + 1,
                            'is_active' => true,
                            'meta' => ['demo' => true],
                        ]);
                    }
                }
            }
        });
    }
}
