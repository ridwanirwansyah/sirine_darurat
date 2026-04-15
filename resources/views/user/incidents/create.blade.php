<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Lapor Insiden - Sisirin'e</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --app-height: 100dvh;
        }

        body {
            background: #f8fafc;
            margin: 0;
            height: var(--app-height);
            overflow: hidden;
        }

        .app-shell {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            height: var(--app-height);
            display: flex;
            flex-direction: column;
            position: relative;
            padding-top: env(safe-area-inset-top);
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 20px;
        }

        .header-fixed {
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .nav-wrapper {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            padding-bottom: calc(15px + env(safe-area-inset-bottom));
            background: linear-gradient(to top, white 95%, transparent);
            z-index: 40;
            width: 100%;
            box-sizing: border-box;
            margin-top: auto;
        }

        .nav-bar {
            background: #0f172a;
            border-radius: 100px;
            height: 65px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0 8px;
        }

        .modal-backdrop {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
        }

        /* Image Preview */
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 8px;
        }

        .image-preview {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .image-upload-btn {
            aspect-ratio: 1/1;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }

        .image-upload-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .image-upload-btn i {
            font-size: 24px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .image-upload-btn span {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        /* Photo Sheet */
        .photo-sheet {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }

        .photo-sheet-box {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 24px 24px 0 0;
            padding: 20px 20px calc(20px + env(safe-area-inset-bottom));
        }

        .photo-sheet-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 14px;
            border: none;
            background: #f8fafc;
            cursor: pointer;
            margin-bottom: 8px;
            transition: background 0.15s;
            font-family: inherit;
        }

        .photo-sheet-btn:active {
            background: #f1f5f9;
        }

        .photo-sheet-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .photo-sheet-btn-label {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .photo-sheet-btn-sub {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 1px;
        }

        .photo-sheet-cancel {
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            margin-top: 4px;
            font-family: inherit;
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <div class="content-wrapper">

            <!-- HEADER -->
            <div class="header-fixed bg-gradient-to-br from-red-600 to-red-700 text-white px-5 pt-4 pb-3">
                <div class="flex items-center gap-3 mb-2">
                    <button onclick="window.history.back()"
                        class="p-1.5 hover:bg-red-500 rounded-lg transition flex-shrink-0">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </button>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-base font-bold leading-tight">Lapor Insiden</h1>
                        <p class="text-[10px] opacity-75 mt-0.5">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Laporkan kejadian darurat
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="bg-white/15 rounded-full px-2.5 py-0.5 text-[10px] font-bold whitespace-nowrap">
                        <i class="fas fa-camera mr-1"></i>Maks. 3 Foto
                    </span>
                    <span class="bg-red-400/40 rounded-full px-2.5 py-0.5 text-[10px] font-bold whitespace-nowrap">
                        <i class="fas fa-keyboard mr-1"></i>Min. 10 Karakter
                    </span>
                    <span class="bg-yellow-400/30 rounded-full px-2.5 py-0.5 text-[10px] font-bold whitespace-nowrap">
                        <i class="fas fa-shield-alt mr-1"></i>Darurat Saja
                    </span>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="main-content">
                <div class="px-5 py-4">
                    <form id="incidentForm" class="space-y-4" enctype="multipart/form-data">

                        <!-- JENIS INSIDEN -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                Jenis Kejadian <span class="text-red-600">*</span>
                            </label>
                            <select name="type" id="incidentType" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-red-500 transition">
                                <option value="">-- Pilih Jenis Kejadian --</option>
                                <option value="KEBAKARAN">🔥 Kebakaran</option>
                                <option value="PENCURIAN">🚨 Pencurian / Pembongkaran</option>
                                <option value="GEMPA_BUMI">📍 Gempa Bumi</option>
                                <option value="BANJIR">🌊 Banjir / Banjir Bandang</option>
                                <option value="KECELAKAAN">🚗 Kecelakaan / Tabrakan</option>
                                <option value="PENYERANGAN">⚠️ Penyerangan / Tindak Kriminal</option>
                                <option value="GANGGUAN_KEAMANAN">🛡️ Gangguan Keamanan</option>
                                <option value="LAINNYA">❓ Lainnya</option>
                            </select>
                        </div>

                        <!-- DESKRIPSI -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-align-left text-red-600 mr-2"></i>
                                Deskripsi Kejadian <span class="text-red-600">*</span>
                            </label>
                            <textarea name="description" id="incidentDesc" required
                                placeholder="Jelaskan detail kejadian dengan sejelasnya (minimal 10 karakter)..."
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-red-500 transition resize-vertical h-28"
                                minlength="10" maxlength="500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="charCount">0</span>/500 karakter
                            </p>
                        </div>

                        <!-- FOTO KEJADIAN -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                <i class="fas fa-camera text-red-600 mr-2"></i>
                                Foto Kejadian
                                <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Maksimal 3 foto · Bisa dari kamera atau galeri</p>

                            <input type="file" id="imageInputGallery" name="images[]" multiple accept="image/*" class="hidden">
                            <input type="file" id="imageInputCamera" name="images[]" accept="image/*" capture="environment" class="hidden">

                            <div id="imagePreview" class="image-preview-container">
                                <div id="uploadButton" class="image-upload-btn" onclick="openPhotoSheet()">
                                    <i class="fas fa-camera"></i>
                                    <span>Tambah Foto</span>
                                </div>
                            </div>

                            <p id="imageError" class="text-xs text-red-600 mt-2 hidden"></p>
                        </div>

                        <!-- LOKASI -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>
                                Lokasi <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="location" id="incidentLocation" required
                                placeholder="Contoh: Ruang Server, Pintu Utama, dll..."
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-red-500 transition">
                        </div>

                        <!-- INFO BOX -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                            <div class="flex gap-2">
                                <i class="fas fa-info-circle text-yellow-600 text-base flex-shrink-0 mt-0.5"></i>
                                <div>
                                    <p class="text-xs font-bold text-yellow-800 mb-1">Perhatian Penting</p>
                                    <ul class="text-[10px] text-yellow-700 space-y-0.5 ml-3 list-disc">
                                        <li>Hanya untuk kejadian darurat yang sebenarnya</li>
                                        <li>Laporan palsu membahayakan keselamatan bersama</li>
                                        <li>Segera hubungi petugas keamanan atau polisi</li>
                                        <li>Foto membantu petugas memahami situasi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="window.history.back()"
                                class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-lg transition text-sm">
                                Batal
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-paper-plane"></i> Kirim Laporan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- NAV -->
        <div class="nav-wrapper">
            <div class="nav-bar">
                <button onclick="location.href='/user/dashboard'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-house"></i>
                </button>
                <button onclick="location.href='/user/incidents/create'"
                    class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-red-500 text-2xl shadow-xl -mt-6 border-[6px] border-[#0f172a] shrink-0">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </button>
                <button onclick="location.href='/user/incidents'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition shrink-0">
                    <i class="fa-solid fa-clipboard-list"></i>
                </button>
                <button onclick="location.href='/user/riwayat'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl shrink-0">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button onclick="location.href='/user/profile'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl shrink-0">
                    <i class="fa-solid fa-user"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- PHOTO SHEET -->
    <div id="photoSheet" class="photo-sheet hidden">
        <div class="photo-sheet-box">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-1">Tambah Foto</p>

            <button class="photo-sheet-btn" onclick="triggerCamera()">
                <div class="photo-sheet-icon" style="background:#fef2f2;color:#dc2626">
                    <i class="fas fa-camera"></i>
                </div>
                <div class="text-left">
                    <div class="photo-sheet-btn-label">Ambil Foto</div>
                    <div class="photo-sheet-btn-sub">Buka kamera langsung</div>
                </div>
            </button>

            <button class="photo-sheet-btn" onclick="triggerGallery()">
                <div class="photo-sheet-icon" style="background:#eff6ff;color:#2563eb">
                    <i class="fas fa-images"></i>
                </div>
                <div class="text-left">
                    <div class="photo-sheet-btn-label">Pilih dari Galeri</div>
                    <div class="photo-sheet-btn-sub">Pilih foto yang sudah ada</div>
                </div>
            </button>

            <button class="photo-sheet-cancel" onclick="closePhotoSheet()">Batal</button>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-5 modal-backdrop">
        <div class="bg-white rounded-[28px] max-w-sm w-full shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white px-6 pt-8 pb-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-white text-2xl"></i>
                </div>
                <h2 class="text-lg font-black mb-1">Laporan Terkirim!</h2>
                <p class="text-xs opacity-80">Kejadian berhasil didokumentasikan</p>
            </div>
            <div class="p-5 space-y-3">
                <div class="bg-slate-50 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-bullhorn text-red-600 text-xs"></i>
                    </div>
                    <p class="text-xs text-slate-600 font-medium">Sirine kini dapat diaktifkan kembali dari dashboard</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-shield-alt text-blue-600 text-xs"></i>
                    </div>
                    <p class="text-xs text-slate-600 font-medium">Petugas keamanan akan segera menindaklanjuti</p>
                </div>
                <button id="goToDashboardBtn"
                    class="w-full px-4 py-3.5 bg-red-600 hover:bg-red-700 text-white font-black text-sm rounded-xl transition flex items-center justify-center gap-2 mt-1">
                    <i class="fas fa-house"></i> Kembali ke Dashboard
                </button>
            </div>
        </div>
    </div>

    <!-- ERROR MODAL -->
    <div id="errorModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-5 modal-backdrop">
        <div class="bg-white rounded-[28px] max-w-sm w-full shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white px-6 pt-6 pb-5 text-center">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-exclamation text-white text-xl"></i>
                </div>
                <h2 class="text-base font-black mb-1">Gagal Mengirim</h2>
                <p class="text-xs opacity-80">Laporan tidak terkirim</p>
            </div>
            <div class="p-5">
                <p id="errorMessage" class="text-sm text-gray-600 text-center mb-4"></p>
                <button onclick="document.getElementById('errorModal').classList.add('hidden')"
                    class="w-full px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                    Coba Lagi
                </button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('incidentForm');
        const descInput = document.getElementById('incidentDesc');
        const charCount = document.getElementById('charCount');
        const submitBtn = document.getElementById('submitBtn');
        const goToDashBtn = document.getElementById('goToDashboardBtn');
        const galleryInput = document.getElementById('imageInputGallery');
        const cameraInput = document.getElementById('imageInputCamera');
        const uploadButton = document.getElementById('uploadButton');
        const imagePreview = document.getElementById('imagePreview');
        const imageError = document.getElementById('imageError');
        // Ambil parameter URL
        const urlParams = new URLSearchParams(window.location.search);
        const alarmSessionId = urlParams.get('session_id');
        const isFromActiveAlarm = urlParams.get('source') === 'active_alarm';

        let selectedImages = [];

        /* ── Char count ── */
        descInput.addEventListener('input', e => {
            charCount.textContent = e.target.value.length;
        });


        /* ── Photo Sheet ── */
        function openPhotoSheet() {
            document.getElementById('photoSheet').classList.remove('hidden');
        }

        function closePhotoSheet() {
            document.getElementById('photoSheet').classList.add('hidden');
        }

        function triggerCamera() {
            closePhotoSheet();
            cameraInput.value = '';
            cameraInput.click();
        }

        function triggerGallery() {
            closePhotoSheet();
            galleryInput.value = '';
            galleryInput.click();
        }

        document.getElementById('photoSheet').addEventListener('click', function(e) {
            if (e.target === this) closePhotoSheet();
        });

        /* ── Compress ── */
        function compressImage(file, maxSize = 1280, quality = 0.72) {
            return new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = ev => {
                    const img = new Image();
                    img.onload = () => {
                        let {
                            width,
                            height
                        } = img;
                        if (width > maxSize || height > maxSize) {
                            if (width > height) {
                                height = Math.round(height * maxSize / width);
                                width = maxSize;
                            } else {
                                width = Math.round(width * maxSize / height);
                                height = maxSize;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                        canvas.toBlob(blob => {
                            resolve(new File(
                                [blob],
                                file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                    type: 'image/jpeg'
                                }
                            ));
                        }, 'image/jpeg', quality);
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        /* ── Handle file selection ── */
        async function handleFiles(files) {
            imageError.classList.add('hidden');

            if (selectedImages.length + files.length > 3) {
                imageError.textContent = 'Maksimal 3 foto yang diizinkan';
                imageError.classList.remove('hidden');
                return;
            }
            for (const file of files) {
                if (!file.type.startsWith('image/')) {
                    imageError.textContent = 'Hanya file gambar yang diizinkan';
                    imageError.classList.remove('hidden');
                    return;
                }
                if (file.size > 20 * 1024 * 1024) {
                    imageError.textContent = 'Ukuran file asli maksimal 20MB';
                    imageError.classList.remove('hidden');
                    return;
                }
            }

            uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:20px;color:#6b7280;margin-bottom:6px"></i><span style="font-size:11px;color:#6b7280">Memproses...</span>';
            uploadButton.style.pointerEvents = 'none';

            const compressed = await Promise.all(files.map(f => compressImage(f)));
            selectedImages.push(...compressed);
            updateImagePreview();
        }

        galleryInput.addEventListener('change', e => handleFiles(Array.from(e.target.files)));
        cameraInput.addEventListener('change', e => handleFiles(Array.from(e.target.files)));

        /* ── Preview ── */
        function updateImagePreview() {
            imagePreview.innerHTML = '';

            uploadButton.innerHTML = '<i class="fas fa-camera"></i><span>Tambah Foto</span>';
            uploadButton.style.pointerEvents = '';

            selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = e => {
                    const sizeKB = (file.size / 1024).toFixed(0);
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-image" onclick="removeImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                    <div style="position:absolute;bottom:4px;left:4px;background:rgba(0,0,0,0.55);color:white;font-size:8px;font-weight:700;padding:2px 5px;border-radius:4px">${sizeKB}KB</div>
                `;
                    imagePreview.insertBefore(div, imagePreview.querySelector('#uploadButton') || null);
                };
                reader.readAsDataURL(file);
            });

            if (selectedImages.length < 3) imagePreview.appendChild(uploadButton);
        }

        window.removeImage = function(index) {
            selectedImages.splice(index, 1);
            updateImagePreview();
        };

        /* ── Submit ── */
        form.addEventListener('submit', async e => {
            e.preventDefault();

            const type = document.getElementById('incidentType').value;
            const description = descInput.value;
            const location = document.getElementById('incidentLocation').value;

            if (!type) {
                showError('Silakan pilih jenis kejadian');
                return;
            }
            if (description.length < 10) {
                showError('Deskripsi minimal 10 karakter');
                return;
            }
            if (!location.trim()) {
                showError('Lokasi wajib diisi');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

            try {
                const formData = new FormData();
                formData.append('type', type);
                formData.append('description', description);
                formData.append('location', location);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                selectedImages.forEach((img, i) => formData.append(`images[${i}]`, img));

                const response = await fetch('/user/incidents', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    // Jika berasal dari sirine aktif dan ada session_id, tandai laporan sudah dibuat
                    if (isFromActiveAlarm && alarmSessionId) {
                        localStorage.setItem('report_created_for_session', alarmSessionId);
                        localStorage.removeItem('need_report_redirect');
                        // Hapus juga flag lain yang mungkin tersisa
                        localStorage.removeItem('from_active_alarm');
                        sessionStorage.removeItem('awaiting_report');
                    }
                    localStorage.setItem('fresh_incident_created', 'true');
                    document.getElementById('successModal').classList.remove('hidden');
                    goToDashBtn.onclick = () => {
                        window.location.href = '/user/dashboard';
                    };
            } else {
                showError(data.message || 'Terjadi kesalahan saat mengirim laporan.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Laporan';
            }
        } catch (err) {
            console.error('Fetch error:', err);
            showError('Koneksi gagal. Silakan coba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Laporan';
        }
        });

        function showError(msg) {
            document.getElementById('errorMessage').textContent = msg;
            document.getElementById('errorModal').classList.remove('hidden');
        }
    </script>
</body>

</html>