@extends('layouts.admin')

@section('title', 'Rapports et Statistiques')

@section('content')
<style>
    .metric-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
        overflow: hidden;
    }
    .metric-card:hover {
        transform: translateY(-2px);
    }
    .chart-container {
        position: relative;
        height: 300px !important;
        width: 100% !important;
    }
    .chart-container canvas {
        max-height: 300px !important;
        max-width: 100% !important;
    }
    .progress-circle {
        width: 100px;
        height: 100px;
    }
    .trend-up {
        color: #28a745;
    }
    .trend-down {
        color: #dc3545;
    }
    .trend-stable {
        color: #ffc107;
    }
    /* Fix for chart responsiveness */
    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
        overflow: hidden;
    }
    .chart-wrapper canvas {
        position: absolute;
        left: 0;
        top: 0;
        pointer-events: none;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i>📊</i> Rapports et Statistiques</h2>
        <p class="text-muted mb-0">Année scolaire {{ $currentYear }}</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i>🖨️</i> Imprimer
        </button>
        <button class="btn btn-outline-success" onclick="exportPDF()">
            <i>📄</i> Exporter PDF
        </button>
        <button class="btn btn-outline-info" onclick="refreshData()">
            <i>🔄</i> Actualiser
        </button>
    </div>
</div>

<!-- Quick Overview Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white text-center">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ number_format($basicStats['total_students']) }}</h3>
                        <p class="mb-0">Étudiants Total</p>
                    </div>
                    <i style="font-size: 2rem; opacity: 0.7;">👥</i>
                </div>
                <div class="mt-2">
                    <small>+{{ $recentActivity['recent_students']->count() }} ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white text-center">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ number_format($basicStats['total_modules']) }}</h3>
                        <p class="mb-0">Modules Total</p>
                    </div>
                    <i style="font-size: 2rem; opacity: 0.7;">📚</i>
                </div>
                <div class="mt-2">
                    <small>{{ $basicStats['active_modules'] }} actifs</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white text-center">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ number_format($performanceMetrics['total_inscriptions']) }}</h3>
                        <p class="mb-0">Inscriptions</p>
                    </div>
                    <i style="font-size: 2rem; opacity: 0.7;">📝</i>
                </div>
                <div class="mt-2">
                    <small>{{ $performanceMetrics['active_inscriptions'] }} actives</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white text-center">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ number_format($basicStats['total_reclamations']) }}</h3>
                        <p class="mb-0">Réclamations</p>
                    </div>
                    <i style="font-size: 2rem; opacity: 0.7;">⚠️</i>
                </div>
                <div class="mt-2">
                    <small>{{ $basicStats['pending_reclamations'] }} en attente</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Analytics Row -->
<div class="row mb-4">
    <!-- Student Distribution by Diploma -->
    <div class="col-md-6 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i>📊</i> Répartition par Diplôme</h5>
                <div class="spinner-border spinner-border-sm text-light d-none" id="diplomaLoader" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="card-body">
                @if($studentStats['by_diploma']->count() > 0)
                    <div class="chart-wrapper">
                        <canvas id="diplomaChart"></canvas>
                    </div>
                    <div class="mt-3">
                        <h6 class="mb-3">Détail par Diplôme:</h6>
                        @foreach($studentStats['by_diploma']->take(6) as $index => $diploma)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background-color: {{ ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'][$index % 6] }}20;">
                            <div class="d-flex align-items-center">
                                <span class="badge me-2" style="background-color: {{ ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'][$index % 6] }};">&nbsp;</span>
                                <span>{{ $diploma->cod_dip ?? 'Non spécifié' }}</span>
                            </div>
                            <div>
                                <span class="badge bg-primary">{{ $diploma->count }}</span>
                                <small class="text-muted ms-2">{{ number_format(($diploma->count / $studentStats['total_students']) * 100, 1) }}%</small>
                            </div>
                        </div>
                        @endforeach
                        @if($studentStats['by_diploma']->count() > 6)
                        <div class="text-center mt-2">
                            <small class="text-muted">+{{ $studentStats['by_diploma']->count() - 6 }} autres diplômes</small>
                        </div>
                        @endif
                        @if($studentStats['without_diploma'] > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded bg-light">
                            <div class="d-flex align-items-center">
                                <span class="badge me-2 bg-secondary">&nbsp;</span>
                                <span class="text-muted">Sans diplôme spécifié</span>
                            </div>
                            <div>
                                <span class="badge bg-secondary">{{ $studentStats['without_diploma'] }}</span>
                                <small class="text-muted ms-2">{{ number_format(($studentStats['without_diploma'] / $studentStats['total_students']) * 100, 1) }}%</small>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i style="font-size: 3rem; color: #6c757d;">📊</i>
                        <h6 class="mt-3 text-muted">Aucune donnée de diplôme disponible</h6>
                        <p class="text-muted small">Les codes de diplôme seront affichés ici une fois disponibles.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notes Performance -->
    <div class="col-md-6 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i>📈</i> Performance Académique</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="progress-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light rounded-circle">
                            <div class="text-center">
                                <h4 class="mb-0 text-success">{{ $noteStats['current_session']['passed_percentage'] }}%</h4>
                                <small>Session Actuelle</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="progress-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light rounded-circle">
                            <div class="text-center">
                                <h4 class="mb-0 text-info">{{ $noteStats['historical']['passed_percentage'] }}%</h4>
                                <small>Historique</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <span>Moyenne générale:</span>
                        <strong>{{ number_format($noteStats['current_session']['average_grade'], 2) }}/20</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Notes saisies:</span>
                        <strong>{{ number_format($noteStats['current_session']['total']) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trends and Analytics -->
<div class="row mb-4">
    <!-- Monthly Trends -->
    <div class="col-md-8 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i>📈</i> Tendances (12 derniers mois)</h5>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="col-md-4 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i>🎯</i> Indicateurs Clés</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Modules par étudiant</span>
                        <span class="badge bg-primary">{{ $performanceMetrics['average_modules_per_student'] }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Temps de réponse réclamations</span>
                        <span class="badge bg-info">{{ $reclamationStats['response_time'] }} jours</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Modules avec ECTS</span>
                        <span class="badge bg-success">{{ $moduleStats['with_ects'] }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>ECTS moyen</span>
                        <span class="badge bg-warning">{{ number_format($moduleStats['average_ects'], 1) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extended Analytics Section -->
<div class="row mb-4">
    <!-- Module Statistics -->
    <div class="col-md-4 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i>📚</i> Statistiques Modules</h5>
            </div>
            <div class="card-body">
                <div class="chart-wrapper" style="height: 250px;">
                    <canvas id="moduleStatusChart"></canvas>
                </div>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-success">{{ $moduleStats['with_ects'] }}</h4>
                            <small>Avec ECTS</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">{{ $moduleStats['without_ects'] }}</h4>
                            <small>Sans ECTS</small>
                        </div>
                    </div>
                    @if($moduleStats['average_ects'] > 0)
                    <div class="text-center mt-2">
                        <span class="badge bg-info">Moyenne ECTS: {{ number_format($moduleStats['average_ects'], 1) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reclamation Analytics -->
    <div class="col-md-4 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i>⚠️</i> Analyse Réclamations</h5>
            </div>
            <div class="card-body">
                <div class="chart-wrapper" style="height: 250px;">
                    <canvas id="reclamationChart"></canvas>
                </div>
                <div class="mt-3">
                    @foreach($reclamationStats['by_status'] as $status)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">{{ $status->status }}</span>
                        <span class="badge bg-{{ $status->status === 'pending' ? 'warning' : ($status->status === 'resolved' ? 'success' : 'secondary') }}">
                            {{ $status->count }}
                        </span>
                    </div>
                    @endforeach
                    @if($reclamationStats['response_time'] > 0)
                    <div class="text-center mt-2">
                        <span class="badge bg-info">Temps moyen: {{ $reclamationStats['response_time'] }} jours</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="col-md-4 mb-4">
        <div class="card metric-card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i>🎯</i> Performance Globale</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="progress-circle mx-auto mb-2 d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px;">
                            <div class="text-center">
                                <h5 class="mb-0 text-success">{{ $noteStats['current_session']['passed_percentage'] }}%</h5>
                                <small>Réussite</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Inscriptions actives:</span>
                        <strong>{{ number_format($performanceMetrics['active_inscriptions']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Modules/Étudiant:</span>
                        <strong>{{ $performanceMetrics['average_modules_per_student'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Moyenne générale:</span>
                        <strong>{{ number_format($noteStats['current_session']['average_grade'], 2) }}/20</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global Chart.js configuration to prevent sizing issues
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;
Chart.defaults.interaction.intersect = false;

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show loader
    const loader = document.getElementById('diplomaLoader');
    if (loader) loader.classList.remove('d-none');

    // Diploma Chart with fixed sizing
    const diplomaCtx = document.getElementById('diplomaChart');
    if (diplomaCtx) {
        // Prepare data for diploma chart
        const diplomaLabels = [
            @foreach($studentStats['by_diploma'] as $diploma)
                '{{ $diploma->cod_dip ?? "Non spécifié" }}',
            @endforeach
            @if($studentStats['without_diploma'] > 0)
                'Sans diplôme'
            @endif
        ];

        const diplomaData = [
            @foreach($studentStats['by_diploma'] as $diploma)
                {{ $diploma->count }},
            @endforeach
            @if($studentStats['without_diploma'] > 0)
                {{ $studentStats['without_diploma'] }}
            @endif
        ];

        const diplomaChart = new Chart(diplomaCtx, {
            type: 'doughnut',
            data: {
                labels: diplomaLabels,
                datasets: [{
                    data: diplomaData,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                        '#FF9999',
                        '#87CEEB',
                        '#DDA0DD',
                        '#98FB98',
                        '#CCCCCC'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 800,
                    onComplete: function() {
                        if (loader) loader.classList.add('d-none');
                    }
                }
            }
        });
    }

    // Module Status Chart with fixed sizing
    const moduleCtx = document.getElementById('moduleStatusChart');
    if (moduleCtx) {
        new Chart(moduleCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($moduleStats['by_status'] as $status)
                        '{{ $status->eta_elp === "A" ? "Actif" : ($status->eta_elp === "I" ? "Inactif" : $status->eta_elp) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Nombre de modules',
                    data: [
                        @foreach($moduleStats['by_status'] as $status)
                            {{ $status->count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107',
                        '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Reclamation Chart with fixed sizing
    const reclamationCtx = document.getElementById('reclamationChart');
    if (reclamationCtx) {
        new Chart(reclamationCtx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($reclamationStats['by_status'] as $status)
                        '{{ ucfirst($status->status) }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($reclamationStats['by_status'] as $status)
                            {{ $status->count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#dc3545',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    }

    // Trend Chart with fixed sizing
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($trendAnalysis as $month)
                        '{{ $month['label'] }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Étudiants ajoutés',
                    data: [
                        @foreach($trendAnalysis as $month)
                            {{ $month['students_added'] }},
                        @endforeach
                    ],
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Réclamations',
                    data: [
                        @foreach($trendAnalysis as $month)
                            {{ $month['reclamations_created'] }},
                        @endforeach
                    ],
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Modules ajoutés',
                    data: [
                        @foreach($trendAnalysis as $month)
                            {{ $month['modules_added'] }},
                        @endforeach
                    ],
                    borderColor: '#4BC0C0',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
});

// Export functions
function exportPDF() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Génération...';
    btn.disabled = true;

    setTimeout(() => {
        alert('Fonctionnalité d\'export PDF en développement. Utilisez l\'impression du navigateur pour le moment.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function refreshData() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Actualisation...';
    btn.disabled = true;

    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Auto-refresh every 5 minutes
setInterval(function() {
    console.log('Auto-refresh des données...');
}, 300000);

// Resize event handler to prevent chart sizing issues
window.addEventListener('resize', function() {
    // Debounce resize events
    clearTimeout(window.resizeTimeout);
    window.resizeTimeout = setTimeout(function() {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }, 100);
});
</script>
@endsection
