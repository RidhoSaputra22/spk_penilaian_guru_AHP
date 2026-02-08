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
                'set_name' => 'Kriteria Supervisi RPP',
                'goal_code' => 'KSP',
                'goal_name' => 'Supervisi Rencana Pelaksanaan Pembelajaran (RPP)',
                'template' => 'Supervisi RPP',
                'description' => 'Instrumen supervisi RPP berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria' => [
                    [
                        'code' => 'KSP-1',
                        'name' => 'Identitas & Kelengkapan RPP',
                        'subcriteria' => [
                            [
                                'code' => 'KSP-1.1',
                                'name' => 'Kompetensi Inti & Kompetensi Dasar',
                                'indicators' => [
                                    ['KSP-I1.1.1', 'Kesesuaian KI dengan kurikulum yang berlaku'],
                                    ['KSP-I1.1.2', 'Kesesuaian KD dengan silabus'],
                                    ['KSP-I1.1.3', 'Kelengkapan identitas mata pelajaran, kelas, dan alokasi waktu'],
                                ],
                            ],
                            [
                                'code' => 'KSP-1.2',
                                'name' => 'Kelengkapan Komponen RPP',
                                'indicators' => [
                                    ['KSP-I1.2.1', 'Tersedianya seluruh komponen RPP sesuai standar proses'],
                                    ['KSP-I1.2.2', 'Sistematika penyusunan RPP runtut dan logis'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSP-2',
                        'name' => 'Tujuan & Indikator Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'KSP-2.1',
                                'name' => 'Indikator Pencapaian Kompetensi',
                                'indicators' => [
                                    ['KSP-I2.1.1', 'Indikator dirumuskan dengan kata kerja operasional yang terukur'],
                                    ['KSP-I2.1.2', 'Indikator mencakup ranah kognitif, afektif, dan psikomotorik'],
                                ],
                            ],
                            [
                                'code' => 'KSP-2.2',
                                'name' => 'Rumusan Tujuan Pembelajaran',
                                'indicators' => [
                                    ['KSP-I2.2.1', 'Tujuan pembelajaran mengandung unsur ABCD (Audience, Behavior, Condition, Degree)'],
                                    ['KSP-I2.2.2', 'Tujuan selaras dengan indikator pencapaian kompetensi'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSP-3',
                        'name' => 'Materi & Metode',
                        'subcriteria' => [
                            [
                                'code' => 'KSP-3.1',
                                'name' => 'Kesesuaian Materi Ajar',
                                'indicators' => [
                                    ['KSP-I3.1.1', 'Materi sesuai dengan KD dan indikator'],
                                    ['KSP-I3.1.2', 'Materi disusun secara sistematis dari sederhana ke kompleks'],
                                ],
                            ],
                            [
                                'code' => 'KSP-3.2',
                                'name' => 'Kesesuaian Metode Pembelajaran',
                                'indicators' => [
                                    ['KSP-I3.2.1', 'Metode sesuai dengan karakteristik materi dan peserta didik'],
                                    ['KSP-I3.2.2', 'Metode mendukung pembelajaran aktif dan partisipatif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSP-4',
                        'name' => 'Kegiatan Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'KSP-4.1',
                                'name' => 'Pendahuluan',
                                'indicators' => [
                                    ['KSP-I4.1.1', 'Terdapat kegiatan apersepsi dan motivasi'],
                                    ['KSP-I4.1.2', 'Penyampaian tujuan pembelajaran dan cakupan materi'],
                                ],
                            ],
                            [
                                'code' => 'KSP-4.2',
                                'name' => 'Kegiatan Inti',
                                'indicators' => [
                                    ['KSP-I4.2.1', 'Langkah pembelajaran menggunakan pendekatan saintifik / model pembelajaran aktif'],
                                    ['KSP-I4.2.2', 'Kegiatan inti memfasilitasi eksplorasi, elaborasi, dan konfirmasi'],
                                ],
                            ],
                            [
                                'code' => 'KSP-4.3',
                                'name' => 'Penutup',
                                'indicators' => [
                                    ['KSP-I4.3.1', 'Terdapat refleksi dan rangkuman pembelajaran'],
                                    ['KSP-I4.3.2', 'Terdapat rencana tindak lanjut (tugas/remedial/pengayaan)'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSP-5',
                        'name' => 'Penilaian & Sumber Belajar',
                        'subcriteria' => [
                            [
                                'code' => 'KSP-5.1',
                                'name' => 'Instrumen Penilaian',
                                'indicators' => [
                                    ['KSP-I5.1.1', 'Instrumen penilaian sesuai dengan indikator pencapaian'],
                                    ['KSP-I5.1.2', 'Tersedia rubrik/pedoman penskoran yang jelas'],
                                ],
                            ],
                            [
                                'code' => 'KSP-5.2',
                                'name' => 'Remedial & Pengayaan',
                                'indicators' => [
                                    ['KSP-I5.2.1', 'Terdapat rencana program remedial'],
                                    ['KSP-I5.2.2', 'Terdapat rencana program pengayaan'],
                                ],
                            ],
                            [
                                'code' => 'KSP-5.3',
                                'name' => 'Sumber dan Media Belajar',
                                'indicators' => [
                                    ['KSP-I5.3.1', 'Sumber belajar relevan dan bervariasi'],
                                    ['KSP-I5.3.2', 'Media pembelajaran mendukung pencapaian tujuan'],
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
                'set_name' => 'Kriteria Supervisi Pelaksanaan Pembelajaran',
                'goal_code' => 'KSPP',
                'goal_name' => 'Supervisi Pelaksanaan Pembelajaran',
                'template' => 'Supervisi Pelaksanaan Pembelajaran',
                'description' => 'Instrumen supervisi pelaksanaan KBM berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria' => [
                    [
                        'code' => 'KSPP-1',
                        'name' => 'Kegiatan Pendahuluan',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-1.1',
                                'name' => 'Apersepsi & Motivasi',
                                'indicators' => [
                                    ['KSPP-I1.1.1', 'Guru mengaitkan materi sebelumnya dengan materi yang akan dipelajari'],
                                    ['KSPP-I1.1.2', 'Guru memberikan motivasi dan menggali pengetahuan awal siswa'],
                                ],
                            ],
                            [
                                'code' => 'KSPP-1.2',
                                'name' => 'Orientasi Pembelajaran',
                                'indicators' => [
                                    ['KSPP-I1.2.1', 'Guru menyampaikan tujuan dan langkah-langkah pembelajaran'],
                                    ['KSPP-I1.2.2', 'Guru mengkondisikan kesiapan belajar peserta didik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-2',
                        'name' => 'Penguasaan Materi',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-2.1',
                                'name' => 'Penguasaan Kelas & Materi',
                                'indicators' => [
                                    ['KSPP-I2.1.1', 'Guru menguasai materi pelajaran secara luas dan mendalam'],
                                    ['KSPP-I2.1.2', 'Guru mengaitkan materi dengan konteks kehidupan nyata'],
                                    ['KSPP-I2.1.3', 'Guru menguasai kelas dengan baik dan kondusif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-3',
                        'name' => 'Strategi & Pendekatan Pembelajaran',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-3.1',
                                'name' => 'Pendekatan Saintifik (5M)',
                                'indicators' => [
                                    ['KSPP-I3.1.1', 'Guru memfasilitasi kegiatan mengamati, menanya, mencoba, menalar, dan mengkomunikasikan'],
                                    ['KSPP-I3.1.2', 'Guru menggunakan model/strategi yang sesuai dengan tujuan pembelajaran'],
                                ],
                            ],
                            [
                                'code' => 'KSPP-3.2',
                                'name' => 'Variasi Metode',
                                'indicators' => [
                                    ['KSPP-I3.2.1', 'Guru menggunakan metode yang bervariasi dan tidak monoton'],
                                    ['KSPP-I3.2.2', 'Guru menyesuaikan metode dengan karakteristik peserta didik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-4',
                        'name' => 'Media & Sumber Belajar',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-4.1',
                                'name' => 'Pemanfaatan Media & Sumber',
                                'indicators' => [
                                    ['KSPP-I4.1.1', 'Guru menggunakan media yang relevan dan menarik'],
                                    ['KSPP-I4.1.2', 'Guru memanfaatkan sumber belajar yang bervariasi'],
                                    ['KSPP-I4.1.3', 'Guru menggunakan teknologi/alat peraga secara efektif'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-5',
                        'name' => 'Keterlibatan Peserta Didik',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-5.1',
                                'name' => 'Partisipasi Siswa',
                                'indicators' => [
                                    ['KSPP-I5.1.1', 'Peserta didik terlibat aktif dalam proses pembelajaran'],
                                    ['KSPP-I5.1.2', 'Guru mendorong interaksi antar peserta didik (diskusi/kerja kelompok)'],
                                    ['KSPP-I5.1.3', 'Guru memberikan kesempatan bertanya dan berpendapat'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-6',
                        'name' => 'Penilaian Autentik',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-6.1',
                                'name' => 'Pelaksanaan Penilaian Proses',
                                'indicators' => [
                                    ['KSPP-I6.1.1', 'Guru melaksanakan penilaian selama proses pembelajaran berlangsung'],
                                    ['KSPP-I6.1.2', 'Guru menggunakan teknik penilaian yang sesuai (observasi, unjuk kerja, portofolio)'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSPP-7',
                        'name' => 'Kegiatan Penutup',
                        'subcriteria' => [
                            [
                                'code' => 'KSPP-7.1',
                                'name' => 'Refleksi & Tindak Lanjut',
                                'indicators' => [
                                    ['KSPP-I7.1.1', 'Guru melakukan refleksi bersama peserta didik terhadap pembelajaran'],
                                    ['KSPP-I7.1.2', 'Guru memberikan umpan balik dan simpulan materi'],
                                    ['KSPP-I7.1.3', 'Guru menyampaikan rencana tindak lanjut (tugas/materi berikutnya)'],
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
                'set_name' => 'Kriteria Supervisi Penilaian',
                'goal_code' => 'KSN',
                'goal_name' => 'Supervisi Penilaian Hasil Belajar',
                'template' => 'Supervisi Penilaian',
                'description' => 'Instrumen supervisi penilaian hasil belajar berbasis AHP. Asesor menilai 1–4 per indikator.',
                'criteria' => [
                    [
                        'code' => 'KSN-1',
                        'name' => 'Perencanaan Penilaian',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-1.1',
                                'name' => 'Kisi-Kisi & Perencanaan',
                                'indicators' => [
                                    ['KSN-I1.1.1', 'Tersedia kisi-kisi soal yang sesuai dengan KD dan indikator'],
                                    ['KSN-I1.1.2', 'Terdapat perencanaan jadwal PH, PTS, PAS, dan PAT'],
                                    ['KSN-I1.1.3', 'Teknik penilaian tercantum dalam RPP'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSN-2',
                        'name' => 'Penilaian Pengetahuan',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-2.1',
                                'name' => 'Tes Tulis, Lisan & Penugasan',
                                'indicators' => [
                                    ['KSN-I2.1.1', 'Soal tes tulis memenuhi kaidah penulisan soal yang baik'],
                                    ['KSN-I2.1.2', 'Tes lisan dilaksanakan dengan pedoman yang jelas'],
                                    ['KSN-I2.1.3', 'Penugasan terstruktur dan relevan dengan materi'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSN-3',
                        'name' => 'Penilaian Keterampilan',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-3.1',
                                'name' => 'Unjuk Kerja & Proyek',
                                'indicators' => [
                                    ['KSN-I3.1.1', 'Tersedia rubrik penilaian unjuk kerja/praktik'],
                                    ['KSN-I3.1.2', 'Penilaian proyek dilaksanakan dengan kriteria yang jelas'],
                                    ['KSN-I3.1.3', 'Penilaian portofolio terdokumentasi dengan baik'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSN-4',
                        'name' => 'Penilaian Sikap',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-4.1',
                                'name' => 'Observasi Sikap',
                                'indicators' => [
                                    ['KSN-I4.1.1', 'Guru melakukan observasi sikap spiritual dan sosial secara berkala'],
                                    ['KSN-I4.1.2', 'Tersedia jurnal penilaian sikap yang terisi konsisten'],
                                    ['KSN-I4.1.3', 'Penilaian diri dan penilaian antar teman dilaksanakan'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSN-5',
                        'name' => 'Pengolahan & Pelaporan Nilai',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-5.1',
                                'name' => 'Analisis Hasil & Bank Soal',
                                'indicators' => [
                                    ['KSN-I5.1.1', 'Guru melakukan analisis butir soal (validitas, reliabilitas, daya pembeda)'],
                                    ['KSN-I5.1.2', 'Hasil analisis digunakan untuk perbaikan instrumen (bank soal)'],
                                    ['KSN-I5.1.3', 'Nilai diolah secara transparan dan dilaporkan tepat waktu'],
                                ],
                            ],
                            [
                                'code' => 'KSN-5.2',
                                'name' => 'Pelaporan ke Orang Tua & Sekolah',
                                'indicators' => [
                                    ['KSN-I5.2.1', 'Laporan hasil belajar disampaikan kepada orang tua/wali'],
                                    ['KSN-I5.2.2', 'Data nilai terdokumentasi dalam sistem informasi sekolah'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'code' => 'KSN-6',
                        'name' => 'Remedial & Pengayaan',
                        'subcriteria' => [
                            [
                                'code' => 'KSN-6.1',
                                'name' => 'Program Remedial',
                                'indicators' => [
                                    ['KSN-I6.1.1', 'Guru melaksanakan program remedial bagi peserta didik yang belum tuntas'],
                                    ['KSN-I6.1.2', 'Terdapat dokumentasi pelaksanaan dan hasil remedial'],
                                ],
                            ],
                            [
                                'code' => 'KSN-6.2',
                                'name' => 'Program Pengayaan',
                                'indicators' => [
                                    ['KSN-I6.2.1', 'Guru melaksanakan program pengayaan bagi peserta didik yang sudah tuntas'],
                                    ['KSN-I6.2.2', 'Terdapat dokumentasi pelaksanaan dan hasil pengayaan'],
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
        $admin = User::where('email', 'admin@demo.local')->first();

        if (! $institution || ! $admin) {
            $this->command?->error('❌ Jalankan DemoUsersSeeder terlebih dahulu.');

            return;
        }

        // Ensure scoring scale 1–4 exists
        $scale = $this->ensureScoringScale($institution);

        // Ensure assessment period exists
        $period = AssessmentPeriod::where('institution_id', $institution->id)
            ->where('meta->demo', true)
            ->first();

        if (! $period) {
            $year1 = (int) now()->format('Y');
            $year2 = $year1 + 1;
            $period = AssessmentPeriod::create([
                'institution_id' => $institution->id,
                'name' => "Semester Ganjil {$year1}/{$year2} (Demo)",
                'academic_year' => "{$year1}/{$year2}",
                'semester' => 'ganjil',
                'scoring_open_at' => now()->subDays(3),
                'scoring_close_at' => now()->addDays(30),
                'status' => 'open',
                'meta' => ['demo' => true],
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
        Institution $institution,
        User $admin,
        ScoringScale $scale,
        AssessmentPeriod $period,
        array $def,
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
                'name' => $def['set_name'],
                'version' => 1,
                'is_active' => true,
                'created_by' => $admin->id,
                'meta' => ['demo' => true, 'form_type' => $def['goal_code']],
            ]);

            // ── 2. Goal node ─────────────────────────────────────────
            $goal = CriteriaNode::create([
                'criteria_set_id' => $set->id,
                'parent_id' => null,
                'node_type' => 'goal',
                'code' => $def['goal_code'],
                'name' => $def['goal_name'],
                'description' => $def['description'],
                'sort_order' => 0,
                'is_active' => true,
                'meta' => ['demo' => true],
            ]);

            // ── 3. Criteria → Subcriteria → Indicator nodes ─────────
            $criteriaNodes = [];
            $criteriaSort = 1;

            foreach ($def['criteria'] as $cDef) {
                $cNode = CriteriaNode::create([
                    'criteria_set_id' => $set->id,
                    'parent_id' => $goal->id,
                    'node_type' => 'criteria',
                    'code' => $cDef['code'],
                    'name' => $cDef['name'],
                    'sort_order' => $criteriaSort++,
                    'is_active' => true,
                    'meta' => ['demo' => true],
                ]);
                $criteriaNodes[] = $cNode;

                $subSort = 1;
                foreach ($cDef['subcriteria'] as $scDef) {
                    $scNode = CriteriaNode::create([
                        'criteria_set_id' => $set->id,
                        'parent_id' => $cNode->id,
                        'node_type' => 'subcriteria',
                        'code' => $scDef['code'],
                        'name' => $scDef['name'],
                        'sort_order' => $subSort++,
                        'is_active' => true,
                        'meta' => ['demo' => true],
                    ]);

                    $indSort = 1;
                    foreach ($scDef['indicators'] as [$iCode, $iName]) {
                        CriteriaNode::create([
                            'criteria_set_id' => $set->id,
                            'parent_id' => $scNode->id,
                            'node_type' => 'indicator',
                            'code' => $iCode,
                            'name' => $iName,
                            'sort_order' => $indSort++,
                            'is_active' => true,
                            'meta' => ['demo' => true],
                        ]);
                    }
                }
            }

            // ── 4. KPI Form Template + Version ──────────────────────
            $template = KpiFormTemplate::create([
                'institution_id' => $institution->id,
                'name' => $def['template'],
                'description' => $def['description'],
                'criteria_set_id' => $set->id,
                'default_scoring_scale_id' => $scale->id,
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $version = KpiFormVersion::create([
                'template_id' => $template->id,
                'version' => 1,
                'status' => 'published',
                'published_at' => now(),
                'locked_at' => null,
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            // ── 5. KPI Form Sections & Items ────────────────────────
            $sectionOrder = 1;
            foreach ($criteriaNodes as $cNode) {
                $section = KpiFormSection::create([
                    'form_version_id' => $version->id,
                    'criteria_node_id' => $cNode->id,
                    'title' => $cNode->name,
                    'description' => null,
                    'sort_order' => $sectionOrder++,
                    'meta' => ['demo' => true],
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
                            'section_id' => $section->id,
                            'criteria_node_id' => $ind->id,
                            'label' => "{$sub->name}: {$ind->name}",
                            'help_text' => 'Skor 1 (kurang) – 2 (cukup) – 3 (baik) – 4 (sangat baik).',
                            'field_type' => 'numeric',
                            'is_required' => true,
                            'min_value' => 1,
                            'max_value' => 4,
                            'scoring_scale_id' => $scale->id,
                            'default_value' => null,
                            'sort_order' => $itemOrder++,
                            'meta' => ['demo' => true],
                        ]);
                    }
                }
            }

            // ── 6. KPI Form Assignment (link to period) ─────────────
            $assignment = KpiFormAssignment::create([
                'assessment_period_id' => $period->id,
                'form_version_id' => $version->id,
                'status' => 'active',
                'assigned_at' => now(),
                'locked_at' => null,
                'assigned_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            // Sync assessors, teachers, groups if available
            $assessors = \App\Models\AssessorProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $teachers = \App\Models\TeacherProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $groups = \App\Models\TeacherGroup::where('institution_id', $institution->id)->get();

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
                'criteria_set_id' => $set->id,
                'status' => 'finalized',
                'consistency_ratio' => round(mt_rand(1, 8) / 100, 2), // CR 0.01–0.08
                'finalized_at' => now(),
                'created_by' => $admin->id,
                'meta' => ['demo' => true, 'form' => $def['goal_code']],
            ]);

            // Criteria-level weights
            $critWeights = $this->randomNormalizedWeights(count($criteriaNodes));
            foreach ($criteriaNodes as $idx => $cNode) {
                AhpWeight::create([
                    'ahp_model_id' => $ahpModel->id,
                    'criteria_node_id' => $cNode->id,
                    'parent_node_id' => $goal->id,
                    'level' => 'criteria',
                    'weight' => $critWeights[$idx],
                    'meta' => ['demo' => true],
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
                        'ahp_model_id' => $ahpModel->id,
                        'criteria_node_id' => $sNode->id,
                        'parent_node_id' => $cNode->id,
                        'level' => 'subcriteria',
                        'weight' => $subWeights[$sIdx],
                        'meta' => [
                            'demo' => true,
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
            'name' => 'Scale 1-4',
            'scale_type' => 'numeric',
            'min_value' => 1,
            'max_value' => 4,
            'step' => 1,
        ]);

        foreach ([
            [1, 'Kurang',       'Belum memenuhi standar minimal.'],
            [2, 'Cukup',        'Memenuhi sebagian standar.'],
            [3, 'Baik',         'Memenuhi standar yang ditetapkan.'],
            [4, 'Sangat Baik',  'Melampaui standar yang ditetapkan.'],
        ] as [$v, $label, $desc]) {
            ScoringScaleOption::create([
                'scoring_scale_id' => $scale->id,
                'value' => (string) $v,
                'label' => $label,
                'description' => $desc,
                'score_value' => $v,
                'sort_order' => $v,
            ]);
        }

        return $scale;
    }

    /** @return float[] */
    private function randomNormalizedWeights(int $n): array
    {
        if ($n <= 0) {
            return [];
        }

        $raw = [];
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $v = mt_rand(10, 100) / 100;
            $raw[] = $v;
            $sum += $v;
        }

        return array_map(fn ($v) => round($v / $sum, 12), $raw);
    }

    /**
     * @param  CriteriaNode[]|array  $nodes
     * @param  float[]  $weights
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
                    'ahp_model_id' => $ahpModelId,
                    'parent_node_id' => $parentNodeId,
                    'node_a_id' => $nodes[$i]->id,
                    'node_b_id' => $nodes[$j]->id,
                    'value' => round($ratio, 6),
                    'created_by' => $createdBy,
                ]);
            }
        }
    }
}
