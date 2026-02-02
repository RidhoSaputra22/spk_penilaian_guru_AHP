@extends('layouts.admin')

@section('title', 'Preview Form - ' . $template->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Preview: {{ $template->name }}</h4>
                        <small class="text-muted">
                            Version: {{ $latestVersion ? $latestVersion->version_number : '1.0' }} |
                            Status: {{ $latestVersion ? ucfirst($latestVersion->status) : 'Draft' }}
                        </small>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @if($latestVersion && $latestVersion->sections()->exists() && $latestVersion->status === 'draft')
                        <form action="{{ route('admin.kpi-forms.publish', $template) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Publish form ini? Form yang sudah dipublish tidak bisa diedit.')">
                                <i class="fas fa-rocket"></i> Publish
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('admin.kpi-forms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($latestVersion && $latestVersion->sections()->exists())
                        <div class="form-preview">
                            <!-- Form Header -->
                            <div class="mb-4 p-4 bg-light rounded">
                                <h2 class="mb-2">{{ $template->name }}</h2>
                                @if($template->description)
                                <p class="text-muted mb-0">{{ $template->description }}</p>
                                @endif
                                <div class="mt-2">
                                    <span class="badge bg-info">Version {{ $latestVersion->version_number }}</span>
                                    <span class="badge bg-{{ $latestVersion->status === 'published' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($latestVersion->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Form Sections -->
                            @foreach($latestVersion->sections()->orderBy('sort_order')->get() as $section)
                            <div class="form-section mb-4">
                                <div class="section-header mb-3">
                                    <h4 class="text-primary">{{ $section->title }}</h4>
                                    @if($section->description)
                                    <p class="text-muted mb-0">{{ $section->description }}</p>
                                    @endif
                                </div>

                                <div class="section-items">
                                    @foreach($section->items()->orderBy('sort_order')->get() as $item)
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">
                                            {{ $item->title }}
                                            @if($item->is_required)
                                            <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @if($item->description)
                                        <small class="form-text text-muted d-block mb-2">{{ $item->description }}</small>
                                        @endif

                                        @switch($item->type)
                                            @case('text')
                                                <input type="text" class="form-control" placeholder="Enter {{ strtolower($item->title) }}" disabled>
                                                @break

                                            @case('textarea')
                                                <textarea class="form-control" rows="4" placeholder="Enter {{ strtolower($item->title) }}" disabled></textarea>
                                                @break

                                            @case('number')
                                                <input type="number" class="form-control" placeholder="Enter {{ strtolower($item->title) }}" disabled>
                                                @break

                                            @case('select')
                                                <select class="form-select" disabled>
                                                    <option value="">-- Pilih {{ $item->title }} --</option>
                                                    @foreach($item->options()->orderBy('sort_order')->get() as $option)
                                                    <option value="{{ $option->option_value }}">{{ $option->option_text }}</option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('radio')
                                                <div class="form-check-group">
                                                    @foreach($item->options()->orderBy('sort_order')->get() as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                               name="item_{{ $item->id }}"
                                                               id="item_{{ $item->id }}_{{ $option->id }}"
                                                               value="{{ $option->option_value }}" disabled>
                                                        <label class="form-check-label" for="item_{{ $item->id }}_{{ $option->id }}">
                                                            {{ $option->option_text }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('checkbox')
                                                <div class="form-check-group">
                                                    @foreach($item->options()->orderBy('sort_order')->get() as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="item_{{ $item->id }}_{{ $option->id }}"
                                                               value="{{ $option->option_value }}" disabled>
                                                        <label class="form-check-label" for="item_{{ $item->id }}_{{ $option->id }}">
                                                            {{ $option->option_text }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('file')
                                                <input type="file" class="form-control" disabled>
                                                <small class="form-text text-muted">
                                                    @if($item->metadata && isset($item->metadata['accepted_files']))
                                                        Accepted files: {{ $item->metadata['accepted_files'] }}
                                                    @endif
                                                    @if($item->metadata && isset($item->metadata['max_size']))
                                                        | Max size: {{ $item->metadata['max_size'] }}MB
                                                    @endif
                                                </small>
                                                @break

                                            @case('date')
                                                <input type="date" class="form-control" disabled>
                                                @break

                                            @case('email')
                                                <input type="email" class="form-control" placeholder="Enter email address" disabled>
                                                @break

                                            @case('url')
                                                <input type="url" class="form-control" placeholder="Enter URL" disabled>
                                                @break

                                            @default
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Unknown field type: {{ $item->type }}
                                                </div>
                                        @endswitch

                                        @if($item->help_text)
                                        <small class="form-text text-info">
                                            <i class="fas fa-info-circle"></i> {{ $item->help_text }}
                                        </small>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach

                            <!-- Form Actions Preview -->
                            <div class="form-actions mt-4 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        This is a preview of how the form will appear to users.
                                    </small>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-secondary" disabled>
                                            <i class="fas fa-save"></i> Draft
                                        </button>
                                        <button type="button" class="btn btn-primary" disabled>
                                            <i class="fas fa-paper-plane"></i> Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $latestVersion->sections()->count() }}</h5>
                                        <p class="card-text">Sections</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">
                                            {{ $latestVersion->sections()->withCount('items')->get()->sum('items_count') }}
                                        </h5>
                                        <p class="card-text">Form Items</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">
                                            {{ $latestVersion->sections()->with('items')->get()->flatMap->items->where('is_required', true)->count() }}
                                        </h5>
                                        <p class="card-text">Required Fields</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $latestVersion->status === 'published' ? 'Yes' : 'No' }}</h5>
                                        <p class="card-text">Published</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-file-alt fa-3x"></i>
                            <h4 class="mt-3">No Form Content</h4>
                            <p>This form template doesn't have any sections yet.</p>
                            <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Start Building
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-preview {
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    background: #fff;
}

.section-header h4 {
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.form-check-group .form-check {
    margin-bottom: 0.5rem;
}

.form-check-group .form-check:last-child {
    margin-bottom: 0;
}

.form-group {
    position: relative;
}

.form-group input:disabled,
.form-group select:disabled,
.form-group textarea:disabled {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

.form-actions {
    border: 1px dashed #dee2e6;
}

.card.bg-primary,
.card.bg-info,
.card.bg-success,
.card.bg-warning {
    border: none;
}

.card.bg-primary .card-title,
.card.bg-info .card-title,
.card.bg-success .card-title,
.card.bg-warning .card-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}
</style>
@endpush
