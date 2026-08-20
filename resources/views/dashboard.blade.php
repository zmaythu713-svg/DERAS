@extends('layouts.master')

@section('content')
    <style>
        .dash-metrics {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            align-items: stretch;
        }
        @media (min-width: 576px) {
            .dash-metrics { grid-template-columns: repeat(2, 1fr); }
        }
        @media (min-width: 992px) {
            .dash-metrics { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 1200px) {
            .dash-metrics { grid-template-columns: repeat(5, 1fr); }
        }

        .dash-metric-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 52px;
            grid-template-rows: auto 52px;
            column-gap: 14px;
            row-gap: 10px;
            align-items: center;
            min-height: 132px;
            height: auto;
            padding: 18px 20px;
            background: #fff;
            border-radius: 16px;
            border-left: 5px solid #ccc;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-sizing: border-box;
        }
        .dash-metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
        }

        .dash-metric-card.is-quota { border-left-color: #059669; }
        .dash-metric-card.is-handover { border-left-color: #0284c7; }
        .dash-metric-card.is-distributed { border-left-color: #0d9488; }
        .dash-metric-card.is-remaining { border-left-color: #f59e0b; }
        .dash-metric-card.is-students { border-left-color: #4f46e5; }

        .dash-metric-label {
            grid-column: 1 / -1;
            grid-row: 1;
            margin: 0;
            min-height: 1.6em;
            display: flex;
            align-items: flex-start;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            line-height: 1.7;
            padding-top: 2px;
            padding-bottom: 2px;
            overflow: visible;
        }
        .dash-metric-label > span {
            display: block;
            overflow: visible;
            white-space: normal;
            word-break: break-word;
        }

        .dash-metric-value {
            grid-column: 1;
            grid-row: 2;
            margin: 0;
            align-self: center;
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .dash-metric-card.is-quota .dash-metric-value { color: #064e3b; }
        .dash-metric-card.is-handover .dash-metric-value { color: #075985; }
        .dash-metric-card.is-distributed .dash-metric-value { color: #0f766e; }
        .dash-metric-card.is-remaining .dash-metric-value { color: #b45309; }
        .dash-metric-card.is-students .dash-metric-value { color: #3730a3; }

        .dash-metric-icon {
            grid-column: 2;
            grid-row: 2;
            align-self: center;
            justify-self: end;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .dash-metric-card.is-quota .dash-metric-icon { background: #ecfdf5; color: #059669; }
        .dash-metric-card.is-handover .dash-metric-icon { background: #f0f9ff; color: #0284c7; }
        .dash-metric-card.is-distributed .dash-metric-icon { background: #f0fdfa; color: #0d9488; }
        .dash-metric-card.is-remaining .dash-metric-icon { background: #fffbeb; color: #d97706; }
        .dash-metric-card.is-students .dash-metric-icon { background: #eef2ff; color: #4f46e5; }
    </style>

    <div class="app-page-container space-y-6">

        <div class="dash-metrics" id="dashMetrics">

            <div class="dash-metric-card is-quota" title="ခွဲတမ်းစာအုပ်">
                <div class="dash-metric-label"><span>ခွဲတမ်းစာအုပ်</span></div>
                <h3 class="dash-metric-value" data-metric-value>{{ number_format($summary['total_quota_books']) }}</h3>
                <div class="dash-metric-icon"><i class="fas fa-book-open"></i></div>
            </div>

            <div class="dash-metric-card is-handover" title="လက်ဆင့်ကမ်းစာအုပ်">
                <div class="dash-metric-label"><span>လက်ဆင့်ကမ်းစာအုပ်</span></div>
                <h3 class="dash-metric-value" data-metric-value>{{ number_format($summary['handover_books']) }}</h3>
                <div class="dash-metric-icon"><i class="fas fa-hands-helping"></i></div>
            </div>

            <div class="dash-metric-card is-distributed" title="ဖြန့်ဝေပြီးစာအုပ်">
                <div class="dash-metric-label"><span>ဖြန့်ဝေပြီးစာအုပ်</span></div>
                <h3 class="dash-metric-value" data-metric-value>{{ number_format($summary['distributed_books']) }}</h3>
                <div class="dash-metric-icon"><i class="fas fa-check-circle"></i></div>
            </div>

            <div class="dash-metric-card is-remaining"
                title="လက်ကျန်စာအုပ် = ခွဲတမ်း + လက်ဆင့်ကမ်း − ဖြန့်ဝေပြီး">
                <div class="dash-metric-label"><span>လက်ကျန်စာအုပ်</span></div>
                <h3 class="dash-metric-value" data-metric-value>{{ number_format($summary['remaining_books']) }}</h3>
                <div class="dash-metric-icon"><i class="fas fa-boxes"></i></div>
            </div>

            <div class="dash-metric-card is-students"
                title="ကျောင်းသားခွဲတမ်း — မူလ / အလယ် / အထက် / စက်စိုက်မွေး စုစုပေါင်း">
                <div class="dash-metric-label"><span>ကျောင်းသား</span></div>
                <h3 class="dash-metric-value" data-metric-value>{{ number_format($summary['students']) }}</h3>
                <div class="dash-metric-icon"><i class="fas fa-user"></i></div>
            </div>

        </div>

        <!-- SECTION 1: ပြဌာန်းစာအုပ် Donut & Bar Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Donut Chart: ပြဌာန်းစာအုပ် ထုတ်ပေးမှု -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h6 class="modern-card-header-title text-base">
                        <i class="fas fa-chart-pie"></i>
                        မြို့နယ်အလိုက်ပြဌာန်းစာအုပ်ထုတ်ပေးမှုအခြေအနေ
                    </h6>
                </div>
                <div class="p-4 flex items-center justify-center" style="min-height: 340px;">
                    <canvas id="pieChart" style="max-height: 320px;"></canvas>
                </div>
            </div>

            <!-- Bar Chart: မြို့နယ်အလိုက် ပြဌာန်း၊ ဆရာလမ်းညွှန်၊ သင်ထောက်ကူ ဖြန့်ဝေမှု -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h6 class="modern-card-header-title text-base">
                        <i class="fas fa-chart-bar"></i>
                        မြို့နယ်အလိုက် ပြဌာန်း၊ ဆရာလမ်းညွှန်နှင့် သင်ထောက်ကူ ဖြန့်ဝေမှု
                    </h6>
                </div>
                <div class="p-4 flex items-center justify-center" style="min-height: 340px;">
                    <canvas id="barChart" style="max-height: 320px;"></canvas>
                </div>
            </div>

        </div>


        <!-- SECTION 2: ခဲတံ၊ ဘောပင်၊ ဝတ်စုံ Donut & Bar Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Donut Chart: မူလတန်း၊ အလယ်တန်း၊ အထက်တန်း အလိုက် ကျောင်းသားဦးရေ -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h6 class="modern-card-header-title text-base">
                        <i class="fas fa-chart-pie"></i>
                        မူလတန်း၊ အလယ်တန်း၊ အထက်တန်း အလိုက် ကျောင်းသားဦးရေ
                    </h6>
                </div>
                <div class="p-4 flex items-center justify-center" style="min-height: 340px;">
                    <canvas id="quotaDonutChart" style="max-height: 320px;"></canvas>
                </div>
            </div>

            <!-- Bar Chart: မြို့နယ်အလိုက် ခဲတံ၊ ဘောပင်၊ ဝတ်စုံ ဖြန့်ဝေရန် ကျောင်းသားဦးရေ -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h6 class="modern-card-header-title text-base">
                        <i class="fas fa-chart-bar"></i>
                        မြို့နယ်အလိုက် ခဲတံ၊ ဘောပင်၊ ဝတ်စုံ ဖြန့်ဝေရန် ကျောင်းသားဦးရေ
                    </h6>
                </div>
                <div class="p-4 flex items-center justify-center" style="min-height: 340px;">
                    <canvas id="quotaBarChart" style="max-height: 320px;"></canvas>
                </div>
            </div>

        </div>

    </div>
@endsection

@section('script-code')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Metric cards: shrink number font if it overflows the card width
            function fitMetricValues() {
                document.querySelectorAll('[data-metric-value]').forEach(function (el) {
                    el.style.fontSize = '';
                    var card = el.closest('.dash-metric-card');
                    if (!card) return;
                    var icon = card.querySelector('.dash-metric-icon');
                    var avail = card.clientWidth - 20 - 20 - 14 - (icon ? icon.offsetWidth : 52) - 8;
                    var max = 1.7;
                    var min = 1.0;
                    var size = max;
                    el.style.fontSize = size + 'rem';
                    while (el.scrollWidth > avail && size > min) {
                        size -= 0.05;
                        el.style.fontSize = size + 'rem';
                    }
                });
            }
            fitMetricValues();
            window.addEventListener('resize', fitMetricValues);

            Chart.defaults.font.family = "Noto Sans Myanmar";
            Chart.register(ChartDataLabels);

            /*
            |--------------------------------------------------------------------------
            | 1. Pie/Doughnut Chart (ပြဌာန်းစာအုပ် ထုတ်ပေးမှု)
            |--------------------------------------------------------------------------
            */
            const pieData = @json($distributionChart);
            const pieTotal = pieData.data.reduce(
                (total, value) => total + Number(value),
                0
            );

            new Chart(
                document.getElementById('pieChart'), {
                    type: 'doughnut',
                    data: {
                        labels: pieData.labels,
                        datasets: [{
                            data: pieData.data,
                            backgroundColor: [
                                '#059669', // Emerald (match quota donut)
                                '#0284c7', // Sky
                                '#d97706'  // Amber
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: 'Noto Sans Myanmar'
                                    }
                                }
                            },
                            datalabels: {
                                color: '#ffffff',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                formatter: function(value) {
                                    if (pieTotal === 0) return '0%';
                                    let percentage = (value / pieTotal) * 100;
                                    return percentage.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'centerText',
                        beforeDraw(chart) {
                            const { ctx } = chart;
                            ctx.save();
                            ctx.font = 'bold 22px Noto Sans Myanmar';
                            ctx.fillStyle = '#1f2937';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            const chartArea = chart.chartArea;
                            if (chartArea) {
                                ctx.fillText(
                                    pieTotal.toLocaleString(),
                                    (chartArea.left + chartArea.right) / 2,
                                    (chartArea.top + chartArea.bottom) / 2
                                );
                            }
                            ctx.restore();
                        }
                    }]
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 2. Bar Chart (မြို့နယ်အလိုက် ပြဌာန်း၊ ဆရာလမ်းညွှန်၊ သင်ထောက်ကူ ဖြန့်ဝေမှု)
            | Flat sharp rectangle bars without top rounding & without datalabels
            |--------------------------------------------------------------------------
            */
            const barData = @json($barChart);

            new Chart(
                document.getElementById('barChart'), {
                    type: 'bar',
                    data: {
                        labels: barData.labels,
                        datasets: [{
                                label: 'ပြဌာန်းဖြန့်ဝေမှု (စုံ)',
                                data: barData.textbooks,
                                backgroundColor: '#059669',
                                borderRadius: 0,
                                maxBarThickness: 28
                            },
                            {
                                label: 'ဆရာကိုင်/ဆရာလမ်းညွှန် ဖြန့်ဝေမှု',
                                data: barData.teacher_guides,
                                backgroundColor: '#0284c7',
                                borderRadius: 0,
                                maxBarThickness: 28
                            },
                            {
                                label: 'သင်ထောက်ကူ ဖြန့်ဝေမှု',
                                data: barData.supplies,
                                backgroundColor: '#d97706',
                                borderRadius: 0,
                                maxBarThickness: 28
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        family: 'Noto Sans Myanmar',
                                        size: 11
                                    },
                                    boxWidth: 12,
                                    padding: 12
                                }
                            },
                            datalabels: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const value = Number(ctx.parsed.y || 0).toLocaleString();
                                        if (ctx.datasetIndex === 0) {
                                            return ctx.dataset.label + ': ' + value + ' စုံ';
                                        }
                                        return ctx.dataset.label + ': ' + value;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                    autoSkip: false,
                                    font: {
                                        family: 'Noto Sans Myanmar',
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    color: '#334155'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(val) {
                                        return val.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 3. Donut Chart (မူလတန်း၊ အလယ်တန်း၊ အထက်တန်း)
            |--------------------------------------------------------------------------
            */
            const quotaDonutData = @json($quotaDonutChart);
            const quotaDonutTotal = quotaDonutData.data.reduce(
                (total, value) => total + Number(value),
                0
            );

            new Chart(
                document.getElementById('quotaDonutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: quotaDonutData.labels,
                        datasets: [{
                            data: quotaDonutData.data,
                            backgroundColor: [
                                '#059669', // မူလတန်း - Emerald
                                '#0284c7', // အလယ်တန်း - Sky
                                '#d97706', // အထက်တန်း - Amber
                                '#0d9488'  // စက်၊စိုက်၊မွေး - Teal
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: 'Noto Sans Myanmar',
                                        size: 13
                                    }
                                }
                            },
                            datalabels: {
                                color: '#ffffff',
                                font: {
                                    weight: 'bold',
                                    size: 13
                                },
                                formatter: function(value) {
                                    if (quotaDonutTotal === 0 || value === 0) return '';
                                    let percentage = (value / quotaDonutTotal) * 100;
                                    return percentage.toFixed(1) + '%';
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'centerTextQuota',
                        beforeDraw(chart) {
                            const { ctx } = chart;
                            ctx.save();
                            ctx.font = 'bold 22px Noto Sans Myanmar';
                            ctx.fillStyle = '#1f2937';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            const chartArea = chart.chartArea;
                            if (chartArea) {
                                ctx.fillText(
                                    quotaDonutTotal.toLocaleString(),
                                    (chartArea.left + chartArea.right) / 2,
                                    (chartArea.top + chartArea.bottom) / 2
                                );
                            }
                            ctx.restore();
                        }
                    }]
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 4. Bar Chart (မြို့နယ်အလိုက် ခဲတံ၊ ဘောပင်၊ ဝတ်စုံ ဖြန့်ဝေရန် ကျောင်းသားဦးရေ)
            | Flat sharp rectangle bars without top rounding & without datalabels
            |--------------------------------------------------------------------------
            */
            const quotaBarData = @json($quotaBarChart);

            new Chart(
                document.getElementById('quotaBarChart'), {
                    type: 'bar',
                    data: {
                        labels: quotaBarData.labels,
                        datasets: [
                            {
                                label: 'ဖြန့်ဝေရန် ကျောင်းသားဦးရေ',
                                data: quotaBarData.data,
                                backgroundColor: [
                                    '#059669', // မြန်အောင်
                                    '#0284c7', // ကြံခင်း
                                    '#d97706', // အင်္ဂပူ
                                    '#334155'  // စုစုပေါင်း
                                ],
                                borderRadius: 0,
                                maxBarThickness: 36,
                                barPercentage: 0.55,
                                categoryPercentage: 0.65
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                    autoSkip: false,
                                    font: {
                                        family: 'Noto Sans Myanmar',
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    color: '#334155'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    callback: function(val) {
                                        return val.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                }
            );

        });
    </script>
@endsection
