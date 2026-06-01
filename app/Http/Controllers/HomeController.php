<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\TeacherMessage;
use App\Models\Testimonial;
use App\Models\CheckHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Halaman Utama Publik
     */
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return response()->view('errors.no_active_year', [], 503);
        }

        $targetDate = $activeYear->target_date;
        $maintenanceMode = $activeYear->maintenance_mode;
        $currentTime = now();

        // Tentukan apakah waktu pengumuman sudah dibuka dan tidak dalam pemeliharaan
        $showResult = ($currentTime >= Carbon::parse($targetDate)) && !$maintenanceMode;

        // Inisialisasi variabel formulir
        $foundStudent = null;
        $isGraduated = false;
        $errorMessage = '';
        $batchReleaseNotYet = false;
        $batchReleaseTime = null;

        // Penanganan cek kelulusan jika form disubmit
        if ($request->isMethod('post')) {
            if ($showResult) {
                $nomorPeserta = trim($request->input('nomorPeserta', ''));
                $nisn = trim($request->input('nisn', ''));
                $tanggalLahir = trim($request->input('tanggalLahir', ''));

                if (empty($nomorPeserta) || empty($nisn) || empty($tanggalLahir)) {
                    $errorMessage = 'Seluruh data pencarian wajib diisi.';
                } else {
                    $foundStudent = Student::where('academic_year_id', $activeYear->id)
                        ->where('nomor_peserta', $nomorPeserta)
                        ->where('nisn', $nisn)
                        ->where('tanggal_lahir', $tanggalLahir)
                        ->first();

                    if ($foundStudent) {
                        $isGraduated = ($foundStudent->status_kelulusan === 'Lulus');
                        $batchReleaseTime = $foundStudent->release_timestamp;

                        if ($batchReleaseTime && $currentTime < Carbon::parse($batchReleaseTime)) {
                            $batchReleaseNotYet = true;
                        }
                    } else {
                        $errorMessage = "Maaf, kombinasi Nomor Peserta, NISN, dan Tanggal Lahir yang Anda masukkan tidak ditemukan dalam data kami.";
                    }
                }
            } else {
                if ($maintenanceMode) {
                    $errorMessage = "Sistem pengecekan kelulusan sedang dalam pemeliharaan. Mohon ditunggu.";
                } else {
                    $errorMessage = "Pengumuman kelulusan baru akan dibuka pada " . Carbon::parse($targetDate)->translatedFormat('d F Y') . " pukul " . Carbon::parse($targetDate)->format('H:i') . " WIB. Mohon ditunggu.";
                }
            }
        }

        // Ambil Pesan Guru
        $teacherMessages = TeacherMessage::with(['comments' => function($q) {
            $q->where('status', 'approved')->orderBy('date', 'asc');
        }])->orderBy('date', 'desc')->get();

        // Ambil Testimoni yang disetujui (Approved)
        $testimonials = Testimonial::where('status', 'approved')->with(['comments' => function($q) {
            $q->where('status', 'approved')->orderBy('date', 'asc');
        }])->orderBy('date', 'desc')->get();

        // Ambil 5 Riwayat Pengecekan Terbaru
        $checkHistory = CheckHistory::where('academic_year_id', $activeYear->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Data Kepala Madrasah dari database settings
        $kepalaMadrasahMessage = [
            'name'     => Setting::get('kepala_nama', 'Kepala MTsN 11 Majalengka'),
            'position' => Setting::get('kepala_jabatan', 'Kepala Madrasah'),
            'message'  => Setting::get('kepala_pesan', 'Selamat kepada seluruh siswa yang telah dinyatakan lulus.'),
        ];

        return view('index', compact(
            'activeYear',
            'targetDate',
            'showResult',
            'foundStudent',
            'isGraduated',
            'errorMessage',
            'batchReleaseNotYet',
            'batchReleaseTime',
            'teacherMessages',
            'testimonials',
            'checkHistory',
            'kepalaMadrasahMessage'
        ));
    }

    /**
     * API Secure Lookup Nomor Peserta (AJAX)
     */
    public function lookupNopes(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return response()->json(['success' => false, 'message' => 'Sistem tidak aktif.']);
        }

        $nisn = trim($request->query('nisn', ''));
        $tgl = trim($request->query('tanggal_lahir', ''));

        if (empty($nisn) || empty($tgl)) {
            return response()->json(['success' => false, 'message' => 'NISN dan Tanggal Lahir wajib diisi.']);
        }

        $nopes = Student::where('academic_year_id', $activeYear->id)
            ->where('nisn', $nisn)
            ->where('tanggal_lahir', $tgl)
            ->value('nomor_peserta');

        if ($nopes) {
            return response()->json(['success' => true, 'nomor_peserta' => $nopes]);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan. Pastikan NISN dan tanggal lahir benar.']);
    }

    /**
     * API Secure Lookup NISN (AJAX)
     */
    public function lookupNisn(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return response()->json(['success' => false, 'message' => 'Sistem tidak aktif.']);
        }

        $nama = trim($request->query('nama', ''));
        $tgl = trim($request->query('tanggal_lahir', ''));

        if (empty($nama) || empty($tgl)) {
            return response()->json(['success' => false, 'message' => 'Nama Lengkap dan Tanggal Lahir wajib diisi.']);
        }

        $nisn = Student::where('academic_year_id', $activeYear->id)
            ->whereRaw('UPPER(nama) = ?', [strtoupper($nama)])
            ->where('tanggal_lahir', $tgl)
            ->value('nisn');

        if ($nisn) {
            return response()->json(['success' => true, 'nisn' => $nisn]);
        }

        return response()->json(['success' => false, 'message' => 'Data tidak ditemukan. Pastikan nama lengkap dan tanggal lahir benar.']);
    }
}
?>
