<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📈 วิเคราะห์หุ้นด้วย AI</title>

 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
 
    <style>
        .card-glow {
            border: 1px solid rgba(0, 0, 0, .08);
        }
        .quick-symbol-btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1 fw-semibold">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                📈 วิเคราะห์หุ้นด้วย AI
            </h3>
            <div class="text-muted">วิเคราะห์หุ้นสหรัฐด้วยตัวชี้วัดทางเทคนิคและ AI — รองรับทุกหุ้น เพียงกรอกสัญลักษณ์</div>
        </div>
 
        <div class="text-end">
            <div class="text-muted">สัญลักษณ์</div>
            <div class="fw-semibold mb-2">{{ $symbol }}</div>

            <div class="d-flex gap-2">
                <form action="/watchlist" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="symbol" value="{{ $symbol }}">
                    <button type="submit" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-bookmark-plus me-1"></i>
                        เพิ่ม Watchlist
                    </button>
                </form>
                <a href="{{ url('/watchlist') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-bookmark-star me-1"></i>
                    รายการของฉัน
                </a>
                <a href="{{ url('/compare') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-diagram-3 me-1"></i>
                    เทียบหลายหุ้น
                </a>
            </div>
        </div>
    </div>
 
    @if (!empty($errorMessage))
        <div class="alert alert-warning d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <div class="fw-semibold mb-1">ไม่สามารถโหลดข้อมูลหุ้นได้</div>
                <div class="small">{{ $errorMessage }}</div>
            </div>
        </div>
    @endif
 
    <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/stock') }}">
        <div class="col-12 col-md-5">
            <label class="form-label">กรอกสัญลักษณ์หุ้น (เช่น NVDA, AAPL)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                <input type="text" name="symbol" class="form-control text-uppercase" value="{{ $symbol }}" placeholder="NVDA" />
            </div>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">เลือกกลยุทธ์ Backtest</label>
            <select name="strategy" class="form-select">
                @php($selectedStrategy = $strategy ?? request()->query('strategy', 'sma_cross'))
                <option value="sma_cross" {{ $selectedStrategy === 'sma_cross' ? 'selected' : '' }}>SMA Crossover</option>
                <option value="macd" {{ $selectedStrategy === 'macd' ? 'selected' : '' }}>MACD Crossover</option>
                <option value="rsi" {{ $selectedStrategy === 'rsi' ? 'selected' : '' }}>RSI Oversold/Overbought</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <button class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i>
                ดูข้อมูล
            </button>
        </div>
    </form>

    <div class="mb-3">
        <div class="text-muted small mb-2">หุ้นยอดนิยม:</div>
        <div class="d-flex flex-wrap gap-2">
            @foreach (['NVDA', 'TSM', 'MU', 'VRT', 'AVGO', 'AAPL', 'MSFT', 'GOOGL'] as $quickSymbol)
                <a href="{{ url('/stock/' . $quickSymbol) }}" class="btn btn-outline-secondary quick-symbol-btn">
                    {{ $quickSymbol }}
                </a>
            @endforeach
        </div>
    </div>
 
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">ชื่อหุ้น</div>
                    <div class="fw-semibold">
                        @php($name = $quote['name'] ?? null)
                        {{ $name !== null && $name !== '' ? $name : $symbol }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">ราคาล่าสุด</div>
                    <div class="fw-semibold fs-4">
                        @php($close = $quote['close'] ?? null)
                        @if ($close !== null)
                            ${{ number_format((float) $close, 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
 
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">เปลี่ยนแปลง</div>
                    <div class="fw-semibold fs-4">
                        @php($pct = $quote['percent_change'] ?? null)
                        @if ($pct !== null)
                            @php($isUp = (float) $pct >= 0)
                            <span class="text-{{ $isUp ? 'success' : 'danger' }}">
                                {{ number_format((float) $pct, 2) }}%
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="fw-semibold">
                                <i class="bi bi-building me-1 text-primary"></i>
                                เกี่ยวกับบริษัท
                            </div>
                            <div class="text-muted small">ข้อมูลถูกสร้างโดย AI เพื่อการศึกษา</div>
                        </div>
                        <div class="text-end">
                            <span id="companySourceBadge" class="badge text-bg-light border">ℹ️ AI (Ollama)</span>
                        </div>
                    </div>

                    <div id="companyInfoStatus" class="mt-3 text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                            <div>กำลังโหลดข้อมูลบริษัท...</div>
                        </div>
                    </div>

                    <div id="companyInfoContent" class="mt-3 lh-lg" style="display:none;"></div>

                    <div class="mt-3 text-muted small">
                        ℹ️ ข้อมูลบริษัทสร้างโดย AI เพื่อการศึกษา อาจไม่ครบถ้วนหรือไม่อัปเดตล่าสุด ควรตรวจสอบจากแหล่งทางการ
                    </div>
                </div>
            </div>
        </div>
 

    <div class="row g-3 mb-4">
                    <div class="fw-semibold fs-4">
                        @php($close = $quote['close'] ?? null)
                        @if ($close !== null)
                            ${{ number_format((float) $close, 2) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
 
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">เปลี่ยนแปลง</div>
                    <div class="fw-semibold fs-4">
                        @php($pct = $quote['percent_change'] ?? null)
                        @if ($pct !== null)
                            @php($isUp = (float) $pct >= 0)
                            <span class="text-{{ $isUp ? 'success' : 'danger' }}">
                                {{ number_format((float) $pct, 2) }}%
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">RSI (14)</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-semibold fs-4">
                            @php($rsi = $indicators['rsi14'] ?? null)
                            {{ $rsi !== null ? number_format((float) $rsi, 2) : 'N/A' }}
                        </div>
                        @php($rsiSignal = $indicators['signals']['rsi'] ?? ['label' => 'Neutral', 'color' => 'secondary'])
                        <span class="badge bg-{{ $rsiSignal['color'] }}">{{ $rsiSignal['label'] }}</span>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="col-12 col-md-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">SMA50</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-semibold fs-4">
                            @php($sma50 = $indicators['sma50'] ?? null)
                            {{ $sma50 !== null ? number_format((float) $sma50, 2) : 'N/A' }}
                        </div>
                        @php($sma50Signal = $indicators['signals']['sma50'] ?? ['label' => '-', 'color' => 'secondary'])
                        <span class="badge bg-{{ $sma50Signal['color'] }}">{{ $sma50Signal['label'] }}</span>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="col-12 col-md-4">
            <div class="card card-glow h-100">
                <div class="card-body">
                    <div class="text-muted">MACD Histogram</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="fw-semibold fs-4">
                            @php($histogram = $indicators['macd']['histogram'] ?? null)
                            {{ $histogram !== null ? number_format((float) $histogram, 2) : 'N/A' }}
                        </div>
                        @php($macdSignal = $indicators['signals']['macd'] ?? ['label' => 'Neutral', 'color' => 'secondary'])
                        <span class="badge bg-{{ $macdSignal['color'] }}">{{ $macdSignal['label'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <div class="mb-3 text-muted small">
        หมายเหตุ: ข้อมูลและการวิเคราะห์เป็นเพียงข้อมูลประกอบการศึกษา ไม่ใช่คำแนะนำการลงทุน
    </div>

    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">บทวิเคราะห์ภาษาไทย</div>
                    <div class="text-muted small">วิเคราะห์จากตัวเลขราคาและตัวบ่งชี้เทคนิค</div>
                </div>
                <div class="text-end text-muted small">
                    <span class="badge text-bg-light border">Rule-based</span>
                </div>
            </div>

            <div class="mt-3">
                @php($ruleOk = ($ruleBasedAnalysis['ok'] ?? false) === true)
                @php($ruleSummary = $ruleBasedAnalysis['summary'] ?? null)
                @php($rulePoints = $ruleBasedAnalysis['points'] ?? [])
                @php($ruleError = $ruleBasedAnalysis['error'] ?? null)

                @if ($ruleOk && $ruleSummary !== null && trim($ruleSummary) !== '')
                    <div class="alert alert-light border-start border-4 border-primary mb-3">
                        <strong class="text-primary">{{ $ruleSummary }}</strong>
                    </div>
                    
                    @if (!empty($rulePoints))
                        <div class="list-group list-group-flush">
                            @foreach ($rulePoints as $point)
                                <div class="list-group-item bg-transparent border-0 px-0 py-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    {{ $point }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                @elseif ($ruleError !== null && $ruleError !== '')
                    <div class="text-muted">{{ $ruleError }}</div>
                @else
                    <div class="text-muted">ไม่มีข้อมูลเพียงพอสำหรับการวิเคราะห์</div>
                @endif
            </div>

            <div class="mt-3 text-muted small">
                หมายเหตุ: บทวิเคราะห์นี้สร้างจากตรรกะการวิเคราะห์เชิงเทคนิค เพื่อการศึกษาเท่านั้น
            </div>
        </div>
    </div>

    <div class="card card-glow mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold">บทวิเคราะห์โดย AI</div>
                        <div class="text-muted small">AI (Local) วิเคราะห์จากตัวเลขราคาและ indicator ที่คำนวณได้</div>
                    </div>
                    <div class="text-end text-muted small">
                        <span class="badge text-bg-light border">Ollama (qwen2)</span>

                    </div>
                </div>

                <div class="mt-3">
                    @php($aiOk = ($aiAnalysis['ok'] ?? false) === true)
                    @php($aiText = $aiAnalysis['analysis'] ?? null)
                    @php($aiError = $aiAnalysis['error'] ?? null)

                    @if ($aiOk && $aiText !== null && trim($aiText) !== '')
                        <div class="lh-lg">{!! nl2br(e($aiText)) !!}</div>
                    @elseif ($aiError !== null && $aiError !== '')
                        <div class="text-muted">{{ $aiError }}</div>
                    @else
                        <div class="text-muted">ยังไม่มีบทวิเคราะห์ AI ในขณะนี้ โปรดตรวจสอบการตั้งค่า</div>
                    @endif
                </div>

                <div class="mt-3 text-muted small">
                    หมายเหตุ: บทวิเคราะห์จาก AI เป็นข้อมูลประกอบเพื่อการศึกษาเท่านั้น ไม่ใช่คำแนะนำการลงทุน
                </div>
            </div>
        </div>
 
    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">สรุปผลทดสอบย้อนหลัง (Backtest)</div>
                    <div class="text-muted small">จำลองผลตอบแทนจากกลยุทธ์ที่เลือกเทียบกับ Buy & Hold</div>
                </div>
                <div class="text-end text-muted small">
                    <span class="badge text-bg-light border">Backtest</span>
                </div>
            </div>

            @php($backtestOk = ($backtestResult['ok'] ?? false) === true)
            @php($backtestError = $backtestResult['error'] ?? null)

            @if ($backtestOk)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mt-3">
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">กลยุทธ์</div>
                                <div class="fw-semibold text-capitalize">{{ $backtestResult['strategy'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">ผลตอบแทนกลยุทธ์</div>
                                <div class="fw-semibold fs-4 text-{{ ($backtestResult['strategy_return_pct'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                    {{ $backtestResult['strategy_return_pct'] !== null ? number_format($backtestResult['strategy_return_pct'], 2) . '%' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">ผลตอบแทนถือยาว</div>
                                <div class="fw-semibold fs-4 text-{{ ($backtestResult['buy_hold_return_pct'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                    {{ $backtestResult['buy_hold_return_pct'] !== null ? number_format($backtestResult['buy_hold_return_pct'], 2) . '%' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">มูลค่าพอร์ตสุดท้าย</div>
                                <div class="fw-semibold fs-4">
                                    {{ $backtestResult['final_value'] !== null ? '$' . number_format($backtestResult['final_value'], 2) : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-3 mt-3">
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">จำนวนเทรดที่ปิด</div>
                                <div class="fw-semibold fs-4">{{ number_format($backtestResult['num_trades'] ?? 0) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-glow h-100">
                            <div class="card-body">
                                <div class="text-muted">อัตราชนะ</div>
                                <div class="fw-semibold fs-4">
                                    {{ $backtestResult['win_rate'] !== null ? number_format($backtestResult['win_rate'], 2) . '%' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>วันที่เข้า</th>
                                <th>วันที่ออก</th>
                                <th>ราคาเข้า</th>
                                <th>ราคาออก</th>
                                <th>กำไร %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backtestResult['trades'] ?? [] as $trade)
                                <tr>
                                    <td>{{ $trade['entry_date'] }}</td>
                                    <td>{{ $trade['exit_date'] }}</td>
                                    <td>${{ number_format($trade['entry_price'], 2) }}</td>
                                    <td>${{ number_format($trade['exit_price'], 2) }}</td>
                                    <td class="text-{{ $trade['profit_pct'] >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($trade['profit_pct'], 2) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center">ไม่มีการเทรดปิดในช่วงเวลานี้</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mt-3" role="alert">
                    {{ $backtestError ?? 'ไม่สามารถทำงาน Backtest ได้ในขณะนี้' }}
                </div>
            @endif
        </div>
    </div>
 
    <div class="card card-glow">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">
                        <i class="bi bi-graph-line me-1"></i>
                        ราคาปิด (30 วันล่าสุด)
                    </div>
                    <div class="text-muted small">อัปเดตจากแคชโดยอัตโนมัติ</div>
                </div>
                <div class="text-end text-muted small">
                    <span class="badge text-bg-light border">Chart.js</span>
                </div>
            </div>
 
            <div class="mt-3">
                <canvas id="stockChart" height="120"></canvas>
            </div>
 
            <div id="chartStatus" class="text-muted small mt-2"></div>
        </div>
    </div>
 
</div>
 
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
 
<script>
    const symbol = @json($symbol);
    const chartEndpoint = @json(url('/stock')) + '/' + encodeURIComponent(symbol) + '/chart-data';
 
    const ctx = document.getElementById('stockChart');
    const chartStatus = document.getElementById('chartStatus');
 
    const labels = [];
    const series = [];
    const sma20 = [];
    const sma50 = [];
 
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: `ปิด - ${symbol}`,
                    data: series,
                    borderWidth: 2,
                    tension: 0.25,
                    pointRadius: 2,
                    pointHoverRadius: 3,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.12)'
                },
                {
                    label: `SMA20 - ${symbol}`,
                    data: sma20,
                    borderWidth: 2,
                    tension: 0.25,
                    pointRadius: 0,
                    borderColor: '#198754',
                    backgroundColor: 'transparent',
                    borderDash: [6, 4]
                },
                {
                    label: `SMA50 - ${symbol}`,
                    data: sma50,
                    borderWidth: 2,
                    tension: 0.25,
                    pointRadius: 0,
                    borderColor: '#fd7e14',
                    backgroundColor: 'transparent',
                    borderDash: [3, 3]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true }
            },
            scales: {
                x: {
                    ticks: { maxRotation: 0, minRotation: 0, autoSkip: true }
                },
                y: {
                    beginAtZero: false
                }
            }
        }
    });
 
    async function loadCompanyInfo() {
        const endpoint = @json(url('/stock')) + '/' + encodeURIComponent(symbol) + '/company-info';
        const statusEl = document.getElementById('companyInfoStatus');
        const contentEl = document.getElementById('companyInfoContent');
        if (!statusEl || !contentEl) return;

        statusEl.style.display = 'block';
        contentEl.style.display = 'none';
        contentEl.innerHTML = '';

        try {
            const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (!data.ok || !data.description) {
                const msg = data.error ? data.error : 'ไม่พบข้อมูลบริษัทในขณะนี้';
                statusEl.innerHTML = '<div class="text-muted">'+ msg +'</div>';
                return;
            }

            const text = String(data.description);
            const html = text.split(/\r?\n/).map(line => line.trim() === '' ? '<br>' : line.replace(/</g,'<').replace(/>/g,'>')).join('<br>');
            contentEl.innerHTML = html;
            statusEl.style.display = 'none';
            contentEl.style.display = 'block';
        } catch (e) {
            statusEl.innerHTML = '<div class="text-muted">เกิดข้อผิดพลาดระหว่างโหลดข้อมูลบริษัท โปรดลองใหม่ภายหลัง</div>';
        }
    }

    async function loadChart() {
        chartStatus.textContent = 'กำลังโหลดข้อมูลกราฟ...';
        try {
            const res = await fetch(chartEndpoint, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
 
            if (!data.ok) {
                chartStatus.textContent = data.error ? data.error : 'ไม่สามารถโหลดข้อมูลกราฟได้';
                return;
            }
 
            chart.data.labels = data.labels || [];
            chart.data.datasets[0].data = data.series || [];
            chart.data.datasets[1].data = data.sma20 || [];
            chart.data.datasets[2].data = data.sma50 || [];
            chart.update();
 
            chartStatus.textContent = '';
        } catch (e) {
            chartStatus.textContent = 'เกิดข้อผิดพลาดระหว่างโหลดข้อมูลกราฟ';
        }
    }
 
    loadCompanyInfo();
    loadChart();
</script>
</body>
</html>
