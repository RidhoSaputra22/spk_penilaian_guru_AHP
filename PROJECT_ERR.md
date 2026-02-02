
Symfony\Component\Routing\Exception\RouteNotFoundException
vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php:526

Route [admin.periods.close] not defined.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/periods
Exception trace
2 vendor frames

resources/views/admin/periods/index.blade.php

resources/views/admin/periods/index.blade.php:189

184                                                    </button>
185                                                </form>
186                                            </li>
187                                        @elseif($period->status === 'open')
188                                            <li>
189                                                <form method="POST" action="{{ route('admin.periods.close', $period) }}">
190                                                    @csrf
191                                                    @method('PATCH')
192                                                    <button typ


Illuminate\Database\QueryException
vendor/laravel/framework/src/Illuminate/Database/Connection.php:838

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'level' in 'order clause' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: db_spk_guru, SQL: select * from `criteria_nodes` where `criteria_nodes`.`criteria_set_id` in (01kgevze82ajvw3e3bz7q1s6a8) order by `level` asc, `sort_order` asc)
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 42S22
500
GET
http://127.0.0.1:8000/admin/criteria
Exception trace
14 vendor frames

app/Http/Controllers/Admin/CriteriaController.php

app/Http/Controllers/Admin/CriteriaController.php:23

18
19        $criteriaSets = CriteriaSet::with(['nodes' => function($q) {
20            $q->orderBy('level')->orderBy('sort_order');
21        }])
22        ->where('institution_id', $institution?->id)
23        ->get();
24
25        $scoringScales = ScoringScale::with('options')


InvalidArgumentException
vendor/laravel/framework/src/Illuminate/View/FileViewFinder.php:138

View [admin.scoring-scales.index] not found.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/scoring-scales
Exception trace
4 vendor frames

routes/web.php

routes/web.php:122

117    // Activity Logs
118    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
119
120    // Placeholder routes for views
121    Route::get('scoring-scales', function () {
122        return view('admin.scoring-scales.index');
123    })->name('scoring-scales.index');
124
125    Route::get('kpi-assignments', function () {
126        return view('admin.kpi-assignments.index');


Symfony\Component\Routing\Exception\RouteNotFoundException
vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php:526

Route [admin.kpi-forms.edit] not defined.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/kpi-forms
Exception trace
2 vendor frames

resources/views/admin/kpi-forms/index.blade.php

resources/views/admin/kpi-forms/index.blade.php:91

86                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
87                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
88                            </svg>
89                        </label>
90                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
91                            <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">Edit Info</a></li>
92                            <li><a href="{{ route('admin.kpi-forms.clone', $template) }}">Duplikat</a></li>
93                            <li><a href="{{ route('admin.kpi-forms.versions', $template) }}">Riwayat Versi</a></li>
94                            <li><a class="text-error" onclick="document.getElementById('delete-{{ $template->id }}').showModal()">Hapus</a></li>
95                        </ul>



InvalidArgumentException
vendor/laravel/framework/src/Illuminate/View/FileViewFinder.php:138

View [admin.kpi-assignments.index] not found.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/kpi-assignments
Exception trace
4 vendor frames

routes/web.php

routes/web.php:126

121    Route::get('scoring-scales', function () {
122        return view('admin.scoring-scales.index');
123    })->name('scoring-scales.index');
124
125    Route::get('kpi-assignments', function () {
126        return view('admin.kpi-assignments.index');
127    })->name('kpi-assignments.index');
128
129    Route::get('reports', function () {
130        return view('admin.reports.index');



Symfony\Component\Routing\Exception\RouteNotFoundException
vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php:526

Route [admin.ahp.store-comparisons] not defined.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/ahp
Exception trace
2 vendor frames

resources/views/admin/ahp/index.blade.php

resources/views/admin/ahp/index.blade.php:86

81                        <x-ui.alert type="info" class="mb-4">
82                            Bobot sudah di-finalisasi. Tidak dapat mengubah perbandingan.
83                        </x-ui.alert>
84                    @endif
85
86                    <form method="POST" action="{{ route('admin.ahp.store-comparisons', $ahpModel) }}" id="comparison-form">
87                        @csrf
88                        <div class="overflow-x-auto">
89                            <table class="table table-sm">



InvalidArgumentException
vendor/laravel/framework/src/Illuminate/View/FileViewFinder.php:138

View [admin.reports.index] not found.
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/reports
Exception trace
4 vendor frames

routes/web.php

routes/web.php:130

125    Route::get('kpi-assignments', function () {
126        return view('admin.kpi-assignments.index');
127    })->name('kpi-assignments.index');
128
129    Route::get('reports', function () {
130        return view('admin.reports.index');
131    })->name('reports.index');
132


Illuminate\Database\QueryException
vendor/laravel/framework/src/Illuminate/Database/Connection.php:838

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'deactivated_at' in 'where clause' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: db_spk_guru, SQL: select count(*) as aggregate from `teacher_profiles` where exists (select * from `users` where `teacher_profiles`.`user_id` = `users`.`id` and `institution_id` = 01kgevzbse93abrgp51vef8125 and `users`.`deleted_at` is null) and exists (select * from `users` where `teacher_profiles`.`user_id` = `users`.`id` and `deactivated_at` is not null and `users`.`deleted_at` is null) and `teacher_profiles`.`deleted_at` is null)
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 42S22
500
GET
http://127.0.0.1:8000/admin/teachers?group=&search=&status=inactive
Exception trace
10 vendor frames

app/Http/Controllers/Admin/TeacherController.php

app/Http/Controllers/Admin/TeacherController.php:54

49                    $q->whereNotNull('deactivated_at');
50                }
51            });
52        }
53
54        $teachers = $query->latest()->paginate(10)->withQueryString();
55        $groups = TeacherGroup::where('institution_id', $institution?->id)->get();



Illuminate\Database\QueryException
vendor/laravel/framework/src/Illuminate/Database/Connection.php:838

SQLSTATE[42S22]: Column not found: 1054 Unknown column 'deactivated_at' in 'where clause' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: db_spk_guru, SQL: select count(*) as aggregate from `teacher_profiles` where exists (select * from `users` where `teacher_profiles`.`user_id` = `users`.`id` and `institution_id` = 01kgevzbse93abrgp51vef8125 and `users`.`deleted_at` is null) and exists (select * from `teacher_groups` inner join `teacher_group_members` on `teacher_groups`.`id` = `teacher_group_members`.`teacher_group_id` where `teacher_profiles`.`id` = `teacher_group_members`.`teacher_profile_id` and `teacher_groups`.`id` = 01kgevzcg0x9tprcxn2z7wspyc and `teacher_groups`.`deleted_at` is null) and exists (select * from `users` where `teacher_profiles`.`user_id` = `users`.`id` and `deactivated_at` is null and `users`.`deleted_at` is null) and `teacher_profiles`.`deleted_at` is null)
