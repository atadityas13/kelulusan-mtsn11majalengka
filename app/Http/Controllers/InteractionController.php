<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CheckHistory;
use App\Models\Testimonial;
use App\Models\TeacherMessage;
use App\Models\Comment;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    /**
     * Menyimpan riwayat pengecekan siswa via AJAX
     */
    public function saveCheckHistory(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return response()->json(['success' => false, 'message' => 'Tahun ajaran aktif tidak ditemukan.']);
        }

        $studentName = trim($request->input('studentName', ''));
        $checkResult = trim($request->input('checkResult', ''));
        $nomorPeserta = trim($request->input('nomorPeserta', ''));

        if (empty($studentName) || empty($checkResult) || empty($nomorPeserta)) {
            return response()->json(['success' => false, 'message' => 'Data riwayat tidak lengkap.']);
        }

        try {
            CheckHistory::create([
                'academic_year_id' => $activeYear->id,
                'nomor_peserta' => $nomorPeserta,
                'student_name' => $studentName,
                'result' => $checkResult,
                'timestamp' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Riwayat berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mencatat riwayat.']);
        }
    }

    /**
     * Menyimpan testimoni baru (kesan & pesan) via AJAX
     */
    public function saveTestimonial(Request $request)
    {
        $studentName = trim($request->input('studentName', ''));
        $testimonialMessage = trim($request->input('testimonialMessage', ''));

        if (empty($studentName) || empty($testimonialMessage)) {
            return response()->json(['success' => false, 'message' => 'Nama dan Kesan/Pesan tidak boleh kosong.']);
        }

        try {
            Testimonial::create([
                'uid' => 'ts-' . uniqid(),
                'name' => $studentName,
                'message' => $testimonialMessage,
                'likes' => 0,
                'status' => 'pending', // Menunggu persetujuan (moderasi) admin
                'date' => now()
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Terima kasih! Kesan & Pesan Anda berhasil dikirim dan akan tampil setelah disetujui administrator.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim kesan & pesan.']);
        }
    }

    /**
     * Menambahkan Suka (Likes) pada testimoni atau pesan guru
     */
    public function likeItem(Request $request)
    {
        $itemId = trim($request->input('itemId', ''));
        $itemType = trim($request->input('itemType', ''));

        if (empty($itemId) || empty($itemType)) {
            return response()->json(['success' => false, 'message' => 'ID dan Tipe item wajib diisi.']);
        }

        try {
            if ($itemType === 'teacher_message') {
                $item = TeacherMessage::where('uid', $itemId)->first();
            } elseif ($itemType === 'testimonial') {
                $item = Testimonial::where('uid', $itemId)->first();
            } else {
                return response()->json(['success' => false, 'message' => 'Tipe item tidak dikenal.']);
            }

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.']);
            }

            $item->increment('likes');

            return response()->json([
                'success' => true,
                'newLikesCount' => $item->likes
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memproses likes.']);
        }
    }

    /**
     * Menambahkan Komentar via AJAX
     */
    public function addComment(Request $request)
    {
        $itemId = trim($request->input('itemId', ''));
        $itemType = trim($request->input('itemType', ''));
        $author = trim($request->input('commentAuthor', ''));
        $commentText = trim($request->input('commentMessage', ''));

        if (empty($itemId) || empty($itemType) || empty($commentText)) {
            return response()->json(['success' => false, 'message' => 'Data komentar tidak lengkap.']);
        }

        if (empty($author)) {
            $author = 'Anonim';
        }

        try {
            // Validasi keberadaan item asal
            if ($itemType === 'teacher_message') {
                $exists = TeacherMessage::where('uid', $itemId)->exists();
            } elseif ($itemType === 'testimonial') {
                $exists = Testimonial::where('uid', $itemId)->exists();
            } else {
                return response()->json(['success' => false, 'message' => 'Tipe item tidak dikenal.']);
            }

            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Item asal tidak ditemukan di database.']);
            }

            $comment = Comment::create([
                'item_uid' => $itemId,
                'item_type' => $itemType,
                'author' => $author,
                'comment' => $commentText,
                'date' => now(),
                'status' => 'approved' // Otomatis disetujui (moderasi pasca-terbit di admin)
            ]);

            return response()->json([
                'success' => true,
                'newComment' => [
                    'author' => $comment->author,
                    'comment' => $comment->comment,
                    'date' => $comment->date->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim komentar: ' . $e->getMessage()]);
        }
    }

    /**
     * Menyimpan tanda tangan digital siswa via AJAX
     */
    public function saveSignature(Request $request)
    {
        $nomorPeserta = trim($request->input('nomorPeserta', ''));
        $signature = trim($request->input('signature', ''));

        if (empty($nomorPeserta) || empty($signature)) {
            return response()->json(['success' => false, 'message' => 'Nomor Peserta dan Tanda Tangan wajib diisi.']);
        }

        try {
            $student = \App\Models\Student::where('nomor_peserta', $nomorPeserta)->first();
            if ($student) {
                $student->signature = $signature;
                $student->save();
                return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
            }
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan tanda tangan: ' . $e->getMessage()]);
        }
    }
}
?>
