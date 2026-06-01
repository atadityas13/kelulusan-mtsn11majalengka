<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Testimonial;
use App\Models\TeacherMessage;
use App\Models\Comment;
use App\Models\CheckHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Response;

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
                      ->orWhere('nomor_skl', 'like', "%$search%")
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
    // TEMPLATE DOWNLOAD
    // ========================================================

    public function downloadStudentTemplate()
    {
        $excel = $this->createExcelTemplate();
        $filename = 'template_import_siswa_' . date('Y-m-d') . '.xlsx';

        return Response::make($excel, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function createExcelTemplate()
    {
        $columns = [
            'nomor_peserta',
            'nisn',
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'foto',
            'status_kelulusan',
            'nomor_skl',
            'Pendidikan Agama Islam - Al Qur\'an Hadis',
            'Pendidikan Agama Islam - Akidah Akhlak',
            'Pendidikan Agama Islam - Fikih',
            'Pendidikan Agama Islam - Sejarah Kebudayaan Islam',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Bahasa Arab',
            'Matematika',
            'Ilmu Pengetahuan Alam',
            'Ilmu Pengetahuan Sosial',
            'Bahasa Inggris',
            'Seni dan Prakarya',
            'Pendidikan Jasmani Olahraga dan Kesehatan',
            'Informatika',
            'Muatan Lokal - Bahasa Sunda',
        ];

        $sampleData = [
            [
                '25-10-10-2-0089-0001',
                '0098765432',
                'Ahmad Fajar',
                'Majalengka',
                '2008-03-15',
                'Laki-laki',
                '',
                'Lulus',
                'SKL-2026-001',
                88, 90, 85, 87, 92, 89, 86, 93, 91, 90, 88, 89, 87, 90,
            ]
        ];

        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        
        if ($zip->open($tempFile, ZipArchive::CREATE) === true) {
            $zip->addFromString('_rels/.rels', $this->getRelationshipsXml());
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelationshipsXml());
            $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->getWorksheetXml($columns, $sampleData));
            $zip->addFromString('xl/styles.xml', $this->getStylesXml());
            $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());
            $zip->close();
        }

        $excel = file_get_contents($tempFile);
        unlink($tempFile);

        return $excel;
    }

    private function getRelationshipsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }

    private function getWorkbookRelationshipsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }

    private function getWorkbookXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<fileVersion appName="xl" lastEdited="4" lowestEdited="4" rupBuild="4505"/>
<workbookPr defaultTheme="1"/>
<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="19020" windowHeight="11010" tabRatio="500" activeTab="0"/></bookViews>
<sheets><sheet name="Data Siswa" sheetId="1" r:id="rId1"/></sheets>
</workbook>';
    }

    private function getWorksheetXml($columns, $data)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheetData>';

        $xml .= '<row r="1">';
        foreach ($columns as $col => $header) {
            $xml .= '<c r="' . $this->getExcelColumn($col) . '1" t="str" s="1"><v>' . htmlspecialchars($header, ENT_XML1) . '</v></c>';
        }
        $xml .= '</row>';

        $rowNum = 2;
        foreach ($data as $row) {
            $xml .= '<row r="' . $rowNum . '">';
            foreach ($row as $col => $value) {
                $colLetter = $this->getExcelColumn($col);
                if (is_numeric($value)) {
                    $xml .= '<c r="' . $colLetter . $rowNum . '"><v>' . $value . '</v></c>';
                } else {
                    $xml .= '<c r="' . $colLetter . $rowNum . '" t="str"><v>' . htmlspecialchars($value, ENT_XML1) . '</v></c>';
                }
            }
            $xml .= '</row>';
            $rowNum++;
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    private function getStylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font></fonts>
<fills><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD3D3D3"/></patternFill></fill></fills>
<borders><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellXfs><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/><xf numFmtId="0" fontId="0" fillId="2" borderId="0" applyFill="1" applyFont="1"><alignment horizontal="center" vertical="center"/></xf></cellXfs>
</styleSheet>';
    }

    private function getContentTypesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }

    private function getExcelColumn($index)
    {
        $col = '';
        while ($index >= 0) {
            $col = chr(65 + ($index % 26)) . $col;
            $index = intval($index / 26) - 1;
        }
        return $col;
    }

    // ========================================================
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status_kelulusan' => 'required|in:Lulus,Tidak Lulus,Ditangguhkan',
            'nomor_skl' => 'nullable|string|max:100',
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

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/foto_siswa', 'public');
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status_kelulusan' => 'required|in:Lulus,Tidak Lulus,Ditangguhkan',
            'nomor_skl' => 'nullable|string|max:100',
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

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/foto_siswa', 'public');
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
            'json_file' => 'required|file|mimes:xlsx,xls,csv,json',
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        $file = $request->file('json_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx' || $extension === 'xls') {
            $studentsData = $this->parseExcelFile($file->getRealPath());
        } elseif ($extension === 'csv') {
            $studentsData = $this->parseCsvFile($file->getRealPath());
        } else {
            $studentsData = json_decode(file_get_contents($file->getRealPath()), true);
        }

        if (!is_array($studentsData)) {
            return back()->with('error', 'Format file tidak valid. Gunakan Excel, CSV, atau JSON dengan template yang benar.');
        }

        $studentsData = array_map(function ($row) {
            return $this->normalizeImportRow((array) $row);
        }, $studentsData);

        $inserted = 0;
        $skipped = 0;
        $yearId = $request->academic_year_id;

        try {
            foreach ($studentsData as $student) {
                $nopes = trim($this->getImportValue($student, ['nomor_peserta', 'nopes']));
                $nisn = trim($this->getImportValue($student, ['nisn']));

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
                    $studentRecord = Student::create([
                        'academic_year_id' => $yearId,
                        'nomor_peserta' => $nopes,
                        'nisn' => $nisn,
                        'nama' => trim($this->getImportValue($student, ['nama', 'nama_lengkap'], 'Tanpa Nama')),
                        'jenis_kelamin' => $this->getImportValue($student, ['jenis_kelamin'], 'Laki-laki'),
                        'tempat_lahir' => trim($this->getImportValue($student, ['tempat_lahir'], 'Majalengka')),
                        'tanggal_lahir' => $this->getImportValue($student, ['tanggal_lahir'], date('Y-m-d')),
                        'kelas' => trim($this->getImportValue($student, ['kelas'], 'IX')),
                        'foto' => trim($this->getImportValue($student, ['foto'], '')),
                        'status_kelulusan' => $this->getImportValue($student, ['status_kelulusan'], 'Lulus'),
                        'nomor_skl' => trim($this->getImportValue($student, ['nomor_skl', 'no_skl'], '')),
                        'release_timestamp' => $this->getImportValue($student, ['release_timestamp'], null),
                    ]);

                    $gradeData = [
                        'student_id' => $studentRecord->id,
                        'academic_year_id' => $yearId,
                        'agama_al_quran_hadis' => $this->getImportValue($student, ['agama_al_quran_hadis', 'pendidikan_agama_islam_al_qur_an_hadis', 'pendidikan_agama_islam_al_quran_hadis']),
                        'agama_akidah_akhlak' => $this->getImportValue($student, ['agama_akidah_akhlak', 'pendidikan_agama_islam_akidah_akhlak']),
                        'agama_fikih' => $this->getImportValue($student, ['agama_fikih', 'pendidikan_agama_islam_fikih']),
                        'agama_sejarah_kebudayaan_islam' => $this->getImportValue($student, ['agama_sejarah_kebudayaan_islam', 'pendidikan_agama_islam_sejarah_kebudayaan_islam']),
                        'pendidikan_pancasila' => $this->getImportValue($student, ['pendidikan_pancasila']),
                        'bahasa_indonesia' => $this->getImportValue($student, ['bahasa_indonesia']),
                        'bahasa_arab' => $this->getImportValue($student, ['bahasa_arab']),
                        'matematika' => $this->getImportValue($student, ['matematika']),
                        'ilmu_pengetahuan_alam' => $this->getImportValue($student, ['ilmu_pengetahuan_alam']),
                        'ilmu_pengetahuan_sosial' => $this->getImportValue($student, ['ilmu_pengetahuan_sosial']),
                        'bahasa_inggris' => $this->getImportValue($student, ['bahasa_inggris']),
                        'seni_dan_prakarya' => $this->getImportValue($student, ['seni_dan_prakarya']),
                        'pendidikan_jasmani_olahraga_dan_kesehatan' => $this->getImportValue($student, ['pendidikan_jasmani_olahraga_dan_kesehatan']),
                        'informatika' => $this->getImportValue($student, ['informatika']),
                        'muatan_lokal_bahasa_sunda' => $this->getImportValue($student, ['muatan_lokal_bahasa_sunda', 'muatan_lokal_bahasa_sunda']),
                    ];

                    $gradeValues = array_filter(array_slice($gradeData, 2), fn($value) => $value !== null && $value !== '');
                    if (!empty($gradeValues)) {
                        StudentGrade::create($gradeData);
                    }

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

    private function parseCsvFile(string $path): array
    {
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $header = null;

            while (($line = fgetcsv($handle, 0, ',')) !== false) {
                if (!$header) {
                    $header = array_map([$this, 'normalizeImportHeader'], $line);
                    continue;
                }

                $line = array_pad($line, count($header), '');
                $rows[] = array_combine($header, $line);
            }

            fclose($handle);
        }

        return $rows;
    }

    private function parseExcelFile(string $path): array
    {
        $rows = [];
        $zip = new ZipArchive();

        if ($zip->open($path) === true) {
            if (($sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml')) === false) {
                $zip->close();
                return [];
            }

            $xml = simplexml_load_string($sheetXml);
            $header = null;
            $rowNum = 0;

            foreach ($xml->sheetData->row as $row) {
                $rowNum++;
                $values = [];

                foreach ($row->c as $cell) {
                    $cellValue = (string) $cell->v;
                    $values[] = $cellValue;
                }

                if ($rowNum === 1) {
                    $header = array_map([$this, 'normalizeImportHeader'], $values);
                    continue;
                }

                if ($header) {
                    $values = array_pad($values, count($header), '');
                    $rows[] = array_combine($header, $values);
                }
            }

            $zip->close();
        }

        return $rows;
    }

    private function normalizeImportHeader(string $header): string
    {
        $header = trim($header);
        $header = strtolower($header);
        $header = preg_replace('/[\s\/\\\-&\.\'"\(\):]+/', '_', $header);
        $header = preg_replace('/[^a-z0-9_]+/', '', $header);
        $header = preg_replace('/_+/', '_', $header);
        return trim($header, '_');
    }

    private function normalizeImportRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeImportHeader($key)] = trim((string) $value);
        }

        return $normalized;
    }

    private function getImportValue(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
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
