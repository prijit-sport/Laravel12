<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการหุ้นที่ติดตาม | Stock Analyzer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f1f5f9;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --ink: #0f172a;
            --ink-soft: #475569;
            --muted: #94a3b8;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #16a34a;
            --danger: #dc2626;
            --shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04);
        }
 
        body {
            background: var(--bg);
            min-height: 100vh;
            font-family: 'Sarabun', 'Segoe UI', Tahoma, sans-serif;
            color: var(--ink);
            padding: 28px 0 48px;
        }
 
        .container { max-width: 1100px; }
 
        /* Navigation */
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }
        .nav-buttons a {
            flex: 1;
            text-align: center;
            background: var(--card-bg);
            color: var(--ink-soft);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .nav-buttons a:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f8faff;
        }
 
        /* Cards */
        .card {
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 22px;
            background: var(--card-bg);
            overflow: hidden;
        }
        .card-header {
            background: var(--card-bg);
            color: var(--ink);
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
        }
        .card-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h2 i { color: var(--primary); }
        .card-body { padding: 22px 24px; }
 
        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
        }
        .btn-primary:hover { background: var(--primary-dark); }
 
        .btn-danger {
            background: transparent;
            color: var(--danger);
            border: 1px solid #fca5a5;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-danger:hover { background: var(--danger); color: #fff; border-color: var(--danger); }
 
        /* Form */
        .form-control, .form-select {
            border-radius: 9px;
            border: 1px solid var(--border);
            padding: 10px 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .form-label { color: var(--ink-soft); font-size: 14px; margin-bottom: 6px; }
 
        /* Table */
        table { margin-bottom: 0; }
        thead th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--ink-soft);
            font-size: 13px;
            border-bottom: 1px solid var(--border) !important;
            padding: 14px 16px;
            white-space: nowrap;
        }
        tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: var(--ink);
        }
        tbody tr {
            cursor: pointer;
            transition: background-color 0.15s;
        }
        tbody tr:hover { background-color: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        .symbol-cell { font-weight: 700; color: var(--primary); }
 
        .text-success { color: var(--success) !important; font-weight: 600; }
        .text-danger { color: var(--danger) !important; font-weight: 600; }
 
        /* Badges */
        .badge-soft {
            display: inline-block;
            border-radius: 7px;
            padding: 4px 10px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-rsi { background: #f1f5f9; color: var(--ink-soft); }
        .badge-trend { background: #f3f4f6; color: #6b7280; }
        .badge-macd { background: #f1f5f9; color: var(--ink-soft); }
        .summary-cell { color: var(--ink-soft); font-size: 14px; max-width: 280px; }
        .note-cell { color: var(--muted); font-size: 14px; }
 
        /* Empty state */
        .empty-state { text-align: center; padding: 56px 20px; color: var(--muted); }
        .empty-state i { font-size: 54px; margin-bottom: 16px; color: var(--border); }
        .empty-state h4 { color: var(--ink-soft); }
 
        /* Form section */
        .form-section { padding: 0; }
        .form-section .row { row-gap: 16px; }
 
        /* Error row */
        .error-row { background-color: #fef2f2 !important; }
        .error-row td { color: var(--danger); }
 
        .alert { border-radius: 10px; border: none; }
 
        .info-card {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <div class="nav-buttons">
            <a href="/stock/NVDA"><i class="bi bi-graph-up"></i> หน้าวิเคราะห์รายตัว</a>
            <a href="/compare"><i class="bi bi-table"></i> เทียบหลายหุ้น</a>
        </div>
 
        <!-- Alerts -->
        @if ($message = session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
 
        @if ($message = session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
 
        <!-- Add Form -->
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-plus-circle"></i> เพิ่มหุ้นเข้า Watchlist</h2>
            </div>
            <div class="card-body">
                <form action="/watchlist" method="POST" class="form-section">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <label for="symbol" class="form-label"><strong>รหัสหุ้น</strong> (เช่น NVDA, TSM)</label>
                            <input
                                type="text"
                                class="form-control @error('symbol') is-invalid @enderror"
                                id="symbol"
                                name="symbol"
                                placeholder="ใส่รหัสหุ้น..."
                                required
                            >
                            @error('symbol')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="note" class="form-label">หมายเหตุ (ไม่บังคับ)</label>
                            <input
                                type="text"
                                class="form-control"
                                id="note"
                                name="note"
                                placeholder="เช่น หุ้นเทคโนโลยี ที่ติดตามหลัง earnings..."
                            >
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i> เพิ่ม
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
 
        <!-- Watchlist Table -->
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-bookmark-star"></i> รายการของคุณ ({{ count($items) }} หุ้น)</h2>
            </div>
 
            @if (count($items) == 0)
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h4>ยังไม่มีหุ้นในรายการ</h4>
                    <p>เริ่มต้นด้วยการเพิ่มหุ้นที่คุณสนใจเข้าระบบติดตาม</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>หุ้น</th>
                                <th>ราคาล่าสุด</th>
                                <th>เปลี่ยนแปลง %</th>
                                <th>RSI (14)</th>
                                <th>แนวโน้ม</th>
                                <th>MACD</th>
                                <th>สรุปสั้น ๆ</th>
                                <th>หมายเหตุ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $itemData = $data[$item->symbol] ?? null;
                                @endphp
 
                                @if ($itemData && isset($itemData['error']))
                                    <tr class="error-row">
                                        <td class="symbol-cell">{{ $item->symbol }}</td>
                                        <td colspan="7">
                                            <i class="bi bi-exclamation-triangle"></i> {{ $itemData['error'] }}
                                        </td>
                                        <td style="cursor: default;">
                                            <form action="/watchlist/{{ $item->id }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('ลบ {{ $item->symbol }} หรือไม่?')"
                                                >
                                                    <i class="bi bi-trash"></i> ลบ
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @else
                                    <tr onclick="window.location.href = '/stock/{{ $item->symbol }}'">
                                        <td class="symbol-cell">{{ $item->symbol }}</td>
                                        <td>{{ number_format($itemData['close'] ?? 0, 2) }}</td>
                                        <td>
                                            @if (($itemData['percent_change'] ?? 0) > 0)
                                                <span class="text-success">+{{ number_format($itemData['percent_change'], 2) }}%</span>
                                            @else
                                                <span class="text-danger">{{ number_format($itemData['percent_change'] ?? 0, 2) }}%</span>
                                            @endif
                                        </td>
                                        <td><span class="badge-soft badge-rsi">{{ $itemData['rsi'] ?? 'N/A' }}</span></td>
                                        <td><span class="badge-soft badge-trend">{{ $itemData['trend_signal'] ?? 'N/A' }}</span></td>
                                        <td><span class="badge-soft badge-macd">{{ $itemData['macd_signal'] ?? 'N/A' }}</span></td>
                                        <td class="summary-cell">{{ $itemData['summary'] ?? 'ข้อมูลไม่พอ' }}</td>
                                        <td class="note-cell">{{ $item->note ?? '—' }}</td>
                                        <td style="cursor: default;" onclick="event.stopPropagation();">
                                            <form action="/watchlist/{{ $item->id }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('ลบ {{ $item->symbol }} หรือไม่?')"
                                                >
                                                    <i class="bi bi-trash"></i> ลบ
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
 
        <!-- Info -->
        <div class="card">
            <div class="card-body info-card">
                <i class="bi bi-info-circle"></i>
                ข้อมูลราคาอัปเดตทุก 15 นาที • ข้อมูลและการวิเคราะห์เป็นเพียงข้อมูลประกอบการศึกษา ไม่เป็นคำแนะนำลงทุน
            </div>
        </div>
    </div>
 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 