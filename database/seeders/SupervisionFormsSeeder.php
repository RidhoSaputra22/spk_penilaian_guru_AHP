<?php

namespace Database\Seeders;

use App\Models\AhpComparison;
use App\Models\AhpModel;
use App\Models\AhpWeight;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\ScoringScale;
use App\Models\ScoringScaleOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupervisionFormsSeeder extends Seeder
{
    /* ================================================================
     *  DATA DEFINITIONS — Form 2, 3, 4
     * ================================================================
     *
     *  Structure per form:
     *  [
     *      'set_name'     => CriteriaSet name,
     *      'goal_code'    => 'G2' | 'G3' | 'G4',
     *      'goal_name'    => string,
     *      'template'     => form template name,
     *      'description'  => form description,
     *      'criteria'     => [
     *          [ code, name, subcriteria => [
     *              [ code, name, indicators => [
     *                  [ code, name ],
     *              ]],
     *          ]],
     *      ],
     *  ]
     * ================================================================ */

    private function formDefinitions(): array
    {
        return [
            // ─────────────────────────────────────────────────────────
            // FORM 2 — Supervisi RPP
            // ─────────────────────────────────────────────────────────
            [
                'set_name'    => 'Kriteria Supervisi RPP',
                'goal_code'   => 'G2',
                'goal_name'   => 'Supervisi Rencana Pelaksanaan Pembelajaran (RPP)',
                'template'    => 'Form 2 – Supervisi RPP',
                'description' => 'Instrumen supervisi RPP berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria'    => [
                    [
                        'code' => 'F2C1',
                        'name' => 'Identitas & Kelengkapan RPP',
                        'subcriteria' => [
                            [
                                'code' => 'F2C1.1',
                                'name' => 'Kompetensi Inti & Kompetensi Dasar',
                                'indicators' => [
                                    ['F2I1.1.1', 'Kesesuaian KI dengan kurikulum yang berlaku'],
                                    ['F2I1.1.2', 'Kesesuaian KD dengan silabus'],
                                    ['F2I1.1.3', 'Kelengkapan identitas mata pelajaran, kelas, dan alokasi waktu'],
                                ],
                            ],
                            [
                                'code' => 'F2C1.2',
                                'name' => 'Kelengkapan Komponen RPP',
                                'indicators' => [
                                    ['F2I1.2.1', 'Tersedianya seluruh komponen RPP sesuai standar proses'],
                                    ['F2I1.2.2', 'Sistematika penyusunan RPP runtut dan logis'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F2C2',
                        'name' => 'Tujuan & Indikator Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'F2C2.1',
                                'name' => 'Indikator Pencapaian Kompetensi',
                                'indicators' => [
                                    ['F2I2.1.1', 'Indikator dirumuskan dengan kata kerja operasional yang terukur'],
                                    ['F2I2.1.2', 'Indikator mencakup ranah kognitif, afektif, dan psikomotorik'],
                                ],
                            ],
                            [
                                'code' => 'F2C2.2',
                                'name' => 'Rumusan Tujuan Pembelajaran',
                                'indicators' => [
                                    ['F2I2.2.1', 'Tujuan pembelajaran mengandung unsur ABCD (Audience, Behavior, Condition, Degree)'],
                                    ['F2I2.2.2', 'Tujuan selaras dengan indikator pencapaian kompetensi'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F2C3',
                        'name' => 'Materi & Metode',
                        'subcriteria' => [
                            [
                                'code' => 'F2C3.1',
                                'name' => 'Kesesuaian Materi Ajar',
                                'indicators' => [
                                    ['F2I3.1.1', 'Materi sesuai dengan KD dan indikator'],
                                    ['F2I3.1.2', 'Materi disusun secara sistematis dari sederhana ke kompleks'],
                                ],
                            ],
                            [
                                'code' => 'F2C3.2',
                                'name' => 'Kesesuaian Metode Pembelajaran',
                                'indicators' => [
                                    ['F2I3.2.1', 'Metode sesuai dengan karakteristik materi dan peserta didik'],
                                    ['F2I3.2.2', 'Metode mendukung pembelajaran aktif dan partisipatif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F2C4',
                        'name' => 'Kegiatan Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'F2C4.1',
                                'name' => 'Pendahuluan',
                                'indicators' => [
                                    ['F2I4.1.1', 'Terdapat kegiatan apersepsi dan motivasi'],
                                    ['F2I4.1.2', 'Penyampaian tujuan pembelajaran dan cakupan materi'],
                                ],
                            ],
                            [
                                'code' => 'F2C4.2',
                                'name' => 'Kegiatan Inti',
                                'indicators' => [
                                    ['F2I4.2.1', 'Langkah pembelajaran menggunakan pendekatan saintifik / model pembelajaran aktif'],
                                    ['F2I4.2.2', 'Kegiatan inti memfasilitasi eksplorasi, elaborasi, dan konfirmasi'],
                                ],
                            ],
                            [
                                'code' => 'F2C4.3',
                                'name' => 'Penutup',
                                'indicators' => [
                                    ['F2I4.3.1', 'Terdapat refleksi dan rangkuman pembelajaran'],
                                    ['F2I4.3.2', 'Terdapat rencana tindak lanjut (tugas/remedial/pengayaan)'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F2C5',
                        'name' => 'Penilaian & Sumber Belajar',
                        'subcriteria' => [
                            [
                                'code' => 'F2C5.1',
                                'name' => 'Instrumen Penilaian',
                                'indicators' => [
                                    ['F2I5.1.1', 'Instrumen penilaian sesuai dengan indikator pencapaian'],
                                    ['F2I5.1.2', 'Tersedia rubrik/pedoman penskoran yang jelas'],
                                ],
                            ],
                            [
                                'code' => 'F2C5.2',
                                'name' => 'Remedial & Pengayaan',
                                'indicators' => [
                                    ['F2I5.2.1', 'Terdapat rencana program remedial'],
                                    ['F2I5.2.2', 'Terdapat rencana program pengayaan'],
                                ],
                            ],
                            [
                                'code' => 'F2C5.3',
                                'name' => 'Sumber dan Media Belajar',
                                'indicators' => [
                                    ['F2I5.3.1', 'Sumber belajar relevan dan bervariasi'],
                                    ['F2I5.3.2', 'Media pembelajaran mendukung pencapaian tujuan'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // FORM 3 — Supervisi Pelaksanaan Pembelajaran
            // ─────────────────────────────────────────────────────────
            [
                'set_name'    => 'Kriteria Supervisi Pelaksanaan Pembelajaran',
                'goal_code'   => 'G3',
                'goal_name'   => 'Supervisi Pelaksanaan Pembelajaran',
                'template'    => 'Form 3 – Supervisi Pelaksanaan Pembelajaran',
                'description' => 'Instrumen supervisi pelaksanaan KBM berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria'    => [
                    [
                        'code' => 'F3C1',
                        'name' => 'Kegiatan Pendahuluan',
                        'subcriteria' => [
                            [
                                'code' => 'F3C1.1',
                                'name' => 'Apersepsi & Motivasi',
                                'indicators' => [
                                    ['F3I1.1.1', 'Guru mengaitkan materi sebelumnya dengan materi yang akan dipelajari'],
                                    ['F3I1.1.2', 'Guru memberikan motivasi dan menggali pengetahuan awal siswa'],
                                ],
                            ],
                            [
                                'code' => 'F3C1.2',
                                'name' => 'Orientasi Pembelajaran',
                                'indicators' => [
                                    ['F3I1.2.1', 'Guru menyampaikan tujuan dan langkah-langkah pembelajaran'],
                                    ['F3I1.2.2', 'Guru mengkondisikan kesiapan belajar peserta didik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C2',
                        'name' => 'Penguasaan Materi',
                        'subcriteria' => [
                            [
                                'code' => 'F3C2.1',
                                'name' => 'Penguasaan Kelas & Materi',
                                'indicators' => [
                                    ['F3I2.1.1', 'Guru menguasai materi pelajaran secara luas dan mendalam'],
                                    ['F3I2.1.2', 'Guru mengaitkan materi dengan konteks kehidupan nyata'],
                                    ['F3I2.1.3', 'Guru menguasai kelas dengan baik dan kondusif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C3',
                        'name' => 'Strategi & Pendekatan Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'F3C3.1',
                                'name' => 'Pendekatan Saintifik (5M)',
                                'indicators' => [
                                    ['F3I3.1.1', 'Guru memfasilitasi kegiatan mengamati, menanya, mencoba, menalar, dan mengkomunikasikan'],
                                    ['F3I3.1.2', 'Guru menggunakan model/strategi yang sesuai dengan tujuan pembelajaran'],
                                ],
                            ],
                            [
                                'code' => 'F3C3.2',
                                'name' => 'Variasi Metode',
                                'indicators' => [
                                    ['F3I3.2.1', 'Guru menggunakan metode yang bervariasi dan tidak monoton'],
                                    ['F3I3.2.2', 'Guru menyesuaikan metode dengan karakteristik peserta didik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C4',
                        'name' => 'Media & Sumber Belajar',
                        'subcriteria' => [
                            [
                                'code' => 'F3C4.1',
                                'name' => 'Pemanfaatan Media & Sumber',
                                'indicators' => [
                                    ['F3I4.1.1', 'Guru menggunakan media yang relevan dan menarik'],
                                    ['F3I4.1.2', 'Guru memanfaatkan sumber belajar yang bervariasi'],
                                    ['F3I4.1.3', 'Guru menggunakan teknologi/alat peraga secara efektif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C5',
                        'name' => 'Keterlibatan Peserta Didik',
                        'subcriteria' => [
                            [
                                'code' => 'F3C5.1',
                                'name' => 'Partisipasi Siswa',
                                'indicators' => [
                                    ['F3I5.1.1', 'Peserta didik terlibat aktif dalam proses pembelajaran'],
                                    ['F3I5.1.2', 'Guru mendorong interaksi antar peserta didik (diskusi/kerja kelompok)'],
                                    ['F3I5.1.3', 'Guru memberikan kesempatan bertanya dan berpendapat'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C6',
                        'name' => 'Penilaian Autentik',
                        'subcriteria' => [
                            [
                                'code' => 'F3C6.1',
                                'name' => 'Pelaksanaan Penilaian Proses',
                                'indicators' => [
                                    ['F3I6.1.1', 'Guru melaksanakan penilaian selama proses pembelajaran berlangsung'],
                                    ['F3I6.1.2', 'Guru menggunakan teknik penilaian yang sesuai (observasi, unjuk kerja, portofolio)'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F3C7',
                        'name' => 'Kegiatan Penutup',
                        'subcriteria' => [
                            [
                                'code' => 'F3C7.1',
                                'name' => 'Refleksi & Tindak Lanjut',
                                'indicators' => [
                                    ['F3I7.1.1', 'Guru melakukan refleksi bersama peserta didik terhadap pembelajaran'],
                                    ['F3I7.1.2', 'Guru memberikan umpan balik dan simpulan materi'],
                                    ['F3I7.1.3', 'Guru menyampaikan rencana tindak lanjut (tugas/materi berikutnya)'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // FORM 4 — Supervisi Penilaian
            // ─────────────────────────────────────────────────────────
            [
                'set_name'    => 'Kriteria Supervisi Penilaian',
                'goal_code'   => 'G4',
                'goal_name'   => 'Supervisi Penilaian Hasil Belajar',
                'template'    => 'Form 4 – Supervisi Penilaian',
                'description' => 'Instrumen supervisi penilaian hasil belajar berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria'    => [
                    [
                        'code' => 'F4C1',
                        'name' => 'Perencanaan Penilaian',
                        'subcriteria' => [
                            [
                                'code' => 'F4C1.1',
                                'name' => 'Kisi-Kisi & Perencanaan',
                                'indicators' => [
                                    ['F4I1.1.1', 'Tersedia kisi-kisi soal yang sesuai dengan KD dan indikator'],
                                    ['F4I1.1.2', 'Terdapat perencanaan jadwal PH, PTS, PAS, dan PAT'],
                                    ['F4I1.1.3', 'Teknik penilaian tercantum dalam RPP'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F4C2',
                        'name' => 'Penilaian Pengetahuan',
                        'subcriteria' => [
                            [
                                'code' => 'F4C2.1',
                                'name' => 'Tes Tulis, Lisan & Penugasan',
                                'indicators' => [
                                    ['F4I2.1.1', 'Soal tes tulis memenuhi kaidah penulisan soal yang baik'],
                                    ['F4I2.1.2', 'Tes lisan dilaksanakan dengan pedoman yang jelas'],
                                    ['F4I2.1.3', 'Penugasan terstruktur dan relevan dengan materi'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F4C3',
                        'name' => 'Penilaian Keterampilan',
                        'subcriteria' => [
                            [
                                'code' => 'F4C3.1',
                                'name' => 'Unjuk Kerja & Proyek',
                                'indicators' => [
                                    ['F4I3.1.1', 'Tersedia rubrik penilaian unjuk kerja/praktik'],
                                    ['F4I3.1.2', 'Penilaian proyek dilaksanakan dengan kriteria yang jelas'],
                                    ['F4I3.1.3', 'Penilaian portofolio terdokumentasi dengan baik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F4C4',
                        'name' => 'Penilaian Sikap',
                        'subcriteria' => [
                            [
                                'code' => 'F4C4.1',
                                'name' => 'Observasi Sikap',
                                'indicators' => [
                                    ['F4I4.1.1', 'Guru melakukan observasi sikap spiritual dan sosial secara berkala'],
                                    ['F4I4.1.2', 'Tersedia jurnal penilaian sikap yang terisi konsisten'],
                                    ['F4I4.1.3', 'Penilaian diri dan penilaian antar teman dilaksanakan'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F4C5',
                        'name' => 'Pengolahan & Pelaporan Nilai',
                        'subcriteria' => [
                            [
                                'code' => 'F4C5.1',
                                'name' => 'Analisis Hasil & Bank Soal',
                                'indicators' => [
                                    ['F4I5.1.1', 'Guru melakukan analisis butir soal (validitas, reliabilitas, daya pembeda)'],
                                    ['F4I5.1.2', 'Hasil analisis digunakan untuk perbaikan instrumen (bank soal)'],
                                    ['F4I5.1.3', 'Nilai diolah secara transparan dan dilaporkan tepat waktu'],
                                ],
                            ],
                            [
                                'code' => 'F4C5.2',
                                'name' => 'Pelaporan ke Orang Tua & Sekolah',
                                'indicators' => [
                                    ['F4I5.2.1', 'Laporan hasil belajar disampaikan kepada orang tua/wali'],
                                    ['F4I5.2.2', 'Data nilai terdokumentasi dalam sistem informasi sekolah'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'F4C6',
                        'name' => 'Remedial & Pengayaan',
                        'subcriteria' => [
                            [
                                'code' => 'F4C6.1',
                                'name' => 'Program Remedial',
                                'indicators' => [
                                    ['F4I6.1.1', 'Guru melaksanakan program remedial bagi peserta didik yang belum tuntas'],
                                    ['F4I6.1.2', 'Terdapat dokumentasi pelaksanaan dan hasil remedial'],
                                ],
                            ],
                            [
                                'code' => 'F4C6.2',
                                'name' => 'Program Pengayaan',
                                'indicators' => [
                                    ['F4I6.2.1', 'Guru melaksanakan program pengayaan bagi peserta didik yang sudah tuntas'],
                                    ['F4I6.2.2', 'Terdapat dokumentasi pelaksanaan dan hasil pengayaan'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /* ================================================================
     *  RUN
     * ================================================================ */

    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        $admin       = User::where('email', 'admin@demo.local')->first();

        if (!$institution || !$admin) {
            $this->command?->error('❌ Jalankan DemoUsersSeeder terlebih dahulu.');
            return;
        }

        // Ensure scoring scale 1–4 exists
        $scale = $this->ensureScoringScale($institution);

        // Ensure assessment period exists
        $period = AssessmentPeriod::where('institution_id', $institution->id)
            ->where('meta->demo', true)
            ->first();

        if (!$period) {
            $year1 = (int) now()->format('Y');
            $year2 = $year1 + 1;
            $period = AssessmentPeriod::create([
                'institution_id'   => $institution->id,
                'name'             => "Semester Ganjil {$year1}/{$year2} (Demo)",
                'academic_year'    => "{$year1}/{$year2}",
                'semester'         => 'ganjil',
                'scoring_open_at'  => now()->subDays(3),
                'scoring_close_at' => now()->addDays(30),
                'status'           => 'open',
                'meta'             => ['demo' => true],
            ]);
        }

        foreach ($this->formDefinitions() as $def) {
            $this->seedOneForm($institution, $admin, $scale, $period, $def);
        }

        $this->command?->info('✅ Form 2, 3, 4 Supervisi berhasil di-seed!');
    }

    /* ================================================================
     *  SEED ONE FORM (criteria set + template + AHP)
     * ================================================================ */

    private function seedOneForm(
        Institution      $institution,
        User             $admin,
        ScoringScale     $scale,
        AssessmentPeriod $period,
        array            $def,
    ): void {
        // Skip if already seeded
        if (CriteriaSet::where('institution_id', $institution->id)->where('name', $def['set_name'])->exists()) {
            $this->command?->warn("⏭  {$def['set_name']} sudah ada, dilewati.");
            return;
        }

        DB::transaction(function () use ($institution, $admin, $scale, $period, $def) {

            // ── 1. Criteria Set ──────────────────────────────────────
            $set = CriteriaSet::create([
                'institution_id' => $institution->id,
                'name'           => $def['set_name'],
                'version'        => 1,
                'is_active'      => true,
                'created_by'     => $admin->id,
                'meta'           => ['demo' => true, 'form_type' => $def['goal_code']],
            ]);

            // ── 2. Goal node ─────────────────────────────────────────
            $goal = CriteriaNode::create([
                'criteria_set_id' => $set->id,
                'parent_id'       => null,
                'node_type'       => 'goal',
                'code'            => $def['goal_code'],
                'name'            => $def['goal_name'],
                'description'     => $def['description'],
                'sort_order'      => 0,
                'is_active'       => true,
                'meta'            => ['demo' => true],
            ]);

            // ── 3. Criteria → Subcriteria → Indicator nodes ─────────
            $criteriaNodes = [];
            $criteriaSort  = 1;

            foreach ($def['criteria'] as $cDef) {
                $cNode = CriteriaNode::create([
                    'criteria_set_id' => $set->id,
                    'parent_id'       => $goal->id,
                    'node_type'       => 'criteria',
                    'code'            => $cDef['code'],
                    'name'            => $cDef['name'],
                    'sort_order'      => $criteriaSort++,
                    'is_active'       => true,
                    'meta'            => ['demo' => true],
                ]);
                $criteriaNodes[] = $cNode;

                $subSort = 1;
                foreach ($cDef['subcriteria'] as $scDef) {
                    $scNode = CriteriaNode::create([
                        'criteria_set_id' => $set->id,
                        'parent_id'       => $cNode->id,
                        'node_type'       => 'subcriteria',
                        'code'            => $scDef['code'],
                        'name'            => $scDef['name'],
                        'sort_order'      => $subSort++,
                        'is_active'       => true,
                        'meta'            => ['demo' => true],
                    ]);

                    $indSort = 1;
                    foreach ($scDef['indicators'] as [$iCode, $iName]) {
                        CriteriaNode::create([
                            'criteria_set_id' => $set->id,
                            'parent_id'       => $scNode->id,
                            'node_type'       => 'indicator',
                            'code'            => $iCode,
                            'name'            => $iName,
                            'sort_order'      => $indSort++,
                            'is_active'       => true,
                            'meta'            => ['demo' => true],
                        ]);
                    }
                }
            }

            // ── 4. KPI Form Template + Version ──────────────────────
            $template = KpiFormTemplate::create([
                'institution_id'           => $institution->id,
                'name'                     => $def['template'],
                'description'              => $def['description'],
                'criteria_set_id'          => $set->id,
                'default_scoring_scale_id' => $scale->id,
                'created_by'               => $admin->id,
                'meta'                     => ['demo' => true],
            ]);

            $version = KpiFormVersion::create([
                'template_id'  => $template->id,
                'version'      => 1,
                'status'       => 'published',
                'published_at' => now(),
                'locked_at'    => null,
                'created_by'   => $admin->id,
                'meta'         => ['demo' => true],
            ]);

            // ── 5. KPI Form Sections & Items ────────────────────────
            $sectionOrder = 1;
            foreach ($criteriaNodes as $cNode) {
                $section = KpiFormSection::create([
                    'form_version_id'  => $version->id,
                    'criteria_node_id' => $cNode->id,
                    'title'            => $cNode->name,
                    'description'      => null,
                    'sort_order'       => $sectionOrder++,
                    'meta'             => ['demo' => true],
                ]);

                $subcriteria = CriteriaNode::where('parent_id', $cNode->id)
                    ->where('node_type', 'subcriteria')
                    ->orderBy('sort_order')
                    ->get();

                $itemOrder = 1;
                foreach ($subcriteria as $sub) {
                    $indicators = CriteriaNode::where('parent_id', $sub->id)
                        ->where('node_type', 'indicator')
                        ->orderBy('sort_order')
                        ->get();

                    foreach ($indicators as $ind) {
                        KpiFormItem::create([
                            'section_id'       => $section->id,
                            'criteria_node_id' => $ind->id,
                            'label'            => "{$sub->name}: {$ind->name}",
                            'help_text'        => 'Skor 1 (kurang) – 2 (cukup) – 3 (baik) – 4 (sangat baik).',
                            'field_type'       => 'numeric',
                            'is_required'      => true,
                            'min_value'        => 1,
                            'max_value'        => 4,
                            'scoring_scale_id' => $scale->id,
                            'default_value'    => null,
                            'sort_order'       => $itemOrder++,
                            'meta'             => ['demo' => true],
                        ]);
                    }
                }
            }

            // ── 6. KPI Form Assignment (link to period) ─────────────
            $assignment = KpiFormAssignment::create([
                'assessment_period_id' => $period->id,
                'form_version_id'      => $version->id,
                'status'               => 'active',
                'assigned_at'          => now(),
                'locked_at'            => null,
                'assigned_by'          => $admin->id,
                'meta'                 => ['demo' => true],
            ]);

            // Sync assessors, teachers, groups if available
            $assessors = \App\Models\AssessorProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $teachers  = \App\Models\TeacherProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $groups    = \App\Models\TeacherGroup::where('institution_id', $institution->id)->get();

            if ($assessors->isNotEmpty()) {
                $assignment->assessors()->sync($assessors->pluck('id')->all());
            }
            if ($teachers->isNotEmpty()) {
                $assignment->teachers()->sync($teachers->pluck('id')->all());
            }
            if ($groups->isNotEmpty()) {
                $assignment->teacherGroups()->sync($groups->pluck('id')->all());
            }

            // ── 7. AHP Model + Weights + Comparisons ────────────────
            $ahpModel = AhpModel::create([
                'assessment_period_id' => $period->id,
                'criteria_set_id'      => $set->id,
                'status'               => 'finalized',
                'consistency_ratio'    => round(mt_rand(1, 8) / 100, 2), // CR 0.01–0.08
                'finalized_at'         => now(),
                'created_by'           => $admin->id,
                'meta'                 => ['demo' => true, 'form' => $def['goal_code']],
            ]);

            // Criteria-level weights
            $critWeights = $this->randomNormalizedWeights(count($criteriaNodes));
            foreach ($criteriaNodes as $idx => $cNode) {
                AhpWeight::create([
                    'ahp_model_id'     => $ahpModel->id,
                    'criteria_node_id' => $cNode->id,
                    'parent_node_id'   => $goal->id,
                    'level'            => 'criteria',
                    'weight'           => $critWeights[$idx],
                    'meta'             => ['demo' => true],
                ]);
            }
            $this->seedComparisons($ahpModel->id, $goal->id, $criteriaNodes, $critWeights, $admin->id);

            // Subcriteria-level weights
            foreach ($criteriaNodes as $cIdx => $cNode) {
                $subs = CriteriaNode::where('parent_id', $cNode->id)
                    ->where('node_type', 'subcriteria')
                    ->orderBy('sort_order')
                    ->get();

                if ($subs->isEmpty()) {
                    continue;
                }

                $subWeights = $this->randomNormalizedWeights($subs->count());
                foreach ($subs as $sIdx => $sNode) {
                    AhpWeight::create([
                        'ahp_model_id'     => $ahpModel->id,
                        'criteria_node_id' => $sNode->id,
                        'parent_node_id'   => $cNode->id,
                        'level'            => 'subcriteria',
                        'weight'           => $subWeights[$sIdx],
                        'meta'             => [
                            'demo'          => true,
                            'global_weight' => (float) $critWeights[$cIdx] * (float) $subWeights[$sIdx],
                        ],
                    ]);
                }
                $this->seedComparisons($ahpModel->id, $cNode->id, $subs->all(), $subWeights, $admin->id);
            }

            $this->command?->info("  ✔ {$def['template']} — criteria set, form, AHP seeded.");
        });
    }

    /* ================================================================
     *  HELPERS
     * ================================================================ */

    private function ensureScoringScale(Institution $institution): ScoringScale
    {
        $scale = ScoringScale::where('institution_id', $institution->id)
            ->where('name', 'Scale 1-4')
            ->first();

        if ($scale) {
            return $scale;
        }

        $scale = ScoringScale::create([
            'institution_id' => $institution->id,
            'name'           => 'Scale 1-4',
            'scale_type'     => 'numeric',
            'min_value'      => 1,
            'max_value'      => 4,
            'step'           => 1,
        ]);

        foreach ([
            [1, 'Kurang',       'Belum memenuhi standar minimal.'],
            [2, 'Cukup',        'Memenuhi sebagian standar.'],
            [3, 'Baik',         'Memenuhi standar yang ditetapkan.'],
            [4, 'Sangat Baik',  'Melampaui standar yang ditetapkan.'],
        ] as [$v, $label, $desc]) {
            ScoringScaleOption::create([
                'scoring_scale_id' => $scale->id,
                'value'            => (string) $v,
                'label'            => $label,
                'description'      => $desc,
                'score_value'      => $v,
                'sort_order'       => $v,
            ]);
        }

        return $scale;
    }

    /** @return float[] */
    private function randomNormalizedWeights(int $n): array
    {
        if ($n <= 0) return [];

        $raw = [];
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $v     = mt_rand(10, 100) / 100;
            $raw[] = $v;
            $sum  += $v;
        }

        return array_map(fn ($v) => round($v / $sum, 12), $raw);
    }

    /**
     * @param CriteriaNode[]|array $nodes
     * @param float[]              $weights
     */
    private function seedComparisons(string $ahpModelId, ?string $parentNodeId, array $nodes, array $weights, string $createdBy): void
    {
        $nodes = array_values($nodes); // reindex
        $count = count($nodes);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $ratio = $weights[$i] / max($weights[$j], 1e-12);
                $ratio = max(1 / 9, min(9, $ratio));

                AhpComparison::create([
                    'ahp_model_id'   => $ahpModelId,
                    'parent_node_id' => $parentNodeId,
                    'node_a_id'      => $nodes[$i]->id,
                    'node_b_id'      => $nodes[$j]->id,
                    'value'          => round($ratio, 6),
                    'created_by'     => $createdBy,
                ]);
            }
        }
    }
}
