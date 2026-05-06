@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 pt-3">
    <!-- Back Button -->
    <a href="{{ route('ujian.detail') }}" class="btn btn-sm btn-back mb-2">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>
<!-- React root element -->
<div id="workspace-root"></div>
@endsection
