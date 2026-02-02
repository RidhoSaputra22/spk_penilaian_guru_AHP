
ErrorException
resources/views/admin/ahp/index.blade.php:101

Attempt to read property "name" on null
LARAVEL 12.49.0
PHP 8.5.2
UNHANDLED
CODE 0
500
GET
http://127.0.0.1:8000/admin/ahp
Exception trace

resources/views/admin/ahp/index.blade.php

resources/views/admin/ahp/index.blade.php:101

96                                </thead>
97                                <tbody>
98                                    @forelse($comparisons ?? [] as $comparison)
99                                        <tr>
100                                            <td class="font-medium">
101                                                {{ $comparison['node_a']->name }}
102                                            </td>
103                                            <td>
104                                                <div class="flex items-center justify-center gap-2">
105                                                    @php
106                                                        $scales = [9, 7, 
