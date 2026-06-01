@extends('layouts.app')

@section('title', 'Pengumuman Kelulusan MTsN 11 Majalengka')

@section('styles')
<style>
    /* Tambahan kustom gaya kepala madrasah */
    .head-teacher-photo { text-align: center; margin-bottom: 15px; }
    .head-teacher-photo .photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #4f46e5; }
    
    .download-notice { margin-top: 15px; padding: 10px; border-radius: 8px; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; text-align: center; font-size: 0.9em; }
    .info-download-skL { margin-top: 15px; font-size: 0.88em; color: #64748b; text-align: center; }
    .btn-download.disabled { background-color: #cbd5e1; cursor: not-allowed; opacity: 0.7; }

    /* Gaya Countdown Premium */
    .countdown-section { background-color: #eff6ff; border: 1px solid #dbeafe; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .countdown-section h2 { color: #1e40af; margin-bottom: 15px; font-size: 1.6em; }
    .countdown-timer { display: flex; justify-content: center; gap: 15px; margin-top: 20px; flex-wrap: wrap; }
    .countdown-item { background-color: #2563eb; color: white; padding: 15px; border-radius: 10px; min-width: 80px; font-size: 1.8em; font-weight: 700; box-shadow: 0 4px 6px rgba(37,99,235,0.15); flex-grow: 1; max-width: 100px; }
    .countdown-item span { display: block; font-size: 0.5em; font-weight: 400; margin-top: 5px; opacity: 0.85; text-transform: uppercase; }
    .countdown-message { margin-top: 15px; font-size: 1.05em; color: #1e293b; line-height: 1.6; }
    
    /* Tombol Likes */
    .like-button { background: #fff; border: 1px solid #f1f5f9; color: #ef4444; border-radius: 8px; padding: 8px 14px; font-size: 0.9em; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer; font-family: inherit; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .like-button .fa-heart { color: #ef4444; transition: transform 0.2s; }
    .like-button.liked .fa-heart { color: #b91c1c; animation: liked-pop 0.3s; }
    @keyframes liked-pop { 0% { transform: scale(1); } 60% { transform: scale(1.4); } 100% { transform: scale(1); } }
    .like-button:hover, .like-button.liked { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

    /* Confetti */
    #confetti-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; display: none; }
</style>
@endsection

@section('content')
    @if($showResult)
    <!-- Modal Himbauan Karakter -->
    <div class="modal-warning-overlay" id="modal-warning-overlay" style="display:none;">
        <div class="modal-warning">
            <h2>HIMBAUAN DALAM MENYIKAPI PENGUMUMAN KELULUSAN</h2>
            <ol>
                <li>Mensyukuri karunia Alloh SWT atas kelulusan yang diperoleh;</li>
                <li>Tidak melakukan konvoi, hura-hura, kumpul bareng merayakan kelulusan;</li>
                <li>Dilarang melakukan coret-coret pakaian seragam atau apapun;</li>
                <li>Dilarang melakukan vandalisme (mencoret-coret fasilitas umum).</li>
            </ol>
            <div class="modal-warning-note">
                Jika melanggar himbauan tersebut maka:<br>
                a. Kelulusan akan dibatalkan karena melanggar nilai akhlak (minimal B);<br>
                b. Proses pendaftaran ke sekolah menengah atas tidak akan diproses akun PPDB-nya tidak akan diaktifkan;<br>
                c. Ijazah tidak akan diberikan.
            </div>
            <div class="modal-warning-checkbox-group">
                <input type="checkbox" id="modal-warning-checkbox">
                <label for="modal-warning-checkbox" style="user-select: none;">
                    Saya menyatakan dengan sungguh-sungguh bahwa saya akan menaati semua himbauan, dan apabila melanggar saya siap menanggung segala konsekuensinya.
                </label>
            </div>
            <button class="btn-modal-confirm" id="btn-modal-confirm" disabled>Mengerti</button>
        </div>
    </div>
    @endif

    <!-- Modal Cek Nomor Peserta -->
    <div class="modal-nopes-overlay" id="modal-nopes-overlay" style="display:none;">
        <div class="modal-nopes">
            <button class="close-modal-nopes" id="close-modal-nopes" title="Tutup">&times;</button>
            <h2>Cek Nomor Peserta Ujian</h2>
            <form id="form-cek-nopes" autocomplete="off">
                <label for="nopes-nisn">NISN:</label>
                <input type="text" id="nopes-nisn" name="nisn" required style="width:98%; margin-bottom:8px;">
                <label for="nopes-tgl">Tanggal Lahir:</label>
                <input type="date" id="nopes-tgl" name="tanggal_lahir" required style="width:98%; margin-bottom:8px;">
                <button type="submit" class="btn-cek-nopes">Cek Nomor Peserta</button>
            </form>
            <div class="modal-nopes-result" id="modal-nopes-result"></div>
            <div class="modal-nopes-error" id="modal-nopes-error"></div>
            <div style="margin-top:12px; font-size:0.92em; color:#b91c1c; font-weight:500;">
                Jika masih kesulitan, silakan hubungi panitia kelulusan di madrasah.
            </div>
        </div>
    </div>

    <!-- Modal Cek NISN -->
    <div class="modal-nisn-overlay" id="modal-nisn-overlay" style="display:none;">
        <div class="modal-nisn">
            <button class="close-modal-nisn" id="close-modal-nisn" title="Tutup">&times;</button>
            <h2>Cek NISN</h2>
            <form id="form-cek-nisn" autocomplete="off">
                <label for="nisn-nama">Nama Lengkap:</label>
                <input type="text" id="nisn-nama" name="nama" required style="width:98%; margin-bottom:8px;">
                <label for="nisn-tgl">Tanggal Lahir:</label>
                <input type="date" id="nisn-tgl" name="tanggal_lahir" required style="width:98%; margin-bottom:8px;">
                <button type="submit" class="btn-cek-nisn">Cek NISN</button>
            </form>
            <div class="modal-nisn-result" id="modal-nisn-result"></div>
            <div class="modal-nisn-error" id="modal-nisn-error"></div>
            <div style="margin-top:12px; font-size:0.92em; color:#b91c1c; font-weight:500;">
                Jika masih kesulitan, silakan hubungi panitia kelulusan di madrasah.
            </div>
        </div>
    </div>

    <canvas id="confetti-canvas"></canvas>

    <div id="app">
        <header class="header">
            <img src="{{ asset('assets/mtsn11majalengka-logo.png') }}" alt="Logo MTsN 11 Majalengka" class="logo">
            <h1>Pengumuman Kelulusan <br> MTsN 11 Majalengka</h1>
            <p class="tagline">Lulusan Tahun Pelajaran {{ $activeYear->year }}</p>
        </header>

        <main class="main-content">
            <div class="content-wrapper">
                <div class="main-column">
                    
                    <!-- FASE 1: BELUM WAKTUNYA RILIS -->
                    @if(!$showResult)
                        <section id="countdown-section" class="card fade-in scroll-reveal">
                            <h2>Pengumuman Kelulusan Belum Dibuka!</h2>
                            <p class="countdown-message">
                                Mohon bersabar, hasil rapat dewan guru sedang dipersiapkan.<br>Hasil kelulusan akan tersedia secara resmi pada:<br>
                                <strong>{{ \Carbon\Carbon::parse($targetDate)->translatedFormat('d F Y') }}</strong> pukul <strong>{{ \Carbon\Carbon::parse($targetDate)->format('H:i') }} WIB</strong>.
                            </p>
                            <h2>Sisa Waktu :</h2>
                            <div class="countdown-timer" id="countdown-timer">
                                <div class="countdown-item"><span id="days">00</span><span>Hari</span></div>
                                <div class="countdown-item"><span id="hours">00</span><span>Jam</span></div>
                                <div class="countdown-item"><span id="minutes">00</span><span>Menit</span></div>
                                <div class="countdown-item"><span id="seconds">00</span><span>Detik</span></div>
                            </div>
                            <p class="info-text" style="margin-top: 20px;">Halaman akan memuat ulang otomatis ketika waktu pengumuman tiba. <br>Anda juga dapat melakukan <strong>refresh manual</strong>.</p>
                            @if(!empty($errorMessage))
                                <p class="message-danger">{{ $errorMessage }}</p>
                            @endif
                        </section>

                        <!-- Tampilkan Pesan Kepala Madrasah saat hitung mundur -->
                        @if(!$foundStudent)
                            <section class="testimoni-section card fade-in scroll-reveal">
                                <h2>Pesan dari Kepala Madrasah</h2>
                                <div class="testimoni-grid">
                                    <div class="testimoni-item">
                                        <div class="head-teacher-photo">
                                            <img src="{{ asset('assets/kepalamadrasah.png') }}" alt="{{ $kepalaMadrasahMessage['name'] }}" class="photo">
                                        </div>
                                        <p>"{{ $kepalaMadrasahMessage['message'] }}"</p>
                                        <span class="author">- {{ $kepalaMadrasahMessage['name'] }} <br>({{ $kepalaMadrasahMessage['position'] }})</span>
                                    </div>
                                </div>
                            </section>
                        @endif

                    <!-- FASE 2: SUDAH WAKTUNYA RILIS -->
                    @else
                        <!-- Form input kelulusan (jika belum dicari / tidak ketemu) -->
                        @if(!$foundStudent)
                            <section id="input-section" class="card fade-in scroll-reveal">
                                <h2>Cek Status Kelulusanmu!</h2>
                                <div class="illustration-container">
                                    <img src="{{ asset('assets/graduation_illustration.png') }}" alt="Ilustrasi Siswa Belajar">
                                </div>
                                <div style="display:flex; gap:10px; justify-content:center; margin-bottom: 25px;">
                                    <button type="button" class="btn-cek-nopes" id="btn-cek-nopes" style="margin:0;">Cek Nomor Peserta?</button>
                                    <button type="button" class="btn-cek-nisn" id="btn-cek-nisn" style="margin:0;">Cek NISN?</button>
                                </div>
                                
                                <form action="{{ route('home') }}" method="POST" id="graduation-form">
                                    @csrf
                                    <div class="form-group">
                                        <label for="nomorPeserta">Nomor Peserta Ujian:</label>
                                        <input type="text" id="nomorPeserta" name="nomorPeserta" placeholder="Contoh: 25-10-10-2-0089-0001" required value="{{ old('nomorPeserta') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="nisn">NISN:</label>
                                        <input type="text" id="nisn" name="nisn" placeholder="Contoh: 0098765432" required value="{{ old('nisn') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggalLahir">Tanggal Lahir:</label>
                                        <input type="date" id="tanggalLahir" name="tanggalLahir" required value="{{ old('tanggalLahir') }}">
                                    </div>
                                    <button type="submit" class="btn-check">Cek Kelulusan</button>
                                </form>
                                <p class="info-text">Masukkan data dengan benar untuk mengetahui status kelulusan Anda.</p>
                                @if(!empty($errorMessage))
                                    <p class="message-danger">{{ $errorMessage }}</p>
                                @endif
                            </section>

                            <section class="testimoni-section card fade-in scroll-reveal">
                                <h2>Pesan dari Kepala Madrasah</h2>
                                <div class="testimoni-grid">
                                    <div class="testimoni-item">
                                        <div class="head-teacher-photo">
                                            <img src="{{ asset('assets/kepalamadrasah.png') }}" alt="{{ $kepalaMadrasahMessage['name'] }}" class="photo">
                                        </div>
                                        <p>"{{ $kepalaMadrasahMessage['message'] }}"</p>
                                        <span class="author">- {{ $kepalaMadrasahMessage['name'] }} <br>({{ $kepalaMadrasahMessage['position'] }})</span>
                                    </div>
                                </div>
                            </section>
                        @endif

                        <!-- Tampilkan Hasil Pencarian Kelulusan -->
                        @if($foundStudent)
                            @if($batchReleaseNotYet)
                                <section id="result-section" class="card fade-in scroll-reveal">
                                    <h2 class="status-tidak-ditemukan">Tunggu Antrian!</h2>
                                    <p class="message-danger" style="font-size:1.05em; line-height:1.6;">
                                        Silakan menunggu giliran rilis akun Anda pada:<br>
                                        <strong>pukul {{ \Carbon\Carbon::parse($batchReleaseTime)->format('H:i') }} WIB</strong>.<br>
                                        Silakan cek kembali setelah waktu tersebut.
                                    </p>
                                    <button class="btn-back" onclick="window.location.href='{{ route('home') }}'">Kembali</button>
                                </section>
                            @else
                                <section id="result-section" class="card fade-in scroll-reveal">
                                    <div class="result-content">
                                        @if($isGraduated)
                                            <h2 class="status-lulus">Selamat!<br> {{ $foundStudent->nama }}</h2>
                                            
                                            <p class="student-info"><span class="info-label">Nomor Peserta</span> <span class="info-value"><strong>: {{ $foundStudent->nomor_peserta }}</strong></span></p>
                                            <p class="student-info"><span class="info-label">NISN</span> <span class="info-value"><strong>: {{ $foundStudent->nisn }}</strong></span></p>
                                            <p class="student-info"><span class="info-label">TTL</span> <span class="info-value"><strong>: {{ $foundStudent->tempat_lahir }}, {{ $foundStudent->tanggal_lahir->format('d-m-Y') }}</strong></span></p>
                                            <p class="student-info"><span class="info-label">Jenis Kelamin</span> <span class="info-value"><strong>: {{ $foundStudent->jenis_kelamin }}</strong></span></p>
                                            <p class="student-info"><span class="info-label">Kelas</span> <span class="info-value"><strong>: {{ $foundStudent->kelas }}</strong></span></p>
                                            
                                            <p class="status-text">Anda dinyatakan <strong style="color:var(--success-color);">LULUS</strong> dari MTsN 11 Majalengka.</p>
                                            <p class="message-success">Kami bangga dengan pencapaian Anda! Teruslah belajar, raih cita-cita, dan jaga nama baik almamater.<br>Rayakan rasa syukur Anda secara positif dengan berbagi kebahagiaan mengisi kesan dan pesan di bawah!</p>

                                            <!-- Penanganan unduhan SKL PDF dinamis -->
                                            @php
                                                $namaClean = strtoupper(str_replace(['.', ','], '', $foundStudent->nama));
                                                $namaClean = preg_replace('/\s+/', ' ', $namaClean);
                                                $namaFile = str_replace(' ', '_', $namaClean);
                                                $sklFileName = "SKL_{$foundStudent->nisn}-{$namaFile}.pdf";
                                                
                                                // Cek file fisik di storage/public/skl atau assets/skl
                                                $sklExists = file_exists(public_path("assets/skl/{$sklFileName}"));
                                            @endphp

                                            @if($sklExists)
                                                <a href="{{ asset('assets/skl/' . $sklFileName) }}" class="btn-download" download="{{ $sklFileName }}">Unduh Surat Keterangan Lulus (PDF)</a>
                                            @else
                                                <span class="btn-download disabled">Unduh Surat Keterangan Lulus (PDF)</span>
                                                <p class="download-notice">Berkas SKL PDF Anda belum diterbitkan oleh Admin. Silakan cek kembali nanti atau hubungi madrasah.</p>
                                            @endif
                                            
                                            <p class="info-download-skL">Surat Keterangan Lulus (fisik asli) dapat diambil di Madrasah pada jam kerja.</p>

                                            <div class="testimoni-section card fade-in scroll-reveal" style="margin-top:30px; box-shadow:none; border: 1px solid var(--border-color);">
                                                <h2>Pesan dari Kepala Madrasah</h2>
                                                <div class="testimoni-grid">
                                                    <div class="testimoni-item">
                                                        <div class="head-teacher-photo">
                                                            <img src="{{ asset('assets/kepalamadrasah.png') }}" alt="{{ $kepalaMadrasahMessage['name'] }}" class="photo">
                                                        </div>
                                                        <p>"{{ $kepalaMadrasahMessage['message'] }}"</p>
                                                        <span class="author">- {{ $kepalaMadrasahMessage['name'] }} <br>({{ $kepalaMadrasahMessage['position'] }})</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Form Kirim Testimoni -->
                                            <div class="student-testimonial-form-section">
                                                <h3>Bagikan Kesan & Pesanmu!</h3>
                                                <form id="student-testimonial-form" class="good-form">
                                                    <p class="form-description">Kami akan sangat senang mendengar kesan dan pesan Anda selama belajar di MTsN 11 Majalengka.</p>
                                                    <input type="hidden" id="studentNameForTestimonial" name="studentName" value="{{ $foundStudent->nama }}">
                                                    <div class="form-group">
                                                        <label for="testimonialMessage">Kesan & Pesan Anda:</label>
                                                        <textarea id="testimonialMessage" name="testimonialMessage" rows="5" placeholder="Tulis pengalaman indah atau kesan pesan Anda di sini..." required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn-submit-testimonial">Kirim Kesan & Pesan</button>
                                                    <div id="testimonial-response" class="info-text" style="margin-top: 10px;"></div>
                                                </form>
                                            </div>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    startConfetti(); // Animasi perayaan kelulusan
                                                    saveCheckHistory(
                                                        "{{ $foundStudent->nomor_peserta }}",
                                                        "{{ $foundStudent->nama }}",
                                                        "{{ $foundStudent->status_kelulusan }}"
                                                    );
                                                });
                                            </script>

                                        @else
                                            <h2 class="status-tidak-lulus">Mohon Maaf, <br> {{ $foundStudent->nama }}</h2>
                                            <p class="status-text" style="font-size:1.1em; line-height:1.6;">
                                                Berdasarkan hasil keputusan rapat dewan guru, Anda dinyatakan:<br>
                                                <strong style="color:var(--danger-color); font-size:1.2em;">BELUM DINYATAKAN LULUS / DITANGGUHKAN</strong>
                                            </p>
                                            <p class="message-danger">Untuk informasi lebih lanjut mengenai status administrasi Anda, silakan hubungi Wakil Kepala Madrasah bidang Kurikulum dan Wali Kelas Anda pada jam kerja.</p>
                                            <p class="message-info" style="font-weight:500;">Jangan patah semangat, jalan menuju kesuksesan masih membentang luas di depan Anda!</p>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    saveCheckHistory(
                                                        "{{ $foundStudent->nomor_peserta }}",
                                                        "{{ $foundStudent->nama }}",
                                                        "Tidak Lulus"
                                                    );
                                                });
                                            </script>
                                        @endif

                                        <button class="btn-back" onclick="window.location.href='{{ route('home') }}'">Kembali ke Pencarian</button>
                                    </div>
                                </section>
                            @endif
                        @endif
                    @endif
                </div>

                <!-- SIDEBAR KANAN (FEED INTERAKTIF) -->
                <div class="sidebar-column">
                    <!-- A. PESAN GURU -->
                    <section class="teacher-message-display-section card fade-in scroll-reveal">
                        <h2>Pesan dari Guru</h2>
                        @if($teacherMessages->isNotEmpty())
                            <ul class="teacher-message-list-public">
                                @foreach($teacherMessages as $msg)
                                    <li class="teacher-message-list-public-item scroll-reveal" data-item-id="{{ $msg->uid }}" data-item-type="teacher_message">
                                        <span class="date">{{ $msg->date->format('d M Y H:i') }}</span>
                                        <p>"{{ $msg->message }}"</p>
                                        <span class="author">- {{ $msg->name }}</span>
                                        
                                        <div class="feedback-actions">
                                            <button class="like-button" data-id="{{ $msg->uid }}" data-type="teacher_message">
                                                <i class="fa-solid fa-heart"></i>
                                                <span class="like-count">{{ $msg->likes }}</span>
                                                <span class="like-label">Suka</span>
                                            </button>
                                            <button class="comment-toggle-button" data-id="{{ $msg->uid }}">
                                                <i class="fa-solid fa-comment"></i> Komentar
                                            </button>
                                        </div>

                                        <div class="comments-section" id="comments-section-{{ $msg->uid }}" style="display: none;">
                                            <ul class="comments-list">
                                                @foreach($msg->comments as $comment)
                                                    <li class="comment-item">
                                                        <span class="comment-author">{{ $comment->author }}</span>
                                                        <span class="comment-date">{{ $comment->date->format('d M Y H:i') }}</span>
                                                        <p class="comment-text">{{ $comment->comment }}</p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <form class="comment-form" onsubmit="addComment(event, '{{ $msg->uid }}', 'teacher_message')">
                                                <input type="text" placeholder="Nama Anda" class="comment-author-input">
                                                <textarea placeholder="Tambahkan komentar..." required></textarea>
                                                <button type="submit">Kirim Komentar</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p style="text-align: center; color: #64748b;">Belum ada pesan petuah dari guru.</p>
                        @endif
                    </section>

                    <!-- B. TESTIMONI SISWA -->
                    <section class="testimonial-display-section card fade-in scroll-reveal">
                        <h2>Kesan & Pesan Siswa</h2>
                        @if($testimonials->isNotEmpty())
                            <ul id="testimonial-list-public-live" class="testimonial-list-public">
                                @foreach($testimonials as $t)
                                    <li class="testimonial-list-public-item scroll-reveal" data-item-id="{{ $t->uid }}" data-item-type="testimonial">
                                        <span class="date">{{ $t->date->format('d M Y H:i') }}</span>
                                        <p>"{{ $t->message }}"</p>
                                        <span class="author">- {{ $t->name }}</span>

                                        <div class="feedback-actions">
                                            <button class="like-button" data-id="{{ $t->uid }}" data-type="testimonial">
                                                <i class="fa-solid fa-heart"></i>
                                                <span class="like-count">{{ $t->likes }}</span>
                                                <span class="like-label">Suka</span>
                                            </button>
                                            <button class="comment-toggle-button" data-id="{{ $t->uid }}">
                                                <i class="fa-solid fa-comment"></i> Komentar
                                            </button>
                                        </div>

                                        <div class="comments-section" id="comments-section-{{ $t->uid }}" style="display: none;">
                                            <ul class="comments-list">
                                                @foreach($t->comments as $comment)
                                                    <li class="comment-item">
                                                        <span class="comment-author">{{ $comment->author }}</span>
                                                        <span class="comment-date">{{ $comment->date->format('d M Y H:i') }}</span>
                                                        <p class="comment-text">{{ $comment->comment }}</p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <form class="comment-form" onsubmit="addComment(event, '{{ $t->uid }}', 'testimonial')">
                                                <input type="text" placeholder="Nama Anda" class="comment-author-input">
                                                <textarea placeholder="Tambahkan komentar..." required></textarea>
                                                <button type="submit">Kirim Komentar</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p id="no-testimonials-msg" style="text-align: center; color: #64748b;">Belum ada pesan dan kesan dari siswa lulus.</p>
                        @endif
                    </section>

                    <!-- C. LOG RIWAYAT -->
                    <section class="check-history-section card fade-in scroll-reveal">
                        <h2>Riwayat Pengecekan Terkini</h2>
                        <ul class="check-history-list">
                            @if($checkHistory->isNotEmpty())
                                @foreach($checkHistory as $entry)
                                    <li class="check-history-item scroll-reveal">
                                        <span class="timestamp">{{ $entry->timestamp->format('d M Y H:i') }}</span>
                                        <span class="details">No. Peserta: <strong>{{ $entry->nomor_peserta }}</strong></span><br>
                                        <span class="details">Nama: <strong>{{ $entry->student_name }}</strong> baru saja mengecek kelulusan.</span>
                                    </li>
                                @endforeach
                            @else
                                <p style="text-align: center; color: #64748b;">Belum ada aktivitas pengecekan.</p>
                            @endif
                        </ul>
                    </section>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; {{ date('Y') }} MTsN 11 Majalengka. Semua Hak Dilindungi. Developed by A.T. Aditya & Antigravity</p>
        </footer>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('script.js') }}"></script>
<script>
    const targetDate = "{{ $targetDate }}";
    const isPageReady = {{ $showResult ? 'true' : 'false' }};

    // Animasi Reveal saat scroll
    document.addEventListener('DOMContentLoaded', function() {
        const revealEls = document.querySelectorAll('.scroll-reveal');
        const revealOnScroll = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        };
        const observer = new IntersectionObserver(revealOnScroll, { threshold: 0.13 });
        revealEls.forEach(el => observer.observe(el));
    });

    // Kontrol Himbauan Modal yang Dipicu Setiap Kali Klik Cek Kelulusan
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('modal-warning-overlay');
        var btn = document.getElementById('btn-modal-confirm');
        var checkbox = document.getElementById('modal-warning-checkbox');
        var form = document.getElementById('graduation-form');
        var isWarningConfirmed = false;
        
        if (modal && btn && checkbox) {
            // Modal disembunyikan di awal
            modal.style.display = 'none';

            // Intersept pengiriman formulir setiap kali submit
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!isWarningConfirmed) {
                        e.preventDefault();
                        // Reset checkbox dan tombol persetujuan setiap kali dibuka
                        checkbox.checked = false;
                        btn.disabled = true;
                        modal.style.display = 'flex';
                    }
                });
            }

            checkbox.addEventListener('change', function() {
                btn.disabled = !checkbox.checked;
            });

            btn.addEventListener('click', function() {
                isWarningConfirmed = true;
                modal.style.display = 'none';
                
                // Kirim formulir setelah siswa menyetujui himbauan
                if (form) {
                    form.submit();
                }
                
                // Reset flag pengaman
                isWarningConfirmed = false;
            });
        }
    });

        // Modal Cek Nomor Peserta
        var btnNopes = document.getElementById('btn-cek-nopes');
        var modalNopes = document.getElementById('modal-nopes-overlay');
        var closeNopes = document.getElementById('close-modal-nopes');
        if (btnNopes && modalNopes && closeNopes) {
            btnNopes.addEventListener('click', () => modalNopes.style.display = 'flex');
            closeNopes.addEventListener('click', () => modalNopes.style.display = 'none');
            modalNopes.addEventListener('click', (e) => { if (e.target === modalNopes) modalNopes.style.display = 'none'; });
        }

        // Modal Cek NISN
        var btnNisn = document.getElementById('btn-cek-nisn');
        var modalNisn = document.getElementById('modal-nisn-overlay');
        var closeNisn = document.getElementById('close-modal-nisn');
        if (btnNisn && modalNisn && closeNisn) {
            btnNisn.addEventListener('click', () => modalNisn.style.display = 'flex');
            closeNisn.addEventListener('click', () => modalNisn.style.display = 'none');
            modalNisn.addEventListener('click', (e) => { if (e.target === modalNisn) modalNisn.style.display = 'none'; });
        }

        // AJAX Cek Nopes (Server-side secure lookup)
        var formCekNopes = document.getElementById('form-cek-nopes');
        if (formCekNopes) {
            formCekNopes.addEventListener('submit', function(e) {
                e.preventDefault();
                var nisn = document.getElementById('nopes-nisn').value.trim();
                var tgl = document.getElementById('nopes-tgl').value.trim();
                var result = document.getElementById('modal-nopes-result');
                var error = document.getElementById('modal-nopes-error');
                result.textContent = '';
                error.textContent = '';

                fetch('{{ route("lookup.nopes") }}?nisn=' + encodeURIComponent(nisn) + '&tanggal_lahir=' + encodeURIComponent(tgl))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            result.textContent = "Nomor Peserta Anda: " + data.nomor_peserta;
                        } else {
                            error.textContent = data.message;
                        }
                    })
                    .catch(() => { error.textContent = "Gagal memproses kueri."; });
            });
        }

        // AJAX Cek NISN (Server-side secure lookup)
        var formCekNisn = document.getElementById('form-cek-nisn');
        if (formCekNisn) {
            formCekNisn.addEventListener('submit', function(e) {
                e.preventDefault();
                var nama = document.getElementById('nisn-nama').value.trim();
                var tgl = document.getElementById('nisn-tgl').value.trim();
                var result = document.getElementById('modal-nisn-result');
                var error = document.getElementById('modal-nisn-error');
                result.textContent = '';
                error.textContent = '';

                fetch('{{ route("lookup.nisn") }}?nama=' + encodeURIComponent(nama) + '&tanggal_lahir=' + encodeURIComponent(tgl))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            result.textContent = "NISN Anda: " + data.nisn;
                        } else {
                            error.textContent = data.message;
                        }
                    })
                    .catch(() => { error.textContent = "Gagal memproses kueri."; });
            });
        }
    });

    // --- FUNGSI INTERAKSI AJAX (Likes, Comments, Testimonials) DENGAN PROTEKSI CSRF ---

    // A. Simpan Riwayat
    async function saveCheckHistory(nomorPeserta, studentName, checkResult) {
        const formData = new FormData();
        formData.append('nomorPeserta', nomorPeserta);
        formData.append('studentName', studentName);
        formData.append('checkResult', checkResult);

        try {
            await fetch('{{ route("interaction.history") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
                body: formData
            });
        } catch (e) {
            console.error('Error logging history:', e);
        }
    }

    // B. Like Item
    async function likeItem(itemId, itemType, buttonElement) {
        const likedItems = JSON.parse(localStorage.getItem('likedItems') || '{}');
        if (likedItems[itemId]) {
            alert('Anda sudah menyukai ini!');
            return;
        }

        const formData = new FormData();
        formData.append('itemId', itemId);
        formData.append('itemType', itemType);

        try {
            const response = await fetch('{{ route("interaction.like") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                const likeCountSpan = buttonElement.querySelector('.like-count');
                if (likeCountSpan) {
                    likeCountSpan.textContent = data.newLikesCount;
                }
                buttonElement.classList.add('liked');
                buttonElement.disabled = true;
                likedItems[itemId] = true;
                localStorage.setItem('likedItems', JSON.stringify(likedItems));
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    // C. Tambah Komentar
    async function addComment(event, itemId, itemType) {
        event.preventDefault();
        const form = event.target;
        const authorInput = form.querySelector('.comment-author-input');
        const messageTextarea = form.querySelector('textarea');
        
        const author = authorInput.value.trim();
        const comment = messageTextarea.value.trim();

        if (!comment) {
            alert('Komentar tidak boleh kosong.');
            return;
        }

        const formData = new FormData();
        formData.append('itemId', itemId);
        formData.append('itemType', itemType);
        formData.append('commentAuthor', author);
        formData.append('commentMessage', comment);

        try {
            const response = await fetch('{{ route("interaction.comment") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                const commentsSection = document.getElementById('comments-section-' + itemId);
                const commentsList = commentsSection.querySelector('.comments-list');
                
                const newLi = document.createElement('li');
                newLi.classList.add('comment-item');
                
                const formattedDate = new Date(data.newComment.date).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                newLi.innerHTML = `
                    <span class="comment-author">${escapeHtml(data.newComment.author)}</span>
                    <span class="comment-date">${formattedDate}</span>
                    <p class="comment-text">${escapeHtml(data.newComment.comment)}</p>
                `;
                commentsList.appendChild(newLi);

                messageTextarea.value = '';
                authorInput.value = '';
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (e) {
            alert('Gagal mengirim komentar.');
        }
    }

    // Toggle Komentar Area
    function toggleComments(itemId) {
        const commentsSection = document.getElementById('comments-section-' + itemId);
        if (commentsSection) {
            commentsSection.style.display = (commentsSection.style.display === 'none' || commentsSection.style.display === '') ? 'block' : 'none';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Pasang Suka & Toggle Komentar Global Listener
    document.addEventListener('DOMContentLoaded', () => {
        // Cek status suka di localStorage saat load awal
        const likedItems = JSON.parse(localStorage.getItem('likedItems') || '{}');
        document.querySelectorAll('.like-button').forEach(button => {
            const itemId = button.dataset.id;
            if (itemId && likedItems[itemId]) {
                button.classList.add('liked');
                button.disabled = true;
            }
        });

        document.body.addEventListener('click', function (event) {
            // Suka
            const likeBtn = event.target.closest('.like-button');
            if (likeBtn && !likeBtn.disabled) {
                const itemId = likeBtn.dataset.id;
                const itemType = likeBtn.dataset.type;
                if (itemId && itemType) {
                    likeItem(itemId, itemType, likeBtn);
                }
            }
            // Komentar Toggle
            const commentBtn = event.target.closest('.comment-toggle-button');
            if (commentBtn) {
                const itemId = commentBtn.dataset.id;
                if (itemId) {
                    toggleComments(itemId);
                }
            }
        });

        // Form Testimoni Ajax
        const testiForm = document.getElementById('student-testimonial-form');
        if (testiForm) {
            testiForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const name = document.getElementById('studentNameForTestimonial').value;
                const msg = document.getElementById('testimonialMessage').value.trim();
                const responseDiv = document.getElementById('testimonial-response');
                const submitBtn = testiForm.querySelector('.btn-submit-testimonial');

                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';
                responseDiv.textContent = '';

                const formData = new FormData();
                formData.append('studentName', name);
                formData.append('testimonialMessage', msg);

                try {
                    const response = await fetch('{{ route("interaction.testimonial") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.csrfToken },
                        body: formData
                    });
                    const data = await response.json();

                    if (data.success) {
                        responseDiv.style.color = 'var(--success-color)';
                        responseDiv.textContent = data.message;
                        document.getElementById('testimonialMessage').value = '';
                    } else {
                        responseDiv.style.color = 'var(--danger-color)';
                        responseDiv.textContent = data.message;
                    }
                } catch (e) {
                    responseDiv.style.color = 'var(--danger-color)';
                    responseDiv.textContent = 'Gagal terhubung ke server.';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Kesan & Pesan';
                }
            });
        }
    });
</script>
@endsection
