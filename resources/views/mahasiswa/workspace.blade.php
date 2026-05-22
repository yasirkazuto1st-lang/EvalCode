@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 pt-3">
    <!-- Back Button -->
    <a href="{{ route('ujian.detail', $exam->ujian_id) }}" class="btn btn-sm btn-back mb-2">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>
<!-- React root element -->
<div id="workspace-root"></div>

<script>
    window.INITIAL_DATA = {
        exam: @json($exam),
        soal: @json($soal),
        all_soals: @json($exam->soals),
        soal_pdf_url: "{{ $soal->soal_pdf ? asset('storage/' . $soal->soal_pdf) : '' }}",
        base_workspace_url: "{{ url('ujian/'.$exam->ujian_id.'/soal') }}",
        attemptsUsed: {{ $attemptsUsed }},
        remainingSeconds: {{ $remainingSeconds }}
    };
</script>
@endsection
