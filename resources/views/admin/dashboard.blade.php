@extends('layouts.app')

@section('title', 'Dasbor Administrasi - MTsN 11 Majalengka')

@section('styles')
<style>
    :root {
        --sidebar-width: 260px;
        --primary: #2563eb;
        --primary-light: #eff6ff;
        --secondary: #1e293b;
        --bg-body: #f8fafc;
        --card-bg: #ffffff;
        --border: #e2e8f0;
        --text-dark: #0f172a;
        --text-muted: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background-color: var(--bg-body) !important;
        display: flex;
        min-height: 100vh;
    }

    /* --- SIDEBAR --- */
    .sidebar {
        width: var(--sidebar-width);
        background-color: var(--secondary);
        color: #fff;
        position: fixed;
        top: 0; bottom: 0; left: 0;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        transition: var(--transition);
    }

    .sidebar-brand {
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-brand img { width: 42px; height: auto; }
    .sidebar-brand span { font-weight: 700; font-size: 1.05em; letter-spacing: 0.5px; line-height: 1.2; }

    .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; overflow-y: auto; }
    .sidebar-menu-item a { display: flex; align-items: center; gap: 14px; padding: 14px 24px; color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.92em; font-weight: 500; transition: var(--transition); border-left: 4px solid transparent; }
    .sidebar-menu-item a:hover { background-color: rgba(255,255,255,0.04); color: #fff; }
    .sidebar-menu-item.active a { background-color: rgba(255,255,255,0.06); color: #fff; border-left-color: var(--primary); }
    .sidebar-menu-item a i { font-size: 1.15em; width: 20px; text-align: center; }

    .sidebar-footer { padding: 20px 24px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.85em; color: rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: space-between; }
    .sidebar-footer button { background: none; border: none; color: var(--danger); font-size: 1.2em; cursor: pointer; transition: var(--transition); }
    .sidebar-footer button:hover { transform: scale(1.15); }

    /* --- MAIN CONTENT AREA --- */
    .main-panel { margin-left: var(--sidebar-width); flex-grow: 1; padding: 30px; box-sizing: border-box; max-width: calc(100% - var(--sidebar-width)); }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
    .header h1 { font-size: 1.7em; margin: 0; font-weight: 700; color: var(--text-dark); letter-spacing: -0.5px; }

    .user-widget { display: flex; align-items: center; gap: 12px; background: #fff; padding: 8px 16px; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border); font-size: 0.9em; font-weight: 500; }
    .user-widget i { color: var(--primary); font-size: 1.1em; }

    /* Alerts */
    .alert { padding: 16px 20px; border-radius: 12px; font-size: 0.92em; font-weight: 500; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
    .alert-success { background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--success); }
    .alert-error { background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--danger); }

    /* --- STATS CARDS --- */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background-color: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; transition: var(--transition); }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .stat-info h3 { margin: 0; color: var(--text-muted); font-size: 0.88em; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-info p { margin: 8px 0 0 0; font-size: 1.8em; font-weight: 700; color: var(--text-dark); line-height: 1; }
    .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4em; }
    .stat-icon-blue { background-color: #eff6ff; color: #3b82f6; }
    .stat-icon-green { background-color: #ecfdf5; color: #10b981; }
    .stat-icon-orange { background-color: #fffbeb; color: #f59e0b; }
    .stat-icon-red { background-color: #fef2f2; color: #ef4444; }

    /* --- CARDS & PANELS --- */
    .card { background-color: var(--card-bg); border-radius: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 30px; overflow: hidden; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
    .card-header h2 { margin: 0; font-size: 1.15em; font-weight: 600; color: var(--text-dark); }
    .card-body { padding: 24px; }

    /* --- DATA TABLES --- */
    .table-responsive { width: 100%; overflow-x: auto; }
    table.data-table { width: 100%; border-collapse: collapse; font-size: 0.9em; text-align: left; }
    table.data-table th, table.data-table td { padding: 14px 18px; border-bottom: 1px solid var(--border); }
    table.data-table th { background-color: #f8fafc; color: var(--text-muted); font-weight: 600; font-size: 0.85em; text-transform: uppercase; letter-spacing: 0.5px; }
    table.data-table tbody tr:hover { background-color: #f8fafc; }

    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.8em; font-weight: 600; }
    .badge-success { background-color: #d1fae5; color: #065f46; }
    .badge-danger { background-color: #fee2e2; color: #991b1b; }
    .badge-warning { background-color: #fef3c7; color: #92400e; }
    .badge-info { background-color: #e0f2fe; color: #075985; }

    .actions-cell { display: flex; gap: 8px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); }
    .btn-icon:hover { color: var(--primary); border-color: var(--primary); background-color: var(--primary-light); }
    .btn-icon-danger:hover { color: var(--danger); border-color: var(--danger); background-color: #fee2e2; }

    /* --- BUTTONS --- */
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-family: inherit; font-size: 0.9em; font-weight: 500; cursor: pointer; transition: var(--transition); border: 1px solid transparent; text-decoration: none; }
    .btn-primary { background-color: var(--primary); color: #fff; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
    .btn-primary:hover { background-color: #4338ca; transform: translateY(-1px); }
    .btn-secondary { background-color: #fff; border-color: var(--border); color: var(--text-dark); }
    .btn-secondary:hover { background-color: #f8fafc; border-color: var(--text-muted); }
    .btn-danger { background-color: var(--danger); color: #fff; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }
    .btn-danger:hover { background-color: #dc2626; transform: translateY(-1px); }

    /* --- FORMS --- */
    .search-form { display: flex; gap: 10px; flex-grow: 1; max-width: 400px; }
    .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-family: inherit; font-size: 0.9em; box-sizing: border-box; background-color: #fff; color: var(--text-dark); outline: none; transition: var(--transition); }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
    
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 18px; }
    .form-group-db { margin-bottom: 18px; text-align: left; }
    .form-group-db label { display: block; margin-bottom: 6px; font-size: 0.88em; font-weight: 500; color: var(--text-dark); }

    /* --- MODALS --- */
    .modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; }
    .modal-content { background: #fff; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); width: 100%; max-width: 600px; overflow: hidden; }
    .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-header h3 { margin: 0; font-size: 1.15em; font-weight: 600; color: var(--text-dark); }
    .modal-close { background: none; border: none; font-size: 1.4em; color: var(--text-muted); cursor: pointer; }
    .modal-close:hover { color: var(--danger); }
    .modal-body { padding: 24px; max-height: 70vh; overflow-y: auto; }
    .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; background-color: #f8fafc; }

    /* --- RESPONSIVITAS --- */
    @media (max-width: 992px) {
        .sidebar { width: 70px; }
        .sidebar-brand span, .sidebar-footer span, .sidebar-menu-item span { display: none; }
        .sidebar-brand { padding: 15px; justify-content: center; }
        .sidebar-menu-item a { padding: 15px; justify-content: center; border-left-width: 3px; }
        .sidebar-footer { padding: 15px; justify-content: center; }
        .main-panel { margin-left: 70px; max-width: calc(100% - 70px); padding: 20px; }
    }
    @media (max-width: 600px) {
        .header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .user-widget { width: 100%; justify-content: center; }
        .card-header { flex-direction: column; align-items: stretch; }
        .search-form { max-width: 100%; }
    }
</style>
@endsection

@section('content')
    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('assets/mtsn11majalengka-logo.png') }}" alt="Logo">
            <span>Admin Control<br><small style="font-size:0.75em; font-weight:normal; opacity:0.7;">MTsN 11 Majalengka</small></span>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item {{ $activeTab === 'overview' ? 'active' : '' }}">
                <a href="?tab=overview"><i class="fa-solid fa-chart-line"></i> <span>Ringkasan</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'students' ? 'active' : '' }}">
                <a href="?tab=students"><i class="fa-solid fa-graduation-cap"></i> <span>Data Siswa</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'testimonials' ? 'active' : '' }}">
                <a href="?tab=testimonials"><i class="fa-solid fa-comments"></i> <span>Testimoni Siswa</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'teacher_messages' ? 'active' : '' }}">
                <a href="?tab=teacher_messages"><i class="fa-solid fa-comment-medical"></i> <span>Pesan Guru</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'history' ? 'active' : '' }}">
                <a href="?tab=history"><i class="fa-solid fa-clock-rotate-left"></i> <span>Log Riwayat</span></a>
            </li>
            <li class="sidebar-menu-item {{ $activeTab === 'settings' ? 'active' : '' }}">
                <a href="?tab=settings"><i class="fa-solid fa-sliders"></i> <span>Pengaturan</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <span>Versi 3.0</span>
            <form action="{{ route('admin.logout') }}" method="POST" id="logout-form" style="margin:0;">
                @csrf
                <button type="submit" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?')"><i class="fa-solid fa-right-from-bracket"></i></button>
            </form>
        </div>
    </aside>

    <!-- PANEL UTAMA -->
    <main class="main-panel">
        <header class="header">
            <div>
                <h1>
                    @if($activeTab === 'overview') Ringkasan & Statistik
                    @elseif($activeTab === 'students') Manajemen Data Siswa Lulusan
                    @elseif($activeTab === 'testimonials') Moderasi Kesan & Pesan
                    @elseif($activeTab === 'teacher_messages') Kelola Pesan Guru
                    @elseif($activeTab === 'history') Log Riwayat Pengecekan
                    @elseif($activeTab === 'settings') Pengaturan Sistem
                    @endif
                </h1>
            </div>
            <div class="user-widget">
                <i class="fa-solid fa-user-shield"></i>
                <span>Halo, {{ Auth::user()->name }}</span>
            </div>
        </header>

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
                        <h3>Total Siswa Lulusan</h3>
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

            <!-- Card Panduan & Fitur Jangka Panjang -->
            <div class="card">
                <div class="card-header">
                    <h2>Keunggulan Sistem Kelulusan Jangka Panjang (Laravel 11)</h2>
                </div>
                <div class="card-body" style="font-size: 0.95em; line-height: 1.7; color: var(--text-dark);">
                    <p>Sistem ini dirancang ulang menggunakan Laravel 11 untuk menjamin kerapian, skalabilitas, dan efisiensi pengoperasian bertahun-tahun mendatang:</p>
                    <ul>
                        <li><strong>Pengarsipan Multi-Tahun Ajaran</strong>: Anda tidak perlu lagi menghapus data kelulusan tahun lalu untuk mengunggah kelulusan tahun ini. Cukup buat periode <em>Tahun Ajaran</em> baru di tab <strong>Pengaturan</strong>, setel target rilis, dan import data siswa baru. Sistem menyimpan arsip historis kelulusan secara utuh.</li>
                        <li><strong>Import Massal JSON Langsung</strong>: Proses unggah data dari cPanel atau spreadsheet yang diekspor ke JSON dapat diimport instan per Tahun Ajaran.</li>
                        <li><strong>Keamanan Lapis Server</strong>: Proteksi data pribadi siswa diperketat dengan menghilangkan download file JSON mentah ke browser. Kueri pencarian dikunci rapat via *Prepared Statements* Eloquent.</li>
                    </ul>
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
                                    <th>No. SKL</th>
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

                    <!-- Paginasi Siswa Lulusan -->
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
                        <p>Silakan buat Tahun Ajaran terlebih dahulu di menu <strong>Pengaturan</strong> sebelum mengelola data Siswa Lulusan.</p>
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
                                <h3>Import Siswa Lulusan Massal (CSV/JSON) - TA {{ $selectedYear->year }}</h3>
                                <button type="button" class="modal-close" onclick="closeModal('importModal')">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p style="font-size:0.9em; line-height: 1.5; color: var(--text-muted); margin-bottom: 12px;">
                                    Silakan unggah berkas Excel (.xlsx) dengan format template yang benar. Template bisa diunduh di bawah, diisi menggunakan Microsoft Excel atau Google Sheets, kemudian diupload kembali.
                                </p>
                                <p style="font-size:0.9em; margin-bottom: 18px;"><a href="{{ route('admin.student.template') }}" download>Download template impor siswa (Excel)</a></p>
                                <div class="form-group-db">
                                    <label for="json_file">Pilih File Excel Template Siswa Lulusan</label>
                                    <input type="file" id="json_file" name="json_file" class="form-control" accept=".xlsx" required>
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
                        <form action="{{ route('admin.student.add') }}" method="POST" id="studentForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <input type="hidden" name="id" id="studentId" value="">
                            
                            <div class="modal-header">
                                <h3 id="studentModalTitle">Tambah Data Siswa Lulusan</h3>
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
                                        <label for="foto">Foto Siswa (opsional)</label>
                                        <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png">
                                    </div>
                                    <div class="form-group-db">
                                        <label for="nomor_skl">Nomor SKL</label>
                                        <input type="text" id="nomor_skl" name="nomor_skl" class="form-control" placeholder="Contoh: SKL-2026-001">
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
                                <button type="submit" class="btn btn-primary" id="studentFormSubmitBtn">Simpan Siswa Lulusan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function openAddStudentModal() {
                        document.getElementById('studentForm').reset();
                        document.getElementById('studentForm').action = "{{ route('admin.student.add') }}";
                        document.getElementById('studentId').value = '';
                        document.getElementById('studentModalTitle').innerText = 'Tambah Siswa Lulusan';
                        document.getElementById('studentFormSubmitBtn').innerText = 'Simpan Siswa Lulusan';
                        openModal('studentFormModal');
                    }

                    function openEditStudentModal(student) {
                        document.getElementById('studentForm').reset();
                        // Ganti action URL form dinamis
                        document.getElementById('studentForm').action = "/admin/students/edit/" + student.id;
                        document.getElementById('studentId').value = student.id;
                        document.getElementById('studentModalTitle').innerText = 'Edit Siswa Lulusan';
                        document.getElementById('studentFormSubmitBtn').innerText = 'Perbarui Siswa Lulusan';

                        document.getElementById('nomor_peserta').value = student.nomor_peserta;
                        document.getElementById('nisn').value = student.nisn;
                        document.getElementById('nama').value = student.nama;
                        document.getElementById('jenis_kelamin').value = student.jenis_kelamin;
                        document.getElementById('kelas').value = student.kelas;
                        document.getElementById('nomor_skl').value = student.nomor_skl || '';
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
