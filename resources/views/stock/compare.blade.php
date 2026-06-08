<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เทียบหลายหุ้น - วิเคราะห์หุ้น AI</title>
 
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
        .compare-table {
            font-size: 0.9rem;
        }
        .compare-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .compare-table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .badge-sm {
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-diagram-3 text-primary"></i>
                เทียบหลายหุ้น
            </h3>
            <div class="text-muted">เปรียบเทียบราคา, ตัวบ่งชี้, และแนวโน้มของหุ้นหลายตัวพร้อมกัน</div>
        </div>
 
        <div class="text-end">
            <div class="d-flex gap-2">
                <a href="{{ url('/watchlist') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-bookmark-star me-1"></i>
                    รายการของฉัน
                </a>
                <a href="{{ url('/stock/NVDA') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    กลับไปวิเคราะห์รายตัว
                </a>
            </div>
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
            <form method="GET" action="{{ url('/compare') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label">กรอกรายชื่อหุ้น (คั่นด้วยจุลภาค, สูงสุด 6 ตัว)</label>
                    <input type="text" name="symbols" class="form-control text-uppercase" value="{{ $userSymbolsInput }}" placeholder="NVDA,TSM,MU,VRT,AVGO" />
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>
                        เทียบ
                    </button>
                </div>
            </form>

            <div class="mt-3">
                <div class="text-muted small mb-2">ชุดหุ้นยอดนิยม:</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ([
                        ['symbols' => 'NVDA,TSM,MU,VRT,AVGO', 'label' => 'ชิป & หน่วยความจำ'],
                        ['symbols' => 'AAPL,MSFT,GOOGL,META,NVDA', 'label' => 'Big Tech'],
                        ['symbols' => 'TSM,SAMSUNG,INTEL,QUALCOMM,BROADCOM', 'label' => 'ผู้ผลิตชิป'],
                    ] as $preset)
                        <a href="{{ url('/compare?symbols=' . $preset['symbols']) }}" class="btn btn-outline-secondary quick-symbol-btn">
                            {{ $preset['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if (!empty($symbols) && !empty($comparison))
        <div class="card card-glow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover compare-table">
                        <thead class="table-light">
                            <tr>
                                <th>หุ้น</th>
                                <th>ราคาล่าสุด</th>
                                <th>เปลี่ยนแปลง</th>
                                <th>RSI (14)</th>
                                <th>แนวโน้ม</th>
                                <th>MACD</th>
                                <th class="text-nowrap">สรุปสั้น ๆ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($symbols as $symbol)
                                @php($data = $comparison[$symbol] ?? null)
                                @if ($data === null)
                                    <tr class="table-light">
                                        <td colspan="7" class="text-muted text-center">ไม่มีข้อมูล</td>
                                    </tr>
                                @elseif (($data['ok'] ?? false) !== true)
                                    <tr class="table-light">
                                        <td><strong>{{ $symbol }}</strong></td>
                                        <td colspan="6" class="text-muted">{{ $data['error'] ?? 'ไม่สามารถดึงข้อมูลได้' }}</td>
                                    </tr>
                                @else
                                    <tr onclick="window.location.href='{{ url('/stock/' . $symbol) }}';">
                                        <td>
                                            <strong>{{ $symbol }}</strong><br>
                                            <small class="text-muted">{{ $data['name'] ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @php($close = $data['close'] ?? null)
                                            @if ($close !== null)
                                                ${{ number_format($close, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php($pct = $data['percent_change'] ?? null)
                                            @if ($pct !== null)
                                                @php($isUp = $pct >= 0)
                                                <span class="text-{{ $isUp ? 'success' : 'danger' }}">
                                                    {{ number_format($pct, 2) }}%
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php($rsi = $data['rsi'] ?? null)
                                            @if ($rsi !== null)
                                                <span class="badge badge-sm bg-{{ $data['rsi_color'] ?? 'secondary' }}">
                                                    {{ number_format($rsi, 1) }}
                                                </span><br>
                                                <small>{{ $data['rsi_signal'] ?? '-' }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php($sma50 = $data['sma50'] ?? null)
                                            @if ($sma50 !== null)
                                                <span class="badge badge-sm bg-{{ $data['trend_color'] ?? 'secondary' }}">
                                                    {{ $data['trend_signal'] ?? '-' }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php($macd = $data['macd_histogram'] ?? null)
                                            @if ($macd !== null)
                                                <span class="badge badge-sm bg-{{ $data['macd_color'] ?? 'secondary' }}">
                                                    {{ $data['macd_signal'] ?? '-' }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <small class="text-muted">{{ Str::limit($data['summary'] ?? '-', 40, '...') }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    คลิกบนแถวเพื่อดูรายละเอียดเต็มของหุ้นนั้น | ข้อมูลจาก Twelve Data | Cache 10 นาที
                </div>
            </div>
        </div>
    @endif

    <div class="mt-4 p-3 bg-white rounded border">
        <div class="small text-muted">
            <strong>หมายเหตุ:</strong> ข้อมูลและการวิเคราะห์เป็นเพียงข้อมูลประกอบการศึกษา ไม่ใช่คำแนะนำการลงทุน ผู้ใช้ควรทำการศึกษาวิจัยเพิ่มเติมและปรึกษาผู้เชี่ยวชาญก่อนตัดสินใจลงทุน
        </div>
    </div>
 
</div>
</body>
</html>
