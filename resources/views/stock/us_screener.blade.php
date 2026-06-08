<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>US Screener</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .card-glow { border: 1px solid rgba(0,0,0,.08); }
        .badge-soft { background: #f1f5f9; color: #475569; font-weight: 600; }
        .sym { font-weight: 800; color: #2563eb; }
        tbody tr { cursor: pointer; }
        tbody tr:hover { background: rgba(13,110,253,.06); }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1"><i class="bi bi-search text-primary"></i> US Screener (Search/Analyze)</h3>
            <div class="text-muted small">ค้นหาหุ้นสหรัฐด้วย symbol หรือ pattern (เช่น A*, *L) จากข้อมูลที่มีใน DB</div>
        </div>
        <div class="text-end">
            <a href="{{ url('/stock/NVDA') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับ
            </a>
        </div>
    </div>

    @if (!empty($errorMessage))
        <div class="alert alert-warning d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <div class="fw-semibold mb-1">มีข้อผิดพลาด</div>
                <div class="small">{{ $errorMessage }}</div>
            </div>
        </div>
    @endif

    <div class="card card-glow mb-4">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/us-screener') }}">
                <div class="col-12 col-md-7">
                    <label class="form-label">ค้นหา symbol หรือ pattern</label>
                    <input type="text" name="q" class="form-control text-uppercase" value="{{ $q ?? '' }}" placeholder="เช่น AAPL หรือ A* หรือ *L">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Limit</label>
                    <input type="number" name="limit" class="form-control" value="{{ $limit ?? 30 }}" min="1" max="200">
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> สแกน</button>
                </div>
            </form>
            <div class="text-muted small mt-3">
                หมายเหตุ: ถ้า DB ยังไม่เคยมี symbol ตัวนั้น ระบบจะยังไม่รู้จัก pattern จึงแสดงผลไม่ได้ทันที
                วิธีเพิ่มข้อมูลคือเปิดหน้า <code>/stock/{symbol}</code> หรือเพิ่มผ่าน watchlist ก่อน
            </div>
        </div>
    </div>

    @if (!empty($items))
        <div class="card card-glow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Symbol</th>
                            <th>Close</th>
                            <th>Change %</th>
                            <th>RSI</th>
                            <th>Trend</th>
                            <th>MACD</th>
                            <th>Summary</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($items as $symbol => $it)
                            @if (($it['ok'] ?? false) !== true)
                                <tr class="table-light">
                                    <td><span class="sym">{{ $symbol }}</span></td>
                                    <td colspan="6" class="text-muted">{{ $it['error'] ?? 'ไม่สามารถดึงข้อมูลได้' }}</td>
                                </tr>
                            @else
                                <tr onclick="window.location.href='{{ url('/stock') }}/{{ $symbol }}'">
                                    <td><span class="sym">{{ $symbol }}</span></td>
                                    <td>{{ isset($it['close']) && $it['close'] !== null ? '$'.number_format((float)$it['close'],2) : '-' }}</td>
                                    <td>
                                        @php($pct = $it['percent_change'] ?? null)
                                        @if ($pct !== null)
                                            @php($isUp = (float)$pct >= 0)
                                            <span class="text-{{ $isUp ? 'success' : 'danger' }}">{{ number_format((float)$pct,2) }}%</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php($rsi = $it['rsi'] ?? null)
                                        <span class="badge badge-soft">{{ $rsi !== null ? number_format((float)$rsi,2) : 'N/A' }}</span>
                                        <div class="small text-muted">{{ $it['rsi_signal'] ?? '-' }}</div>
                                    </td>
                                    <td><span class="badge badge-soft">{{ $it['trend_signal'] ?? '-' }}</span></td>
                                    <td><span class="badge badge-soft">{{ $it['macd_signal'] ?? '-' }}</span></td>
                                    <td style="max-width:320px;">
                                        <div class="small text-muted">{{ $it['summary'] ?? '-' }}</div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
</body>
</html>

