<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | AI Stock Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .hero-card { border: 1px solid rgba(0,0,0,.06); background: rgba(255,255,255,.9); }
        .stat-card { border: 1px solid rgba(0,0,0,.06); background: #fff; }
        .quick-symbol { padding: 0.35rem 0.8rem; font-size: 0.9rem; }
        .watch-grid .watch-card { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; background: #fff; }
        .badge-signal { font-size: 0.82rem; padding: .45rem .7rem; border-radius: 999px; }
        .logo-box { width:44px; height:44px; border-radius:12px; background:#f8fafc; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
 
    <!-- Header / Hero -->
    <div class="hero-card rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="mb-1 fw-bold">
                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                    📈 AI Stock Analyzer
                </h2>
                <div class="text-muted">วิเคราะห์หุ้นสหรัฐด้วยตัวชี้วัดทางเทคนิคและสรุปสัญญาณ — รองรับทุกหุ้น</div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="{{ url('/watchlist') }}" class="btn btn-primary">
                    <i class="bi bi-bookmark-star me-1"></i>Watchlist
                </a>
                <a href="{{ url('/compare') }}" class="btn btn-outline-primary">
                    <i class="bi bi-diagram-3 me-1"></i>เทียบหลายหุ้น
                </a>
            </div>
        </div>
 
        <form method="GET" action="{{ url('/stock') }}" class="mt-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label text-muted mb-1" for="dashSymbol">ค้นหาด่วน</label>
                    <input type="text" id="dashSymbol" name="symbol" class="form-control text-uppercase"
                           placeholder="กรอก ticker เช่น NVDA, AAPL, KO..." aria-label="symbol">
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>🔍 วิเคราะห์
                    </button>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-muted small mb-2">Quick picks</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach (['NVDA','AAPL','MSFT','GOOGL','JPM','KO','WMT','XOM'] as $s)
                        <a href="{{ url('/stock/'.$s) }}" class="btn btn-outline-secondary quick-symbol">{{ $s }}</a>
                    @endforeach
                </div>
            </div>
        </form>
    </div>
 
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card rounded-4 p-3 h-100">
                <div class="text-muted small">Watchlist</div>
                <div class="fs-4 fw-bold"><i class="bi bi-bookmark-star text-warning me-2"></i>{{ $watchlistCount }}</div>
                <div class="text-muted small">รายการที่ติดตาม</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card rounded-4 p-3 h-100">
                <div class="text-muted small">หุ้นในระบบ</div>
                <div class="fs-4 fw-bold"><i class="bi bi-database text-primary me-2"></i>{{ $totalSymbols }}</div>
                <div class="text-muted small">distinct symbols ใน DB</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card rounded-4 p-3 h-100">
                <div class="text-muted small">แนวโน้มขาขึ้น</div>
                <div class="fs-4 fw-bold text-success"><i class="bi bi-graph-up text-success me-2"></i>{{ $bullishCount }}</div>
                <div class="text-muted small">trend_signal = ขาขึ้น</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stat-card rounded-4 p-3 h-100">
                <div class="text-muted small">แนวโน้มขาลง</div>
                <div class="fs-4 fw-bold text-danger"><i class="bi bi-graph-down text-danger me-2"></i>{{ $bearishCount }}</div>
                <div class="text-muted small">RSI Oversold: {{ $oversoldCount }}</div>
            </div>
        </div>
    </div>
 
    <!-- Watchlist Overview -->
    <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <h4 class="mb-0 fw-bold"><i class="bi bi-bookmark-star me-2 text-primary"></i>Watchlist Overview</h4>
            <div class="text-muted small">สรุปจากข้อมูล DB เท่านั้น</div>
        </div>
 
        @if ($watchlistItems->isEmpty())
            <div class="text-center bg-white border rounded-4 p-5">
                <div class="mb-3">
                    <i class="bi bi-inbox" style="font-size:3rem; color:#cbd5e1"></i>
                </div>
                <div class="fw-bold">เพิ่มหุ้นใน Watchlist เพื่อดูสรุปที่นี่</div>
                <div class="text-muted small mt-1">เริ่มต้นจากการเพิ่ม ticker ที่ต้องการติดตาม</div>
                <a href="{{ url('/watchlist') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>ไปหน้า Watchlist
                </a>
            </div>
        @else
            <div class="watch-grid row g-3">
                @foreach ($watchlistItems as $item)
                    @php
                        $sym = strtoupper(trim((string) $item->symbol));
                        $d   = $watchlistData[$sym] ?? null;
 
                        $close      = $d['close'] ?? null;
                        $pct        = $d['percent_change'] ?? null;
                        $rsi        = $d['rsi'] ?? null;
                        $trend      = $d['trend_signal'] ?? 'N/A';
                        $macdSig    = $d['macd_signal'] ?? 'N/A';
                        $rsiSig     = $d['rsi_signal'] ?? 'N/A';
                        $stockLogo  = $d['logoUrl'] ?? null;
 
                        // logo src: Twelve Data → crypto CDN → building icon
                        $cryptoIconSrc = 'https://cdn.jsdelivr.net/gh/spothq/cryptocurrency-icons@master/svg/color/' . strtolower($sym) . '.svg';
                        $logoSrc = !empty($stockLogo) ? $stockLogo : $cryptoIconSrc;
 
                        $rsiBadge   = match($rsiSig) { 'Oversold' => 'success', 'Overbought' => 'danger', default => 'secondary' };
                        $trendBadge = match($trend) { 'ขาขึ้น' => 'success', 'ขาลง' => 'danger', default => 'secondary' };
                        $macdBadge  = match($macdSig) { 'Bullish' => 'success', 'Bearish' => 'danger', default => 'secondary' };
                        $isUp       = is_numeric($pct) && (float)$pct >= 0;
                        $hasClose   = is_numeric($close);
                    @endphp
 
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="watch-card p-3 h-100">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Logo: stock logo → crypto CDN → building icon fallback -->
                                    <div class="logo-box">
                                        <img src="{{ $logoSrc }}" alt="{{ $sym }}"
                                             style="width:100%; height:100%; object-fit:contain;"
                                             onerror="this.style.display='none'; var ic=this.nextElementSibling; if(ic) ic.style.display='inline-block';">
                                        <i class="bi bi-building" style="font-size:1.4rem; color:#94a3b8; display:none;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5">
                                            <a href="{{ url('/stock/'.$sym) }}" class="text-decoration-none">{{ $sym }}</a>
                                        </div>
                                        <div class="text-muted small">${{ $hasClose ? number_format((float)$close, 2) : 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">% change</div>
                                    <div class="fw-bold text-{{ $isUp ? 'success' : 'danger' }}">
                                        {{ is_numeric($pct) ? number_format((float)$pct, 2).'%' : 'N/A' }}
                                    </div>
                                </div>
                            </div>
 
                            <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                                <span class="badge text-bg-light border badge-signal">
                                    <i class="bi bi-speedometer2 me-1"></i>
                                    RSI: {{ $rsi !== null ? number_format((float)$rsi, 2) : 'N/A' }}
                                </span>
                                <span class="badge bg-{{ $rsiBadge }} badge-signal">{{ $rsiSig }}</span>
                                <span class="badge bg-{{ $trendBadge }} badge-signal">{{ $trend }}</span>
                                <span class="badge bg-{{ $macdBadge }} badge-signal">MACD: {{ $macdSig }}</span>
                            </div>
 
                            <div class="mt-3">
                                <a href="{{ url('/stock/'.$sym) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-bullseye me-1"></i>ดูวิเคราะห์
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
 
    <!-- Footer navigation -->
    <div class="d-flex gap-2 flex-wrap mt-4 mb-5">
        <a href="{{ url('/stock/NVDA') }}" class="btn btn-outline-secondary flex-fill">
            <i class="bi bi-graph-up me-1"></i>📊 วิเคราะห์รายตัว
        </a>
        <a href="{{ url('/watchlist') }}" class="btn btn-outline-primary flex-fill">
            <i class="bi bi-bookmark-star me-1"></i>⭐ Watchlist
        </a>
        <a href="{{ url('/compare') }}" class="btn btn-outline-secondary flex-fill">
            <i class="bi bi-diagram-3 me-1"></i>📋 เทียบหลายหุ้น
        </a>
    </div>
</div>
</body>
</html>
 