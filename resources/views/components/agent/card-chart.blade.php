<div class="card shadow-sm rounded border-0 p-3">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted text-sm">{{ $title }}</div>
            <div class="h5 font-weight-bold">{{ $value }}</div>
        </div>
        <div class="dropdown">
            <i class="fas fa-ellipsis-h text-muted small"></i>
        </div>
    </div>
    <canvas id="{{ $chartId }}" height="50"></canvas>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [...Array({{ count($chartData) }}).keys()],
            datasets: [{
                data: @json($chartData),
                borderColor: '{{ $color ?? "#28a745" }}',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }},
            scales: {
                x: { display: false },
                y: { display: false }
            },
            elements: {
                point: { radius: 0 }
            }
        }
    });
});
</script>
@endpush
