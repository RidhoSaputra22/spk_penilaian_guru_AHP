@extends('layouts.admin')

@section('title', 'Form Builder - ' . $template->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Form Builder: {{ $template->name }}</h4>
                        <small class="text-muted">
                            Version: {{ $version ? $version->version_number : '1.0' }} |
                            Status: {{ $version ? ucfirst($version->status) : 'Draft' }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.kpi-forms.preview', $template) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <a href="{{ route('admin.kpi-forms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <h5>Form Builder</h5>
                            @if($version && $version->sections && $version->sections->count() > 0)
                            <p class="text-success">
                                <i class="fas fa-check-circle"></i>
                                This form has {{ $version->sections->count() }} section(s)
                            </p>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Form builder content will be displayed here. Currently no sections are available.
                            </div>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Available Criteria Sets</h6>
                                </div>
                                <div class="card-body">
                                    @if($criteriaSets && $criteriaSets->count() > 0)
                                    <p class="text-success">{{ $criteriaSets->count() }} criteria set(s) available</p>
                                    @else
                                    <p class="text-muted">No criteria sets available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
