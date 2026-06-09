<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📈 วิเคราะห์หุ้นด้วย AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .card-glow { border: 1px solid rgba(0,0,0,.08); }
        .quick-symbol-btn { padding: 0.25rem 0.75rem; font-size: 0.85rem; }
        .chart-wrapper { position: relative; height: 160px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
 
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h3 class="mb-1 fw-semibold">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                📈 วิเคราะห์หุ้นด้วย AI
            </h3>
            <div class="text-muted">วิเคราะห์หุ้นสหรัฐด้วยตัวชี้วัดทางเทคนิคและ AI — รองรับทุกหุ้น เพียงกรอกสัญลักษณ์</div>
        </div>
        <div class="text-end">
            <div class="mb-2">
                @php
                    $cryptoSrc = 'https://cdn.jsdelivr.net/gh/spothq/cryptocurrency-icons@master/svg/color/' . strtolower($symbol) . '.svg';
                    $imgSrc = !empty($logoUrl) ? $logoUrl : $cryptoSrc;
                @endphp
                <img id="headerLogo" src="{{ $imgSrc }}" alt="{{ $symbol }}"
                     style="height:52px; max-width:130px; object-fit:contain; display:block; margin-left:auto;">
                <span id="headerSymbol" class="badge rounded-pill px-3 py-2 fw-semibold"
                      style="display:none;background:#dbeafe;color:#1d4ed8;font-size:1rem;">{{ $symbol }}</span>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="{{ url('/') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <form action="/watchlist" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="symbol" value="{{ $symbol }}">
                    <button type="submit" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-bookmark-plus me-1"></i>เพิ่ม Watchlist
                    </button>
                </form>
                <a href="{{ url('/watchlist') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-bookmark-star me-1"></i>รายการของฉัน
                </a>
                <a href="{{ url('/compare') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-diagram-3 me-1"></i>เทียบหลายหุ้น
                </a>
            </div>
        </div>
    </div>
 
    <!-- Error -->
    @if (!empty($errorMessage))
        <div class="alert alert-warning d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <div class="fw-semibold mb-1">ไม่สามารถโหลดข้อมูลหุ้นได้</div>
                <div class="small">{{ $errorMessage }}</div>
            </div>
        </div>
    @endif
 
    <!-- Search form -->
    <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url('/stock') }}">
        <div class="col-12 col-md-5">
            <label class="form-label" for="symbolInput">กรอกสัญลักษณ์หุ้น (เช่น NVDA, AAPL)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                <input type="text" id="symbolInput" name="symbol" class="form-control text-uppercase" value="{{ $symbol }}" placeholder="NVDA" />
            </div>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label" for="strategySelect">เลือกกลยุทธ์ Backtest</label>
            <select id="strategySelect" name="strategy" class="form-select">
                @php($selectedStrategy = $strategy ?? request()->query('strategy', 'sma_cross'))
                <option value="sma_cross" {{ $selectedStrategy === 'sma_cross' ? 'selected' : '' }}>SMA Crossover</option>
                <option value="macd" {{ $selectedStrategy === 'macd' ? 'selected' : '' }}>MACD Crossover</option>
                <option value="rsi" {{ $selectedStrategy === 'rsi' ? 'selected' : '' }}>RSI Oversold/Overbought</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <button class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i>ดูข้อมูล
            </button>
        </div>
    </form>
 
    <!-- Quick picks -->
    <div class="mb-4">
        <div class="text-muted small mb-2">หุ้นยอดนิยม:</div>
        <div class="d-flex flex-wrap gap-2">
            @foreach (['NVDA','AAPL','MSFT','GOOGL','JPM','KO','WMT','XOM'] as $quickSymbol)
                <a href="{{ url('/stock/'.$quickSymbol) }}" class="btn btn-outline-secondary quick-symbol-btn">{{ $quickSymbol }}</a>
            @endforeach
        </div>
    </div>
 
    <!-- Row 1: name / price / change -->
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
    </div>
 
    <!-- About company -->
    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-semibold"><i class="bi bi-building me-1 text-primary"></i>เกี่ยวกับบริษัท</div>
                    <div class="text-muted small">ข้อมูลถูกสร้างโดย AI เพื่อการศึกษา</div>
                </div>
                <span class="badge text-bg-light border">ℹ️ AI (Ollama)</span>
            </div>
            <div id="companyInfoStatus" class="mt-3 text-muted small">
                <div class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                    <div>กำลังโหลดข้อมูลบริษัท...</div>
                </div>
            </div>
            <div id="companyInfoContent" class="mt-3 lh-lg" style="display:none;"></div>
            <div class="mt-3 text-muted small">ℹ️ ข้อมูลบริษัทสร้างโดย AI เพื่อการศึกษา อาจไม่ครบถ้วนหรือไม่อัปเดตล่าสุด ควรตรวจสอบจากแหล่งทางการ</div>
        </div>
    </div>
 
    <!-- Row 2: indicators -->
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
                        @php($rsiSignal = $indicators['signals']['rsi'] ?? ['label'=>'Neutral','color'=>'secondary'])
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
                        @php($sma50Signal = $indicators['signals']['sma50'] ?? ['label'=>'-','color'=>'secondary'])
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
                        @php($macdSignal = $indicators['signals']['macd'] ?? ['label'=>'Neutral','color'=>'secondary'])
                        <span class="badge bg-{{ $macdSignal['color'] }}">{{ $macdSignal['label'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <div class="mb-3 text-muted small">หมายเหตุ: ข้อมูลและการวิเคราะห์เป็นเพียงข้อมูลประกอบการศึกษา ไม่ใช่คำแนะนำการลงทุน</div>
 
    <!-- Rule-based -->
    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">บทวิเคราะห์ภาษาไทย</div>
                    <div class="text-muted small">วิเคราะห์จากตัวเลขราคาและตัวบ่งชี้เทคนิค</div>
                </div>
                <span class="badge text-bg-light border">Rule-based</span>
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
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>{{ $point }}
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
            <div class="mt-3 text-muted small">หมายเหตุ: บทวิเคราะห์นี้สร้างจากตรรกะการวิเคราะห์เชิงเทคนิค เพื่อการศึกษาเท่านั้น</div>
        </div>
    </div>
 
    <!-- AI Ollama -->
    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">บทวิเคราะห์โดย AI</div>
                    <div class="text-muted small">AI (Local) วิเคราะห์จากตัวเลขราคาและ indicator ที่คำนวณได้</div>
                </div>
                <span class="badge text-bg-light border">Ollama (qwen2)</span>
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
            <div class="mt-3 text-muted small">หมายเหตุ: บทวิเคราะห์จาก AI เป็นข้อมูลประกอบเพื่อการศึกษาเท่านั้น ไม่ใช่คำแนะนำการลงทุน</div>
        </div>
    </div>
 
    <!-- Backtest -->
    <div class="card card-glow mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-semibold">สรุปผลทดสอบย้อนหลัง (Backtest)</div>
                    <div class="text-muted small">จำลองผลตอบแทนจากกลยุทธ์ที่เลือกเทียบกับ Buy & Hold</div>
                </div>
                <span class="badge text-bg-light border">Backtest</span>
            </div>
            @php($backtestOk = ($backtestResult['ok'] ?? false) === true)
            @php($backtestError = $backtestResult['error'] ?? null)
            @if ($backtestOk)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mt-3">
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">กลยุทธ์</div>
                        <div class="fw-semibold text-capitalize">{{ $backtestResult['strategy'] ?? '-' }}</div>
                    </div></div></div>
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">ผลตอบแทนกลยุทธ์</div>
                        <div class="fw-semibold fs-4 text-{{ ($backtestResult['strategy_return_pct'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                            {{ $backtestResult['strategy_return_pct'] !== null ? number_format($backtestResult['strategy_return_pct'], 2).'%' : 'N/A' }}
                        </div>
                    </div></div></div>
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">ผลตอบแทนถือยาว</div>
                        <div class="fw-semibold fs-4 text-{{ ($backtestResult['buy_hold_return_pct'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                            {{ $backtestResult['buy_hold_return_pct'] !== null ? number_format($backtestResult['buy_hold_return_pct'], 2).'%' : 'N/A' }}
                        </div>
                    </div></div></div>
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">มูลค่าพอร์ตสุดท้าย</div>
                        <div class="fw-semibold fs-4">
                            {{ $backtestResult['final_value'] !== null ? '$'.number_format($backtestResult['final_value'], 2) : 'N/A' }}
                        </div>
                    </div></div></div>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-3 mt-3">
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">จำนวนเทรดที่ปิด</div>
                        <div class="fw-semibold fs-4">{{ number_format($backtestResult['num_trades'] ?? 0) }}</div>
                    </div></div></div>
                    <div class="col"><div class="card card-glow h-100"><div class="card-body">
                        <div class="text-muted">อัตราชนะ</div>
                        <div class="fw-semibold fs-4">
                            {{ $backtestResult['win_rate'] !== null ? number_format($backtestResult['win_rate'], 2).'%' : 'N/A' }}
                        </div>
                    </div></div></div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-striped table-sm">
                        <thead><tr><th>วันที่เข้า</th><th>วันที่ออก</th><th>ราคาเข้า</th><th>ราคาออก</th><th>กำไร %</th></tr></thead>
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
                                <tr><td colspan="5" class="text-muted text-center">ไม่มีการเทรดปิดในช่วงเวลานี้</td></tr>
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
 
    <!-- Charts card -->
    <div class="card card-glow">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="fw-semibold"><i class="bi bi-graph-line me-1"></i>กราฟราคาและ Indicators</div>
                    <div class="text-muted small">ราคาปิด 30 วันล่าสุด + RSI + MACD</div>
                </div>
                <span class="badge text-bg-light border">Chart.js</span>
            </div>
 
            <!-- Price chart -->
            <div class="fw-semibold small text-muted mb-1">ราคาปิด + SMA</div>
            <div class="chart-wrapper">
                <canvas id="stockChart"></canvas>
            </div>
            <div id="chartStatus" class="text-muted small mt-1 mb-3"></div>
 
            <!-- RSI chart -->
            <div class="border-top pt-3 mt-2">
                <div class="fw-semibold small mb-1">
                    RSI (14)
                    <span class="text-muted fw-normal">— เส้นแดง = Overbought (70) &nbsp; เส้นเขียว = Oversold (30)</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="rsiChart"></canvas>
                </div>
            </div>
 
            <!-- MACD chart -->
            <div class="border-top pt-3 mt-2">
                <div class="fw-semibold small mb-1">
                    MACD Histogram
                    <span class="text-muted fw-normal">— แท่งเขียว = Bullish &nbsp; แท่งแดง = Bearish</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="macdChart"></canvas>
                </div>
            </div>
        </div>
    </div>
 
</div>
 
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const symbol = @json($symbol);
    const chartEndpoint = @json(url('/stock')) + '/' + encodeURIComponent(symbol) + '/chart-data';
    const chartStatus = document.getElementById('chartStatus');
 
    // Helper: แปลง null → NaN (ป้องกัน Chart.js วาดเส้นไปที่ 0)
    const toNaN = arr => (arr || []).map(v => (v === null || v === undefined) ? NaN : Number(v));
 
    // ---- Price chart ----
    const priceChart = new Chart(document.getElementById('stockChart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: `ปิด - ${symbol}`, data: [], borderWidth: 2, tension: 0.25, pointRadius: 2,
                  borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.10)' },
                { label: `SMA20`, data: [], borderWidth: 1.5, tension: 0.25, pointRadius: 0,
                  borderColor: '#198754', backgroundColor: 'transparent', borderDash: [6,4] },
                { label: `SMA50`, data: [], borderWidth: 1.5, tension: 0.25, pointRadius: 0,
                  borderColor: '#fd7e14', backgroundColor: 'transparent', borderDash: [3,3] }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: true, labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { beginAtZero: false }
            }
        }
    });
 
    // ---- RSI chart ----
    const rsiChart = new Chart(document.getElementById('rsiChart'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'RSI (14)', data: [], borderColor: '#6f42c1', backgroundColor: 'rgba(111,66,193,0.07)',
                  borderWidth: 2, pointRadius: 1, tension: 0.2, spanGaps: true, fill: false },
                { label: 'Overbought 70', data: [], borderColor: 'rgba(220,53,69,0.7)', borderWidth: 1,
                  borderDash: [5,5], pointRadius: 0, spanGaps: true, fill: false },
                { label: 'Oversold 30', data: [], borderColor: 'rgba(25,135,84,0.7)', borderWidth: 1,
                  borderDash: [5,5], pointRadius: 0, spanGaps: true, fill: false }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: true, labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                y: { min: 0, max: 100, ticks: { stepSize: 20 } },
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } }
            }
        }
    });
 
    // ---- MACD chart ----
    const macdChart = new Chart(document.getElementById('macdChart'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                { label: 'Histogram', data: [], backgroundColor: [], borderWidth: 0, order: 2 },
                { label: 'MACD', data: [], type: 'line', borderColor: '#0d6efd', borderWidth: 1.5,
                  pointRadius: 0, tension: 0.2, spanGaps: true, backgroundColor: 'transparent', order: 1 },
                { label: 'Signal', data: [], type: 'line', borderColor: '#fd7e14', borderWidth: 1.5,
                  pointRadius: 0, tension: 0.2, spanGaps: true, backgroundColor: 'transparent', order: 1 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: true, labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { beginAtZero: false }
            }
        }
    });
 
    async function loadCompanyInfo() {
        const endpoint = @json(url('/stock')) + '/' + encodeURIComponent(symbol) + '/company-info';
        const statusEl = document.getElementById('companyInfoStatus');
        const contentEl = document.getElementById('companyInfoContent');
        if (!statusEl || !contentEl) return;
        try {
            const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.ok || !data.description) {
                statusEl.innerHTML = '<div class="text-muted">' + (data.error || 'ไม่พบข้อมูลบริษัทในขณะนี้') + '</div>';
                return;
            }
            contentEl.textContent = String(data.description);
            contentEl.innerHTML = contentEl.innerHTML.replace(/\r?\n/g, '<br>');
            statusEl.style.display = 'none';
            contentEl.style.display = 'block';
        } catch (e) {
            statusEl.innerHTML = '<div class="text-muted">เกิดข้อผิดพลาดระหว่างโหลดข้อมูลบริษัท</div>';
        }
    }
 
    async function loadChart() {
        chartStatus.textContent = 'กำลังโหลดข้อมูลกราฟ...';
        try {
            const res = await fetch(chartEndpoint, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.ok) {
                chartStatus.textContent = data.error || 'ไม่สามารถโหลดข้อมูลกราฟได้';
                return;
            }
            chartStatus.textContent = '';
            const labels = data.labels || [];
            const n = labels.length;
 
            // Price
            priceChart.data.labels = labels;
            priceChart.data.datasets[0].data = toNaN(data.series);
            priceChart.data.datasets[1].data = toNaN(data.sma20);
            priceChart.data.datasets[2].data = toNaN(data.sma50);
            priceChart.update();
 
            // RSI — ถ้า endpoint ยังไม่ส่ง rsi_series มา กราฟจะว่างเปล่า (ไม่ error)
            const rsiData = toNaN(data.rsi_series);
            rsiChart.data.labels = labels;
            rsiChart.data.datasets[0].data = rsiData;
            rsiChart.data.datasets[1].data = Array(n).fill(70);
            rsiChart.data.datasets[2].data = Array(n).fill(30);
            rsiChart.update();
 
            // MACD — ถ้า endpoint ยังไม่ส่ง macd_histogram มา กราฟจะว่างเปล่า (ไม่ error)
            const histData = toNaN(data.macd_histogram);
            macdChart.data.labels = labels;
            macdChart.data.datasets[0].data = histData;
            macdChart.data.datasets[0].backgroundColor = histData.map(v =>
                isNaN(v) ? 'transparent' : (v >= 0 ? 'rgba(25,135,84,0.7)' : 'rgba(220,53,69,0.7)')
            );
            macdChart.data.datasets[1].data = toNaN(data.macd_line);
            macdChart.data.datasets[2].data = toNaN(data.signal_line);
            macdChart.update();
 
        } catch (e) {
            chartStatus.textContent = 'เกิดข้อผิดพลาดระหว่างโหลดข้อมูลกราฟ';
        }
    }
 
    // Logo onerror: ถ้า Twelve Data logo ล้มเหลว → ลอง crypto CDN → ถ้าล้มเหลวอีก → badge
    const headerLogo = document.getElementById('headerLogo');
    if (headerLogo) {
        headerLogo.onerror = function () {
            const cryptoUrl = 'https://cdn.jsdelivr.net/gh/spothq/cryptocurrency-icons@master/svg/color/'
                + symbol.toLowerCase() + '.svg';
            if (!this.src.includes('cryptocurrency-icons')) {
                this.src = cryptoUrl;
                this.onerror = function () {
                    this.style.display = 'none';
                    const symEl = document.getElementById('headerSymbol');
                    if (symEl) symEl.style.display = 'inline-block';
                };
            } else {
                this.style.display = 'none';
                const symEl = document.getElementById('headerSymbol');
                if (symEl) symEl.style.display = 'inline-block';
            }
        };
    }
 
    loadCompanyInfo();
    loadChart();
</script>
</body>
</html>
