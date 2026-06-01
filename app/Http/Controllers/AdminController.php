<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Testimonial;
use App\Models\TeacherMessage;
use App\Models\Comment;
use App\Models\CheckHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Halaman Login Admin (GET & POST)
     */
    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]);

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard');
            }

            return back()->withErrors([
                'email' => 'Email atau Password yang Anda masukkan salah.',
            ])->withInput($request->only('email'));
        }

        return view('admin.login');
    }

    /**
     * Logout Admin
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Dasbor Utama Admin (Single Page multi-tabs)
     */
    public function dashboard(Request $request)
    {
        $activeTab = $request->query('tab', 'overview');

        // Statistik Cepat (Stats Cards)
        $statTotalStudents = Student::count();
        $statTotalLulus = Student::where('status_kelulusan', 'Lulus')->count();
        $statTotalPendingTesti = Testimonial::where('status', 'pending')->count();
        $statTotalChecks = CheckHistory::count();

        // 1. Tahun Ajaran
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Filter Tahun Ajaran Terpilih
        $selectedYearId = $request->query('year_id', $activeYear ? $activeYear->id : null);
        $selectedYear = $selectedYearId ? AcademicYear::find($selectedYearId) : null;

        // 2. Data Siswa (CRUD, Search & Paginate)
        $studentsList = collect();
        $totalStudents = 0;
        $search = trim($request->query('search', ''));

        if ($selectedYearId) {
            $query = Student::where('academic_year_id', $selectedYearId);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%$search%")
                      ->orWhere('nisn', 'like', "%$search%")
                      ->orWhere('nomor_peserta', 'like', "%$search%")
                      ->orWhere('kelas', 'like', "%$search%");
                });
            }
            $totalStudents = $query->count();
            $studentsList = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();
        }

        // 3. Testimoni
        $testimonialsList = Testimonial::orderBy('id', 'desc')->get();

        // 4. Pesan Guru
        $messagesList = TeacherMessage::orderBy('id', 'desc')->get();

        // 5. Log Riwayat (Paginasi & Pencarian)
        $historySearch = trim($request->query('history_search', ''));
        $historyQuery = CheckHistory::with('academicYear');
        if (!empty($historySearch)) {
            $historyQuery->where('student_name', 'like', "%$historySearch%")
                         ->orWhere('nomor_peserta', 'like', "%$historySearch%");
        }
        $totalHistories = $historyQuery->count();
        $historyList = $historyQuery->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // 6. Log Riwayat Terkini untuk Overview
        $recentChecks = CheckHistory::orderBy('id', 'desc')->limit(5)->get();

        // 7. Data Pengaturan (Settings)
        $settings = [
            'kepala_nama'    => Setting::get('kepala_nama', ''),
            'kepala_jabatan' => Setting::get('kepala_jabatan', ''),
            'kepala_pesan'   => Setting::get('kepala_pesan', ''),
        ];

        return view('admin.dashboard', compact(
            'activeTab',
            'statTotalStudents',
            'statTotalLulus',
            'statTotalPendingTesti',
            'statTotalChecks',
            'academicYears',
            'activeYear',
            'selectedYearId',
            'selectedYear',
            'studentsList',
            'totalStudents',
            'search',
            'testimonialsList',
            'messagesList',
            'historyList',
            'totalHistories',
            'historySearch',
            'recentChecks',
            'settings'
        ));
    }

    // ========================================================
    // A. TINDAKAN MANAJEMEN SISWA
    // ========================================================

    public function addStudent(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'nomor_peserta' => 'required|string|max:50',
            'nisn' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'required|date',
            'kelas' => 'required|string|max:20',
            'status_kelulusan' => 'required|in:Lulus,Tidak Lulus,Ditangguhkan',
            'release_timestamp' => 'nullable|date',
        ]);

        // Cek Keunikan
        $exists = Student::where('academic_year_id', $data['academic_year_id'])
            ->where(function($q) use ($data) {
                $q->where('nomor_peserta', $data['nomor_peserta'])
                  ->orWhere('nisn', $data['nisn']);
            })->exists();

        if ($exists) {
            return back()->with('error', 'Gagal! Nomor Peserta atau NISN sudah terdaftar pada tahun ajaran ini.')->withInput();
        }

        Student::create($data);
        return back()->with('success', 'Siswa baru berhasil ditambahkan!');
    }

    public function editStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'nomor_peserta' => 'required|string|max:50',
            'nisn' => 'required|string|max:20',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'required|date',
            'kelas' => 'required|string|max:20',
            'status_kelulusan' => 'required|in:Lulus,Tidak Lulus,Ditangguhkan',
            'release_timestamp' => 'nullable|date',
        ]);

        // Cek Keunikan
        $exists = Student::where('academic_year_id', $student->academic_year_id)
            ->where(function($q) use ($data) {
                $q->where('nomor_peserta', $data['nomor_peserta'])
                  ->orWhere('nisn', $data['nisn']);
            })
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Gagal! Nomor Peserta atau NISN sudah terdaftar pada siswa lain di tahun ajaran ini.');
        }

        $student->update($data);
        return back()->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function deleteStudent($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return back()->with('success', 'Siswa berhasil dihapus dari database.');
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file',
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        $file = $request->file('json_file');
        $studentsData = json_decode(file_get_contents($file->getRealPath()), true);

        if (!is_array($studentsData)) {
            return back()->with('error', 'Format file JSON tidak valid.');
        }

        $inserted = 0;
        $skipped = 0;
        $yearId = $request->academic_year_id;

        try {
            foreach ($studentsData as $student) {
                $nopes = trim($student['nomor_peserta'] ?? '');
                $nisn = trim($student['nisn'] ?? '');

                if (empty($nopes) || empty($nisn)) {
                    $skipped++;
                    continue;
                }

                $exists = Student::where('academic_year_id', $yearId)
                    ->where(function($q) use ($nopes, $nisn) {
                        $q->where('nomor_peserta', $nopes)
                          ->orWhere('nisn', $nisn);
                    })->exists();

                if (!$exists) {
                    Student::create([
                        'academic_year_id' => $yearId,
                        'nomor_peserta' => $nopes,
                        'nisn' => $nisn,
                        'nama' => trim($student['nama'] ?? 'Tanpa Nama'),
                        'jenis_kelamin' => $student['jenis_kelamin'] ?? 'Laki-laki',
                        'tempat_lahir' => trim($student['tempat_lahir'] ?? 'Majalengka'),
                        'tanggal_lahir' => $student['tanggal_lahir'] ?? date('Y-m-d'),
                        'kelas' => trim($student['kelas'] ?? 'IX'),
                        'status_kelulusan' => $student['status_kelulusan'] ?? 'Lulus',
                        'release_timestamp' => $student['release_timestamp'] ?? null
                    ]);
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
            return back()->with('success', "Proses import massal selesai. Berhasil memasukkan: {$inserted} siswa. Dilewati (sudah terdaftar/tidak lengkap): {$skipped} siswa.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses import data: ' . $e->getMessage());
        }
    }

    // ========================================================
    // B. MANAJEMEN TAHUN AJARAN (ACADEMIC YEAR)
    // ========================================================

    public function addAcademicYear(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|string|max:9|unique:academic_years,year',
            'target_date' => 'required|date',
        ], [
            'year.unique' => 'Tahun ajaran ini sudah terdaftar.',
        ]);

        AcademicYear::create([
            'year' => $data['year'],
            'is_active' => false, // Default tidak aktif, harus diaktifkan manual
            'target_date' => $data['target_date'],
            'maintenance_mode' => false
        ]);

        return back()->with('success', 'Tahun Ajaran baru berhasil ditambahkan! Silakan aktifkan jika ingin menggunakannya.');
    }

    public function activateAcademicYear($id)
    {
        $year = AcademicYear::findOrFail($id);

        // Deaktifkan seluruh tahun ajaran lain
        AcademicYear::where('id', '!=', $id)->update(['is_active' => false]);
        
        // Aktifkan tahun ajaran terpilih
        $year->update(['is_active' => true]);

        return back()->with('success', "Tahun ajaran {$year->year} kini aktif sebagai periode pengecekan utama!");
    }

    public function deleteAcademicYear($id)
    {
        $year = AcademicYear::findOrFail($id);
        
        if ($year->is_active) {
            return back()->with('error', 'Gagal! Tidak dapat menghapus Tahun Ajaran yang sedang berstatus Aktif.');
        }

        $year->delete();
        return back()->with('success', 'Tahun Ajaran beserta seluruh data siswa terikat berhasil dihapus.');
    }

    // ========================================================
    // C. MODERASI TESTIMONI
    // ========================================================

    public function approveTestimonial($id)
    {
        $t = Testimonial::findOrFail($id);
        $t->update(['status' => 'approved']);

        return back()->with('success', 'Testimoni disetujui dan kini tampil di halaman depan!');
    }

    public function rejectTestimonial($id)
    {
        $t = Testimonial::findOrFail($id);
        $t->update(['status' => 'rejected']);

        return back()->with('success', 'Testimoni ditolak.');
    }

    public function deleteTestimonial($id)
    {
        $t = Testimonial::findOrFail($id);
        
        // Hapus komentar terkait
        Comment::where('item_uid', $t->uid)->where('item_type', 'testimonial')->delete();
        $t->delete();

        return back()->with('success', 'Testimoni dan komentar terkait berhasil dihapus.');
    }

    // ========================================================
    // D. KELOLA PESAN GURU
    // ========================================================

    public function addTeacherMessage(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'message' => 'required|string',
        ]);

        TeacherMessage::create([
            'uid' => 'tm-' . uniqid(),
            'name' => $data['name'],
            'message' => $data['message'],
            'likes' => 0,
            'date' => now()
        ]);

        return back()->with('success', 'Pesan guru berhasil diterbitkan!');
    }

    public function deleteTeacherMessage($id)
    {
        $msg = TeacherMessage::findOrFail($id);
        
        // Hapus komentar terkait
        Comment::where('item_uid', $msg->uid)->where('item_type', 'teacher_message')->delete();
        $msg->delete();

        return back()->with('success', 'Pesan guru berhasil dihapus.');
    }

    // ========================================================
    // E. SETTINGS & LOG
    // ========================================================

    public function clearCheckHistory()
    {
        CheckHistory::truncate();
        return back()->with('success', 'Seluruh log riwayat pengecekan dibersihkan.');
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'target_date'      => 'required|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'kepala_nama'      => 'required|string|max:150',
            'kepala_jabatan'   => 'required|string|max:150',
            'kepala_pesan'     => 'required|string',
        ]);

        // Simpan pengaturan waktu & mode pemeliharaan ke tabel academic_years
        $year = AcademicYear::findOrFail($data['academic_year_id']);
        $year->update([
            'target_date'      => $data['target_date'],
            'maintenance_mode' => $request->has('maintenance_mode') ? true : false
        ]);

        // Simpan data Kepala Madrasah ke tabel settings
        Setting::set('kepala_nama', $data['kepala_nama']);
        Setting::set('kepala_jabatan', $data['kepala_jabatan']);
        Setting::set('kepala_pesan', $data['kepala_pesan']);

        return back()->with('success', 'Pengaturan sistem dan data Kepala Madrasah berhasil diperbarui!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->update(['password' => Hash::make($request->new_password)]);
            return back()->with('success', 'Kata sandi administrator berhasil diubah!');
        }

        return back()->with('error', 'Kata sandi saat ini yang Anda masukkan salah.')->withInput();
    }
}
?>
