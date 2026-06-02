<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pernyataan Kelulusan - {{ $student->nama }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Gaya Global & Reset */
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            font-size: 12pt;
        }

        /* Container untuk Tampilan Web */
        .page-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 20mm 20mm;
            margin: 30px auto;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
            border-radius: 4px;
        }

        /* Kop Surat atau Judul Dokumen */
        .letter-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .letter-title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Bagian Isi Surat */
        .letter-body {
            text-align: justify;
        }

        .section-intro {
            margin-bottom: 15px;
        }

        /* Tabel Detail Siswa */
        .student-details-table {
            width: 100%;
            margin: 15px 0 25px 30px;
            border-collapse: collapse;
        }

        .student-details-table td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .student-details-table td.label-col {
            width: 220px;
        }

        .student-details-table td.colon-col {
            width: 15px;
            text-align: center;
        }

        /* Poin Pernyataan */
        .statement-points {
            margin-bottom: 25px;
            padding-left: 20px;
        }

        .statement-points li {
            margin-bottom: 12px;
            text-align: justify;
            list-style-type: decimal;
            padding-left: 5px;
        }

        .letter-footer-text {
            margin-bottom: 35px;
        }

        /* Blok Tanda Tangan & Tanggal */
        .signature-block {
            float: right;
            width: 280px;
            text-align: center;
            margin-top: 10px;
        }

        .signature-date {
            margin-bottom: 15px;
        }

        .signature-wrapper {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
        }

        .signature-image {
            max-height: 80px;
            max-width: 220px;
            object-fit: contain;
        }

        .student-name-underline {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }

        /* Floating Navigation Bar untuk Web View */
        .no-print-bar {
            background-color: #1f2937;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .bar-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bar-actions {
            display: flex;
            gap: 12px;
        }

        .btn-action {
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-print {
            background-color: #10b981;
            color: #fff;
        }

        .btn-print:hover {
            background-color: #059669;
        }

        .btn-close {
            background-color: #4b5563;
            color: #fff;
        }

        .btn-close:hover {
            background-color: #374151;
        }

        /* CSS KHUSUS PENCETAKAN */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }

            .page-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print-bar {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Navigation Bar (Hidden during printing) -->
    <div class="no-print-bar">
        <div class="bar-title">
            <i class="fa-solid fa-file-invoice" style="color: #10b981;"></i>
            Surat Pernyataan Siswa
        </div>
        <div class="bar-actions">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Cetak Surat
            </button>
            <button onclick="window.close()" class="btn-action btn-close">
                <i class="fa-solid fa-circle-xmark"></i> Tutup Halaman
            </button>
        </div>
    </div>

    <!-- Halaman Dokumen A4 -->
    <div class="page-container">
        
        <div class="letter-header">
            <h1 class="letter-title">SURAT PERNYATAAN</h1>
        </div>

        <div class="letter-body">
            <p class="section-intro">Yang bertanda tangan di bawah ini:</p>

            <table class="student-details-table">
                <tr>
                    <td class="label-col">Nama Lengkap Siswa</td>
                    <td class="colon-col">:</td>
                    <td><strong>{{ $student->nama }}</strong></td>
                </tr>
                <tr>
                    <td class="label-col">Nomor Peserta AM</td>
                    <td class="colon-col">:</td>
                    <td>{{ $student->nomor_peserta }}</td>
                </tr>
                <tr>
                    <td class="label-col">NISN</td>
                    <td class="colon-col">:</td>
                    <td>{{ $student->nisn }}</td>
                </tr>
                <tr>
                    <td class="label-col">Tempat, Tanggal Lahir</td>
                    <td class="colon-col">:</td>
                    <td>{{ $student->tempat_lahir ?? '-' }}, {{ $student->tanggal_lahir ? $student->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Kelas</td>
                    <td class="colon-col">:</td>
                    <td>{{ $student->kelas }}</td>
                </tr>
            </table>

            <p class="section-intro">Dengan ini menyatakan dengan sebenar-benarnya dan penuh tanggung jawab bahwa sehubungan dengan Pengumuman Kelulusan, saya:</p>

            <ol class="statement-points">
                <li>
                    <strong>Siap dan Sanggup Mematuhi</strong> seluruh ketentuan, tata tertib, serta imbauan resmi yang telah dikeluarkan oleh pihak madrasah dalam menyikapi pengumuman kelulusan.
                </li>
                <li>
                    <strong>Berjanji dan Bersedia</strong> untuk tidak melakukan tindakan yang mengganggu ketertiban umum, seperti aksi corat-coret seragam, konvoi kendaraan, perusakan fasilitas, atau kegiatan eforia lainnya yang bertentangan dengan norma dan aturan madrasah.
                </li>
                <li>
                    <strong>Siap Menerima Sanksi</strong> tegas sesuai dengan Peraturan yang Berlaku di MTsN 11 Majalengka apabila di kemudian hari saya terbukti melakukan pelanggaran atau tidak mengindahkan imbauan yang telah ditetapkan tersebut.
                </li>
            </ol>

            <p class="letter-footer-text">
                Demikian surat pernyataan ini saya buat dengan sungguh-sungguh, dalam keadaan sehat, serta tanpa ada paksaan dari pihak mana pun.
            </p>

            <!-- Blok Tanda Tangan -->
            <div class="signature-block">
                <div class="signature-date">
                    Cingambul, {{ \Carbon\Carbon::parse($activeYear->target_date)->locale('id')->translatedFormat('d F Y') }}
                </div>
                <div>Yang Membuat Pernyataan,</div>
                
                <div class="signature-wrapper">
                    @if($student->signature)
                        <img src="{{ $student->signature }}" alt="Tanda Tangan Siswa" class="signature-image">
                    @else
                        <div style="font-style: italic; color: #dc3545; font-size: 10pt;">(Belum menandatangani)</div>
                    @endif
                </div>

                <div class="student-name-underline">{{ $student->nama }}</div>
                <div>NISN. {{ $student->nisn }}</div>
            </div>
            
            <div style="clear: both;"></div>

        </div>

    </div>

    <!-- Script to trigger printing automatically -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Trigger browser print dialog after loading is complete
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>
</body>
</html>
