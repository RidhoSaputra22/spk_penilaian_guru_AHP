@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Detail User</h1>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Detail User: {{ $user->name }}</h5>

                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $user->id }}</dd>

                        <dt class="col-sm-3">Nama</dt>
                        <dd class="col-sm-9">{{ $user->name }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $user->email }}</dd>

                        <dt class="col-sm-3">Dibuat</dt>
                        <dd class="col-sm-9">{{ $user->created_at ?? 'N/A' }}</dd>
                    </dl>

                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Kembali</a>
                    <a href="#" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
