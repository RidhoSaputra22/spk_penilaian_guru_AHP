<x-layouts.admin>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Detail Hasil Penilaian</h1>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Hasil Penilaian</h5>

                        <dl class="row">
                            <dt class="col-sm-3">ID</dt>
                            <dd class="col-sm-9">{{ $result->id }}</dd>

                            <dt class="col-sm-3">Final Score</dt>
                            <dd class="col-sm-9">{{ $result->final_score ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">Ranking</dt>
                            <dd class="col-sm-9">{{ $result->ranking ?? 'N/A' }}</dd>

                            <dt class="col-sm-3">Dibuat</dt>
                            <dd class="col-sm-9">{{ $result->created_at ?? 'N/A' }}</dd>
                        </dl>

                        <h6>Kriteria:</h6>
                        @if(count($rootCriteria) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Kriteria</th>
                                        <th>Score</th>
                                        <th>Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rootCriteria as $item)
                                    <tr>
                                        <td>{{ $item['criterion']['name'] ?? 'N/A' }}</td>
                                        <td>{{ $item['raw_score'] ?? 'N/A' }}</td>
                                        <td>{{ $item['weight'] ?? 'N/A' }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p>Belum ada data kriteria.</p>
                        @endif

                        <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
