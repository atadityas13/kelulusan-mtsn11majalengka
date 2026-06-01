@extends('layouts.app')

@section('title', 'Admin - Kelulusan MTsN 11 Majalengka')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --sidebar-w: 256px;
        --primary:       #2563eb;
        --primary-dark:  #1d4ed8;
        --primary-light: #eff6ff;
        --sidebar-bg:    #0f172a;
        --sidebar-hover: rgba(255,255,255,0.05);
        --sidebar-active:rgba(255,255,255,0.08);
        --bg:            #f1f5f9;
        --surface:       #ffffff;
        --border:        #e2e8f0;
        --text:          #0f172a;
        --text-muted:    #64748b;
        --success:       #16a34a;
        --warning:       #d97706;
        --danger:        #dc2626;
        --success-bg:    #f0fdf4;
        --warning-bg:    #fffbeb;
        --danger-bg:     #fef2f2;
        --info:          #2563eb;
        --info-bg:       #eff6ff;
        --radius:        10px;
        --shadow-sm:     0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        --shadow:        0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
        --transition:    0.18s ease;
    }
    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        background: var(--bg);
        color: var(--text);
        display: flex;
        min-height: 100vh;
    }

    /* =============================================
       SIDEBAR
    ============================================= */
    .sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        color: #fff;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        display: flex;
        flex-direction: column;
        z-index: 200;
        border-right: 1px solid rgba(255,255,255,0.04);
    }
    .sidebar-brand {
        padding: 20px 18px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .sidebar-brand img { width: 38px; flex-shrink: 0; }
    .sidebar-brand strong { display: block; font-size: 0.88em; font-weight: 600; color: #f1f5f9; line-height: 1.3; }
    .sidebar-brand small { font-size: 0.72em; color: rgba(255,255,255,0.4); }

    .sidebar-section-label {
        padding: 16px 18px 6px;
        font-size: 0.68em;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.3);
    }
    .sidebar-menu { list-style: none; padding: 8px 10px; flex: 1; overflow-y: auto; }
    .sidebar-menu-item a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 10px;
        color: rgba(255,255,255,0.55);
        text-decoration: none;
        font-size: 0.87em;
        font-weight: 500;
        border-radius: 7px;
        transition: background var(--transition), color var(--transition);
        margin-bottom: 2px;
    }
    .sidebar-menu-item a:hover { background: var(--sidebar-hover); color: rgba(255,255,255,0.9); }
    .sidebar-menu-item.active a {
        background: var(--sidebar-active);
        color: #fff;
    }
    .sidebar-menu-item.active a i { color: #60a5fa; }
    .sidebar-menu-item a i { width: 18px; text-align: center; font-size: 0.95em; color: rgba(255,255,255,0.35); transition: color var(--transition); }
    .sidebar-menu-item a:hover i { color: rgba(255,255,255,0.7); }

    .sidebar-footer {
        padding: 14px 18px;
        border-top: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.76em;
        color: rgba(255,255,255,0.3);
    }
    .logout-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        color: rgba(255,255,255,0.45);
        cursor: pointer;
        font-size: 0.85em;
        font-family: inherit;
        padding: 5px 8px;
        border-radius: 6px;
        transition: background var(--transition), color var(--transition);
    }
    .logout-btn:hover { background: rgba(239,68,68,0.12); color: #f87171; }

    /* =============================================
       MAIN PANEL
    ============================================= */
    .main-panel {
        margin-left: var(--sidebar-w);
        flex: 1;
        padding: 28px 32px;
        max-width: calc(100% - var(--sidebar-w));
        min-height: 100vh;
    }
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--border);
    }
    .topbar-left h1 {
        font-size: 1.25em;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
    }
    .topbar-left p {
        font-size: 0.82em;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .topbar-user {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 7px 14px;
        font-size: 0.84em;
        font-weight: 500;
        color: var(--text);
        box-shadow: var(--shadow-sm);
    }
    .topbar-user-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78em;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* =============================================
       ALERTS
    ============================================= */
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.88em;
        font-weight: 500;
        margin-bottom: 20px;
        border: 1px solid;
    }
    .alert i { font-size: 1em; flex-shrink: 0; }
    .alert-success { background: var(--success-bg); color: var(--success); border-color: #bbf7d0; }
    .alert-error   { background: var(--danger-bg);  color: var(--danger);  border-color: #fecaca; }

    /* =============================================
       STAT CARDS
    ============================================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--shadow-sm);
        transition: box-shadow var(--transition), transform var(--transition);
    }
    .stat-card:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
    .stat-info h3 {
        font-size: 0.73em;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    .stat-info p { font-size: 1.9em; font-weight: 700; color: var(--text); line-height: 1; }
    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25em;
        flex-shrink: 0;
    }
    .stat-icon-blue   { background: #dbeafe; color: #2563eb; }
    .stat-icon-green  { background: #dcfce7; color: #16a34a; }
    .stat-icon-orange { background: #fef9c3; color: #ca8a04; }
    .stat-icon-red    { background: #fee2e2; color: #dc2626; }

    /* =============================================
       CARDS
    ============================================= */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 22px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #fafbfd;
    }
    .card-header h2 { font-size: 0.95em; font-weight: 600; color: var(--text); }
    .card-body { padding: 20px; }

    /* =============================================
       TABLES
    ============================================= */
    .table-responsive { width: 100%; overflow-x: auto; }
    table.data-table { width: 100%; border-collapse: collapse; font-size: 0.875em; }
    table.data-table thead th {
        padding: 11px 16px;
        background: #f8fafc;
        color: var(--text-muted);
        font-size: 0.78em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border);
        text-align: left;
        white-space: nowrap;
    }
    table.data-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
        vertical-align: middle;
    }
    table.data-table tbody tr:last-child td { border-bottom: none; }
    table.data-table tbody tr:hover { background: #f8fafc; }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 0.78em;
        font-weight: 600;
        letter-spacing: 0.01em;
    }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-danger  { background: #fee2e2; color: #b91c1c; }
    .badge-warning { background: #fef9c3; color: #a16207; }
    .badge-info    { background: #dbeafe; color: #1d4ed8; }

    .actions-cell { display: flex; gap: 6px; align-items: center; }
    .btn-icon {
        width: 30px; height: 30px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 0.85em;
        transition: background var(--transition), color var(--transition), border-color var(--transition);
    }
    .btn-icon:hover { background: var(--primary-light); color: var(--primary); border-color: #bfdbfe; }
    .btn-icon-danger:hover { background: var(--danger-bg); color: var(--danger); border-color: #fecaca; }

    /* =============================================
       BUTTONS
    ============================================= */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 7px;
        font-family: inherit;
        font-size: 0.865em;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
        line-height: 1.4;
        transition: background var(--transition), box-shadow var(--transition), border-color var(--transition);
        white-space: nowrap;
    }
    .btn-primary {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 1px 2px rgba(37,99,235,0.25);
    }
    .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); box-shadow: 0 3px 8px rgba(37,99,235,0.3); }
    .btn-secondary {
        background: var(--surface);
        border-color: var(--border);
        color: var(--text);
        box-shadow: var(--shadow-sm);
    }
    .btn-secondary:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .btn-danger {
        background: var(--danger-bg);
        color: var(--danger);
        border-color: #fecaca;
    }
    .btn-danger:hover { background: #fecaca; border-color: #f87171; }
    .btn-sm { padding: 5px 11px; font-size: 0.8em; border-radius: 6px; }

    /* =============================================
       FORMS
    ============================================= */
    .search-form { display: flex; gap: 8px; flex-grow: 1; max-width: 380px; }
    .form-control {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: 7px;
        font-family: inherit;
        font-size: 0.875em;
        background: var(--surface);
        color: var(--text);
        outline: none;
        box-shadow: var(--shadow-sm);
        transition: border-color var(--transition), box-shadow var(--transition);
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 16px; }
    .form-group-db { margin-bottom: 16px; }
    .form-group-db label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.82em;
        font-weight: 500;
        color: #374151;
    }

    /* =============================================
       MODALS
    ============================================= */
    .modal {
        position: fixed; inset: 0;
        background: rgba(15,23,42,0.45);
        backdrop-filter: blur(3px);
        z-index: 1000;
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
    }
    .modal-content {
        background: var(--surface);
        border-radius: var(--radius);
        width: 100%; max-width: 580px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        border: 1px solid var(--border);
    }
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        background: #fafbfd;
    }
    .modal-header h3 { font-size: 0.95em; font-weight: 600; color: var(--text); }
    .modal-close {
        background: none; border: none;
        font-size: 1.3em; color: var(--text-muted);
        cursor: pointer; padding: 2px 6px;
        border-radius: 4px;
        transition: background var(--transition), color var(--transition);
    }
    .modal-close:hover { background: #f1f5f9; color: var(--text); }
    .modal-body { padding: 20px; max-height: 68vh; overflow-y: auto; }
    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        display: flex; justify-content: flex-end; gap: 8px;
        background: #f8fafc;
    }

    /* =============================================
       PAGINATION
    ============================================= */
    .pagination-container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .pagination-info { font-size: 0.82em; color: var(--text-muted); }

    /* =============================================
       RESPONSIVE
    ============================================= */
    @media (max-width: 1024px) {
        .sidebar { width: 72px; }
        .sidebar-brand span, .sidebar-section-label, .sidebar-menu-item span, .sidebar-footer span, .logout-btn span { display: none; }
        .sidebar-brand { justify-content: center; padding: 16px 12px; }
        .sidebar-menu { padding: 8px 6px; }
        .sidebar-menu-item a { justify-content: center; padding: 11px; gap: 0; }
        .sidebar-footer { justify-content: center; padding: 12px; }
        .main-panel { margin-left: 72px; max-width: calc(100% - 72px); padding: 20px; }
    }
    @media (max-width: 640px) {
        .topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
        .card-header { flex-direction: column; align-items: stretch; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .main-panel { padding: 16px; }
    }
</style>
@endsection

@section('content')
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('assets/mtsn11majalengka-logo.png') }}" alt="Logo">
            <div>
                <strong>MTsN 11 Majalengka</strong>
                <small>Panel Administrasi</small>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item {{ $activeTab === 'overview' ? 'active' : '' }}">
                <a href="?tab=overview"><i class="fa-solid fa-gauge-high"></i> <span>Ringkasan</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'students' ? 'active' : '' }}">
                <a href="?tab=students"><i class="fa-solid fa-graduation-cap"></i> <span>Data Siswa</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'testimonials' ? 'active' : '' }}">
                <a href="?tab=testimonials"><i class="fa-solid fa-comments"></i> <span>Testimoni</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'teacher_messages' ? 'active' : '' }}">
                <a href="?tab=teacher_messages"><i class="fa-solid fa-chalkboard-user"></i> <span>Pesan Guru</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'history' ? 'active' : '' }}">
                <a href="?tab=history"><i class="fa-solid fa-clock-rotate-left"></i> <span>Log Riwayat</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'settings' ? 'active' : '' }}">
                <a href="?tab=settings"><i class="fa-solid fa-sliders"></i> <span>Pengaturan</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <span>v3.0</span>
            <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="logout-btn" onclick="return confirm('Yakin ingin keluar?')">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-panel">
        <div class="topbar">
            <div class="topbar-left">
                <h1>
                    @if($activeTab === 'overview') Ringkasan & Statistik
                    @elseif($activeTab === 'students') Manajemen Data Siswa
                    @elseif($activeTab === 'testimonials') Moderasi Testimoni
                    @elseif($activeTab === 'teacher_messages') Pesan Guru
                    @elseif($activeTab === 'history') Log Pengecekan
                    @elseif($activeTab === 'settings') Pengaturan Sistem
                    @endif
                </h1>
                <p>Sistem Kelulusan MTsN 11 Majalengka &bull; {{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="topbar-user">
                <div class="topbar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <span>{{ Auth::user()->name }}</span>
            </div>
        </div>

        <!-- Notifikasi Alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') ?: $errors->first() }}</span>
            </div>
        @endif

        <!-- ========================================================
             TAB 1: OVERVIEW (RINGKASAN)
             ======================================================== -->
        @if($activeTab === 'overview')
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Roster</h3>
                        <p>{{ $statTotalStudents }}</p>
                    </div>
                    <div class="stat-icon stat-icon-blue"><i class="fa-solid fa-users"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Lulus</h3>
                        <p>{{ $statTotalLulus }}</p>
                    </div>
                    <div class="stat-icon stat-icon-green"><i class="fa-solid fa-user-check"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Pending Testi</h3>
                        <p>{{ $statTotalPendingTesti }}</p>
                    </div>
                    <div class="stat-icon stat-icon-orange"><i class="fa-solid fa-comment-slash"></i></div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Log Checks</h3>
                        <p>{{ $statTotalChecks }}</p>
                    </div>
                    <div class="stat-icon stat-icon-red"><i class="fa-solid fa-magnifying-glass"></i></div>
                </div>
            </div>

            <!-- 5 Log Pengecekan Terkini -->
            <div class="card">
                <div class="card-header">
                    <h2>Aktivitas Pengecekan Kelulusan Terakhir (Real-time)</h2>
                    <a href="?tab=history" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8em;"><i class="fa-solid fa-arrow-right"></i> Log Lengkap</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Tahun Ajaran</th>
                                <th>No. Peserta</th>
                                <th>Nama Lengkap Siswa</th>
                                <th>Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recentChecks->isNotEmpty())
                                @foreach($recentChecks as $log)
                                    <tr>
                                        <td>{{ $log->timestamp->format('d M Y H:i:s') }}</td>
                                        <td><span class="badge badge-info">{{ $log->academicYear->year ?? '-' }}</span></td>
                                        <td>{{ $log->nomor_peserta }}</td>
                                        <td><strong>{{ $log->student_name }}</strong></td>
                                        <td>
                                            <span class="badge {{ $log->result === 'Lulus' ? 'badge-success' : ($log->result === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ $log->result }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="5" style="text-align: center; color: #888;">Belum ada riwayat pengecekan.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================================
             TAB 2: STUDENTS (DATA SISWA)
             ======================================================== -->
        @elseif($activeTab === 'students')
            <div class="card">
                <div class="card-header">
                    <!-- Filter Tahun Ajaran & Pencarian -->
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; flex-grow:1;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <label style="font-weight:600; font-size:0.9em; white-space:nowrap;">Tahun Ajaran:</label>
                            <select class="form-control" style="width: 140px; padding: 8px 12px;" onchange="window.location.href='?tab=students&year_id='+this.value">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>{{ $year->year }} {!! $year->is_active ? '(Aktif)' : '' !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <form action="admin_dashboard.php" method="GET" class="search-form">
                            <input type="hidden" name="tab" value="students">
                            <input type="hidden" name="year_id" value="{{ $selectedYearId }}">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama, NISN, Kelas..." value="{{ $search }}">
                            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </div>

                    <!-- Tindakan CRUD / Import -->
                    @if($selectedYear)
                        <div style="display:flex; gap:10px; margin-top:5px;">
                            <button type="button" class="btn btn-secondary" onclick="openModal('importModal')"><i class="fa-solid fa-file-import"></i> Import Siswa</button>
                            <button type="button" class="btn btn-primary" onclick="openAddStudentModal()"><i class="fa-solid fa-plus"></i> Tambah Siswa</button>
                        </div>
                    @endif
                </div>

                @if($selectedYear)
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Peserta</th>
                                    <th>NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas</th>
                                    <th>Hasil</th>
                                    <th>Waktu Rilis Batch</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($studentsList->isNotEmpty())
                                    @php $no = ($studentsList->currentPage() - 1) * $studentsList->perPage() + 1; @endphp
                                    @foreach($studentsList as $student)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td><strong>{{ $student->nomor_peserta }}</strong></td>
                                            <td>{{ $student->nisn }}</td>
                                            <td>{{ $student->nama }}</td>
                                            <td>{{ $student->kelas }}</td>
                                            <td>
                                                <span class="badge {{ $student->status_kelulusan === 'Lulus' ? 'badge-success' : ($student->status_kelulusan === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning') }}">
                                                    {{ $student->status_kelulusan }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $student->release_timestamp ? $student->release_timestamp->format('d-m-Y H:i') : 'Default (Ikut Tanggal Target)' }}</small>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-icon" title="Edit" onclick='openEditStudentModal({{ json_encode($student) }})'><i class="fa-solid fa-pen-to-square"></i></button>
                                                    <form action="{{ route('admin.student.delete', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini secara permanen?')" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="8" style="text-align: center; color: #888;">Data siswa kosong atau tidak ditemukan pada tahun ajaran ini.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginasi Roster Siswa -->
                    @if($studentsList->hasPages())
                        <div class="card-body" style="padding: 0 24px 24px 24px;">
                            <div class="pagination-container">
                                <span class="pagination-info">Menampilkan data {{ $studentsList->firstItem() }} - {{ $studentsList->lastItem() }} dari total {{ $studentsList->total() }} data siswa</span>
                                <div class="pagination-links">
                                    {{ $studentsList->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="card-body" style="text-align:center; color: #888;">
                        <p>Silakan buat Tahun Ajaran terlebih dahulu di menu <strong>Pengaturan</strong> sebelum mengelola data roster kelulusan.</p>
                    </div>
                @endif
            </div>

            @if($selectedYear)
                <!-- MODAL IMPORT DATA SISWA -->
                <div id="importModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <form action="{{ route('admin.student.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <div class="modal-header">
                                <h3>Import Roster Siswa Massal (JSON) - TA {{ $selectedYear->year }}</h3>
                                <button type="button" class="modal-close" onclick="closeModal('importModal')">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p style="font-size:0.9em; line-height: 1.5; color: var(--text-muted); margin-bottom: 20px;">
                                    Silakan unggah berkas JSON data kelulusan. Sistem otomatis memasukkan data baru dan mengabaikan data duplikat berdasarkan NISN/Nomor Peserta.
                                </p>
                                <div class="form-group-db">
                                    <label for="json_file">Pilih Berkas JSON Data Roster Siswa</label>
                                    <input type="file" id="json_file" name="json_file" class="form-control" accept=".json" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('importModal')">Batal</button>
                                <button type="submit" class="btn btn-primary">Mulai Import</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MODAL CRUD SISWA (ADD / EDIT) -->
                <div id="studentFormModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <form action="{{ route('admin.student.add') }}" method="POST" id="studentForm">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <input type="hidden" name="id" id="studentId" value="">
                            
                            <div class="modal-header">
                                <h3 id="studentModalTitle">Tambah Data Roster Siswa</h3>
                                <button type="button" class="modal-close" onclick="closeModal('studentFormModal')">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-row">
                                    <div class="form-group-db">
                                        <label for="nomor_peserta">Nomor Peserta Ujian</label>
                                        <input type="text" id="nomor_peserta" name="nomor_peserta" class="form-control" placeholder="Contoh: 25-10-10-2-0089-0001" required>
                                    </div>
                                    <div class="form-group-db">
                                        <label for="nisn">NISN Siswa</label>
                                        <input type="text" id="nisn" name="nisn" class="form-control" placeholder="Contoh: 0098765432" required>
                                    </div>
                                </div>

                                <div class="form-group-db">
                                    <label for="nama">Nama Lengkap Siswa</label>
                                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap siswa" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group-db">
                                        <label for="jenis_kelamin">Jenis Kelamin</label>
                                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group-db">
                                        <label for="kelas">Kelas Siswa</label>
                                        <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Contoh: IX.1" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group-db">
                                        <label for="tempat_lahir">Tempat Lahir</label>
                                        <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" placeholder="Contoh: Majalengka">
                                    </div>
                                    <div class="form-group-db">
                                        <label for="tanggal_lahir">Tanggal Lahir</label>
                                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group-db">
                                        <label for="status_kelulusan">Status Kelulusan</label>
                                        <select id="status_kelulusan" name="status_kelulusan" class="form-control">
                                            <option value="Lulus">Lulus</option>
                                            <option value="Tidak Lulus">Tidak Lulus</option>
                                            <option value="Ditangguhkan">Ditangguhkan</option>
                                        </select>
                                    </div>
                                    <div class="form-group-db">
                                        <label for="release_timestamp">Waktu Rilis Batch</label>
                                        <input type="datetime-local" id="release_timestamp" name="release_timestamp" class="form-control" step="1">
                                        <small style="color:var(--text-muted); font-size:0.75em; display:block; margin-top:4px;">Kosongkan jika ingin mengikuti target default kelulusan.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('studentFormModal')">Batal</button>
                                <button type="submit" class="btn btn-primary" id="studentFormSubmitBtn">Simpan Roster</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function openAddStudentModal() {
                        document.getElementById('studentForm').reset();
                        document.getElementById('studentForm').action = "{{ route('admin.student.add') }}";
                        document.getElementById('studentId').value = '';
                        document.getElementById('studentModalTitle').innerText = 'Tambah Roster Siswa';
                        document.getElementById('studentFormSubmitBtn').innerText = 'Simpan Roster';
                        openModal('studentFormModal');
                    }

                    function openEditStudentModal(student) {
                        document.getElementById('studentForm').reset();
                        // Ganti action URL form dinamis
                        document.getElementById('studentForm').action = "/admin/students/edit/" + student.id;
                        document.getElementById('studentId').value = student.id;
                        document.getElementById('studentModalTitle').innerText = 'Edit Roster Siswa';
                        document.getElementById('studentFormSubmitBtn').innerText = 'Perbarui Roster';

                        document.getElementById('nomor_peserta').value = student.nomor_peserta;
                        document.getElementById('nisn').value = student.nisn;
                        document.getElementById('nama').value = student.nama;
                        document.getElementById('jenis_kelamin').value = student.jenis_kelamin;
                        document.getElementById('kelas').value = student.kelas;
                        document.getElementById('tempat_lahir').value = student.tempat_lahir;
                        
                        if (student.tanggal_lahir) {
                            // Potong timestamp jika ada
                            document.getElementById('tanggal_lahir').value = student.tanggal_lahir.substring(0, 10);
                        }
                        document.getElementById('status_kelulusan').value = student.status_kelulusan;

                        if (student.release_timestamp) {
                            var dateStr = student.release_timestamp.replace(' ', 'T').substring(0, 19);
                            document.getElementById('release_timestamp').value = dateStr;
                        } else {
                            document.getElementById('release_timestamp').value = '';
                        }

                        openModal('studentFormModal');
                    }
                </script>
            @endif

        <!-- ========================================================
             TAB 3: TESTIMONIALS (MODERASI TESTIMONI)
             ======================================================== -->
        @elseif($activeTab === 'testimonials')
            <div class="card">
                <div class="card-header">
                    <h2>Moderasi Kesan & Pesan Kelulusan Siswa</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal Kirim</th>
                                <th>Nama Pengirim</th>
                                <th>Isi Kesan/Pesan</th>
                                <th>Likes</th>
                                <th>Status</th>
                                <th>Tindakan Moderasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($testimonialsList->isNotEmpty())
                                @foreach($testimonialsList as $t)
                                    <tr>
                                        <td><small>{{ $t->date->format('d-m-Y H:i') }}</small></td>
                                        <td><strong>{{ $t->name }}</strong></td>
                                        <td style="max-width:300px; font-style:italic;">"{{ $t->message }}"</td>
                                        <td><i class="fa-solid fa-heart" style="color:var(--danger);"></i> {{ $t->likes }}</td>
                                        <td>
                                            <span class="badge {{ $t->status === 'approved' ? 'badge-success' : ($t->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $t->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                @if($t->status !== 'approved')
                                                    <form action="{{ route('admin.testimonial.approve', $t->id) }}" method="POST" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn-icon" style="color:var(--success); border-color:var(--success);" title="Setujui (Terbitkan)"><i class="fa-solid fa-check"></i></button>
                                                    </form>
                                                @endif
                                                @if($t->status !== 'rejected')
                                                    <form action="{{ route('admin.testimonial.reject', $t->id) }}" method="POST" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn-icon" style="color:var(--warning); border-color:var(--warning);" title="Tolak"><i class="fa-solid fa-ban"></i></button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.testimonial.delete', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus testimoni ini secara permanen beserta komentarnya?')" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6" style="text-align: center; color: #888;">Belum ada data testimoni siswa.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================================
             TAB 4: TEACHER MESSAGES (PESAN GURU)
             ======================================================== -->
        @elseif($activeTab === 'teacher_messages')
            <div class="stats-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">
                <!-- Form Tulis Pesan -->
                <div class="card">
                    <div class="card-header">
                        <h2>Tulis Pesan Guru Baru</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.teacher-message.add') }}" method="POST">
                            @csrf
                            <div class="form-group-db">
                                <label for="name">Nama Lengkap Guru (Gelar)</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Tin Sumartini, S.Pd." required>
                            </div>
                            <div class="form-group-db">
                                <label for="message">Isi Pesan / Motivasi</label>
                                <textarea id="message" name="message" class="form-control" rows="6" placeholder="Tulis petuah Anda di sini..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-paper-plane"></i> Terbitkan Pesan</button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Pesan Guru -->
                <div class="card">
                    <div class="card-header">
                        <h2>Pesan Guru yang Diterbitkan</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Guru</th>
                                    <th>Isi Pesan</th>
                                    <th>Likes</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($messagesList->isNotEmpty())
                                    @foreach($messagesList as $msg)
                                        <tr>
                                            <td><small>{{ $msg->date->format('d-m-Y H:i') }}</small></td>
                                            <td><strong>{{ $msg->name }}</strong></td>
                                            <td style="max-width:250px; font-style:italic;">"{{ $msg->message }}"</td>
                                            <td><i class="fa-solid fa-heart" style="color:var(--danger);"></i> {{ $msg->likes }}</td>
                                            <td>
                                                <div class="actions-cell">
                                                    <form action="{{ route('admin.teacher-message.delete', $msg->id) }}" method="POST" onsubmit="return confirm('Hapus pesan guru ini?')" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" style="text-align: center; color: #888;">Belum ada pesan dari guru.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================================
             TAB 5: LOG PENGECEKAN (HISTORY)
             ======================================================== -->
        @elseif($activeTab === 'history')
            <div class="card">
                <div class="card-header">
                    <form action="admin_dashboard.php" method="GET" class="search-form">
                        <input type="hidden" name="tab" value="history">
                        <input type="text" name="history_search" class="form-control" placeholder="Cari Nama Siswa, No. Peserta..." value="{{ $historySearch }}">
                        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                    
                    <form action="{{ route('admin.history.clear') }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN: Bersihkan seluruh log pencarian kelulusan secara permanen?')">
                        @csrf
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-broom"></i> Bersihkan Seluruh Log</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Waktu Pengecekan</th>
                                <th>Tahun Ajaran</th>
                                <th>Nomor Peserta</th>
                                <th>Nama Lengkap Siswa</th>
                                <th>Hasil Pencarian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($historyList->isNotEmpty())
                                @php $no = ($historyList->currentPage() - 1) * $historyList->perPage() + 1; @endphp
                                @foreach($historyList as $log)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $log->timestamp->format('d M Y H:i:s') }}</td>
                                        <td><span class="badge badge-info">{{ $log->academicYear->year ?? '-' }}</span></td>
                                        <td><strong>{{ $log->nomor_peserta }}</strong></td>
                                        <td>{{ $log->student_name }}</td>
                                        <td>
                                            <span class="badge {{ $log->result === 'Lulus' ? 'badge-success' : ($log->result === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ $log->result }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6" style="text-align: center; color: #888;">Log pencarian kelulusan kosong.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($historyList->hasPages())
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <div class="pagination-container">
                            <span class="pagination-info">Menampilkan data {{ $historyList->firstItem() }} - {{ $historyList->lastItem() }} dari total {{ $historyList->total() }} riwayat pencarian</span>
                            <div class="pagination-links">
                                {{ $historyList->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        <!-- ========================================================
             TAB 6: SETTINGS (PENGATURAN)
             ======================================================== -->
        @elseif($activeTab === 'settings')
            <div class="stats-grid" style="grid-template-columns: 1fr 1fr; align-items: start;">
                
                <!-- 1. Pengaturan Target Waktu Pengumuman per Tahun Ajaran -->
                <div class="card">
                    <div class="card-header">
                        <h2>Sistem Target Kelulusan & Pengumuman</h2>
                    </div>
                    <div class="card-body">
                        @if($activeYear)
                            <form action="{{ route('admin.settings.save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="academic_year_id" value="{{ $activeYear->id }}">

                                <div class="form-group-db">
                                    <label>Tahun Ajaran Aktif</label>
                                    <input type="text" class="form-control" style="background:#f1f5f9; cursor:not-allowed;" readonly value="TA {{ $activeYear->year }}">
                                </div>

                                <div class="form-group-db">
                                    <label for="target_date">Tanggal &amp; Waktu Pengumuman Dibuka</label>
                                    <input type="text" id="target_date" name="target_date" class="form-control" placeholder="Format: YYYY-MM-DD HH:MM:SS" value="{{ $activeYear->target_date }}" required>
                                    <small style="color:var(--text-muted); font-size:0.75em; display:block; margin-top:5px;">Contoh format waktu: <code>2025-06-02 15:00:00</code> (WIB)</small>
                                </div>

                                <div class="form-group-db" style="margin: 25px 0 30px 0;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" style="width:20px; height:20px; accent-color:var(--primary);" {{ $activeYear->maintenance_mode ? 'checked' : '' }}>
                                        <label for="maintenance_mode" style="user-select:none; margin:0; cursor:pointer;"><strong>Aktifkan Mode Maintenance</strong></label>
                                    </div>
                                    <small style="color:var(--text-muted); font-size:0.75em; display:block; margin-top:5px; margin-left: 32px;">Menyembunyikan form kelulusan di halaman depan dan mengunci akses siswa sementara.</small>
                                </div>

                                <hr style="border:none; border-top:1px solid var(--border); margin: 20px 0;">
                                <p style="font-size:0.8em; color:var(--text-muted); margin-bottom:16px;">Perubahan pada Kepala Madrasah akan langsung tampil di halaman depan setelah disimpan.</p>

                                <div class="form-group-db">
                                    <label for="kepala_nama"><i class="fa-solid fa-user-tie" style="margin-right:6px; color:var(--primary);"></i> Nama Kepala Madrasah</label>
                                    <input type="text" id="kepala_nama" name="kepala_nama" class="form-control" placeholder="Contoh: H. Asep Awaludin, S.Pd., M.M." value="{{ $settings['kepala_nama'] }}" required>
                                </div>

                                <div class="form-group-db">
                                    <label for="kepala_jabatan"><i class="fa-solid fa-id-badge" style="margin-right:6px; color:var(--primary);"></i> Jabatan</label>
                                    <input type="text" id="kepala_jabatan" name="kepala_jabatan" class="form-control" placeholder="Contoh: Kepala MTsN 11 Majalengka" value="{{ $settings['kepala_jabatan'] }}" required>
                                </div>

                                <div class="form-group-db">
                                    <label for="kepala_pesan"><i class="fa-solid fa-message" style="margin-right:6px; color:var(--primary);"></i> Pesan Kepala Madrasah</label>
                                    <textarea id="kepala_pesan" name="kepala_pesan" class="form-control" rows="5" placeholder="Tulis sambutan atau pesan dari Kepala Madrasah..." required style="resize:vertical;">{{ $settings['kepala_pesan'] }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Simpan Semua Konfigurasi</button>
                            </form>
                        @else
                            <p style="text-align:center; color:#888;">Silakan buat dan aktifkan periode Tahun Ajaran terlebih dahulu untuk mengatur tanggal target rilis.</p>
                        @endif
                    </div>
                </div>

                <!-- 2. Ubah Password Administrator -->
                <div class="card">
                    <div class="card-header">
                        <h2>Ubah Kata Sandi Administrator</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.password') }}" method="POST">
                            @csrf
                            <div class="form-group-db">
                                <label for="current_password">Kata Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Masukkan kata sandi lama Anda" required>
                            </div>
                            <div class="form-group-db">
                                <label for="new_password">Kata Sandi Baru</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Kata sandi baru (minimal 6 karakter)" required>
                            </div>
                            <div class="form-group-db">
                                <label for="new_password_confirmation">Ulangi Kata Sandi Baru</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-key"></i> Perbarui Kata Sandi</button>
                        </form>
                    </div>
                </div>

                <!-- 3. Kelola Tahun Ajaran (TAMBAH / AKTIFKAN) -->
                <div class="card" style="grid-column: 1 / span 2;">
                    <div class="card-header">
                        <h2>Kelola Tahun Ajaran / Periode Kelulusan</h2>
                        <button type="button" class="btn btn-primary" onclick="openModal('addYearModal')"><i class="fa-solid fa-plus"></i> Tambah Tahun Ajaran Baru</button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Target Tanggal Rilis</th>
                                    <th>Status</th>
                                    <th>Tindakan Operasional</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($academicYears as $year)
                                    <tr>
                                        <td>{{ $year->id }}</td>
                                        <td><strong>Tahun Ajaran {{ $year->year }}</strong></td>
                                        <td>{{ $year->target_date }}</td>
                                        <td>
                                            <span class="badge {{ $year->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $year->is_active ? 'Aktif Pengecekan' : 'Arsip Non-aktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                @if(!$year->is_active)
                                                    <form action="{{ route('admin.year.activate', $year->id) }}" method="POST" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 12px; font-size:0.85em; font-weight:600; color:var(--success); border-color:var(--success);"><i class="fa-solid fa-power-off"></i> Aktifkan</button>
                                                    </form>
                                                    <form action="{{ route('admin.year.delete', $year->id) }}" method="POST" onsubmit="return confirm('⚠️ TINDAKAN SANGAT KRITIS: Hapus Tahun Ajaran {{ $year->year }}? SELURUH data siswa kelulusan yang terikat di tahun ajaran ini akan ikut TERHAPUS permanen!')" style="margin:0;">
                                                        @csrf
                                                        <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <span style="font-size:0.85em; font-weight:600; color:var(--success);"><i class="fa-solid fa-circle-check"></i> Sedang Digunakan</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL TAMBAH TAHUN AJARAN -->
            <div id="addYearModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <form action="{{ route('admin.year.add') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h3>Tambah Periode Tahun Ajaran Baru</h3>
                            <button type="button" class="modal-close" onclick="closeModal('addYearModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group-db">
                                <label for="year">Nama Tahun Ajaran</label>
                                <input type="text" id="year" name="year" class="form-control" placeholder="Contoh: 2025/2026" required max="9">
                                <small style="color:var(--text-muted); font-size:0.78em; display:block; margin-top:4px;">Harus format standard 9 karakter seperti: <code>2025/2026</code>.</small>
                            </div>
                            <div class="form-group-db">
                                <label for="target_date_new">Target Waktu Rilis Kelulusan</label>
                                <input type="text" id="target_date_new" name="target_date" class="form-control" placeholder="Format: YYYY-MM-DD HH:MM:SS" required value="2026-06-02 15:00:00">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addYearModal')">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Tahun Ajaran</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </main>

    <!-- JS GLOBAL DI DASHBOARD -->
    <script>
        function openModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
@endsection
