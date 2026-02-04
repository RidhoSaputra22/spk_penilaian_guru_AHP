<x-layouts.admin>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Hasil Penilaian</h1>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daftar Hasil Penilaian</h5>

                        @if(count($results) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Teacher</th>
                                        <th>Final Score</th>
                                        <th>Ranking</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $result)
                                    <tr>
                                        <td>{{ $result['id'] ?? 'N/A' }}</td>
                                        <td>{{ $result['teacher']['user']['name'] ?? 'N/A' }}</td>
                                        <td>{{ $result['final_score'] ?? 'N/A' }}</td>
                                        <td>{{ $result['ranking'] ?? 'N/A' }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p>Belum ada data hasil penilaian.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>
