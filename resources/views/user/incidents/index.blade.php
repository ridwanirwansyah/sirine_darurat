<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Daftar Insiden - Sisirin'e</title>
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
            background-color: #0f172a;
            border-radius: 100px;
            height: 65px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .nav-button {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s;
            border-radius: 50%;
            cursor: pointer;
            color: #94a3b8;
        }

        .nav-button.active {
            width: 56px;
            height: 56px;
            background: white;
            color: #dc2626;
            margin-top: -8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            border: 6px solid #0f172a;
        }

        .nav-button:hover:not(.active) {
            color: #cbd5e1;
        }

        /* IMAGE PREVIEW */
        .image-preview-container {
            display: flex;
            gap: 6px;
            margin-top: 6px;
            flex-wrap: nowrap;
        }

        .image-thumb {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            flex-shrink: 0;
            transition: border-color 0.15s;
        }

        .image-thumb:hover {
            border-color: #dc2626;
        }

        .image-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-thumb-more {
            width: 60px;
            height: 60px;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* SPINNER */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* IMAGE VIEWER */
        .img-viewer {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.92);
            z-index: 200;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .img-viewer.hidden {
            display: none;
        }

        .img-viewer-img {
            max-width: 100%;
            max-height: 75vh;
            object-fit: contain;
            border-radius: 10px;
        }

        .img-viewer-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-viewer-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-viewer-counter {
            margin-top: 12px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 4px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="app-shell">
        <div class="content-wrapper">

            <!-- HEADER - kompak tapi tetap informatif -->
            <div class="header-fixed bg-gradient-to-br from-red-600 to-red-700 text-white px-5 pt-4 pb-3">
                <div class="flex items-center gap-3 mb-2">
                    <button onclick="window.history.back()" class="p-1.5 hover:bg-red-500 rounded-lg transition flex-shrink-0">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </button>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-base font-bold leading-tight">Daftar Insiden Saya</h1>
                        @if(auth()->user()->role !== 'admin')
                        <p class="text-[10px] opacity-75 mt-0.5">
                            <i class="fas fa-shield-alt mr-1"></i>Hanya admin yang dapat mengubah status
                        </p>
                        @endif
                    </div>
                </div>
                <!-- Stats pills -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="bg-white/15 rounded-full px-2.5 py-0.5 text-[10px] font-bold">
                        Total: {{ $incidents->total() }}
                    </span>
                    <span class="bg-red-400/40 rounded-full px-2.5 py-0.5 text-[10px] font-bold">
                        Aktif: {{ $activeIncidents }}
                    </span>
                    @if(isset($resolvedIncidents))
                    <span class="bg-green-400/30 rounded-full px-2.5 py-0.5 text-[10px] font-bold">
                        Selesai: {{ $resolvedIncidents }}
                    </span>
                    @endif
                    @if(isset($falseAlarmIncidents))
                    <span class="bg-yellow-400/30 rounded-full px-2.5 py-0.5 text-[10px] font-bold">
                        Alarm Palsu: {{ $falseAlarmIncidents }}
                    </span>
                    @endif
                </div>
            </div>

            <!-- CONTENT -->
            <div class="main-content">
                <div class="px-4 py-4">

                    @if($incidents->isEmpty())
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-sm">Belum ada laporan insiden</p>
                        <p class="text-gray-400 text-xs mt-1">Gunakan tombol di atas untuk membuat laporan</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($incidents as $incident)

                        {{--
                            Ambil images dari kolom JSON di tabel incidents.
                            Kolom bisa berupa array (sudah di-cast) atau string JSON.
                            Path file: storage/app/public/incidents/{filename}
                            URL akses : /storage/incidents/{filename}
                        --}}
                        @php
                        // Ambil kolom images — bisa array (sudah cast) atau string JSON
                        $imgs = $incident->images ?? [];
                        if (is_string($imgs)) {
                        $imgs = json_decode($imgs, true) ?? [];
                        }
                        if (!is_array($imgs)) {
                        $imgs = [];
                        }
                        // Normalisasi: pastikan hanya nama file (tanpa path prefix apapun)
                        $imgs = array_values(array_filter(array_map(function($f) {
                        // Hapus prefix "incidents/", "storage/incidents/", "/storage/incidents/" dll
                        $f = ltrim($f, '/');
                        $f = preg_replace('#^(storage/)?incidents/#', '', $f);
                        return $f;
                        }, $imgs)));
                        @endphp

                        <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition"
                            data-incident-id="{{ $incident->id }}"
                            data-images="{{ json_encode($imgs) }}">

                            <!-- Top row: badge tipe + badge status -->
                            <div class="flex justify-between items-start mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold
                                    @if($incident->type === 'KEBAKARAN') bg-red-50 text-red-700
                                    @elseif($incident->type === 'BENCANA_ALAM') bg-orange-50 text-orange-700
                                    @elseif($incident->type === 'KECELAKAAN') bg-yellow-50 text-yellow-700
                                    @elseif($incident->type === 'KRIMINAL') bg-purple-50 text-purple-700
                                    @else bg-gray-50 text-gray-700 @endif">
                                    <i class="fas {{ $incident->getTypeIcon() }} text-[10px]"></i>
                                    {{ $incident->getTypeLabel() }}
                                </span>
                                @php
                                $statusMap = ['ACTIVE'=>'Aktif','RESOLVED'=>'Selesai','FALSE_ALARM'=>'Alarm Palsu','PENDING'=>'Menunggu'];
                                $statusLabel = $statusMap[$incident->status] ?? ucfirst(strtolower($incident->status));
                                $statusColor = match($incident->status) {
                                'ACTIVE' => 'bg-red-100 text-red-800',
                                'RESOLVED' => 'bg-green-100 text-green-800',
                                'FALSE_ALARM' => 'bg-yellow-100 text-yellow-800',
                                'PENDING' => 'bg-orange-100 text-orange-800',
                                default => 'bg-gray-100 text-gray-700',
                                };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <!-- Deskripsi -->
                            <p class="text-sm text-gray-700 mb-2 leading-snug">{{ Str::limit($incident->description, 100) }}</p>

                            <!-- FOTO: tampilkan thumbnail jika ada -->
                            @if(count($imgs) > 0)
                            <div class="mb-2">
                                <p class="text-[10px] text-gray-400 mb-1.5 font-semibold uppercase tracking-wide">
                                    <i class="fas fa-images mr-1"></i>{{ count($imgs) }} foto
                                </p>
                                <div class="image-preview-container">
                                    @foreach(array_slice($imgs, 0, 3) as $imgFile)
                                    @php
                                    // $imgFile sudah dinormalisasi di atas — tinggal nama file saja
                                    $imgUrl = asset('storage/incidents/' . $imgFile);
                                    @endphp
                                    <div class="image-thumb"
                                        data-id="{{ $incident->id }}"
                                        data-index="{{ $loop->index }}">
                                        <img src="{{ $imgUrl }}"
                                            alt="Foto {{ $loop->iteration }}"
                                            loading="lazy"
                                            onerror="this.parentElement.style.display='none'">
                                    </div>
                                    @endforeach

                                    @if(count($imgs) > 3)
                                    <div class="image-thumb-more"
                                        data-id="{{ $incident->id }}"
                                        data-index="3">
                                        +{{ count($imgs) - 3 }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Meta info -->
                            <div class="flex justify-between items-center text-[10px] text-gray-400 mt-2">
                                <span><i class="far fa-clock mr-1"></i>{{ $incident->reported_at->locale('id')->diffForHumans() }}</span>
                                @if($incident->location)
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $incident->location }}</span>
                                @endif
                            </div>

                            <!-- Action buttons -->
                            @if($incident->status === 'ACTIVE')
                            <div class="mt-3">
                                @if(auth()->user()->role === 'admin')
                                <div class="flex gap-2">
                                    <button type="button"
                                        class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 resolve-btn"
                                        data-incident-id="{{ $incident->id }}">
                                        <i class="fas fa-check-circle"></i> Selesaikan
                                    </button>
                                    <button type="button"
                                        class="flex-1 px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1 false-alarm-btn"
                                        data-incident-id="{{ $incident->id }}">
                                        <i class="fas fa-times-circle"></i> Alarm Palsu
                                    </button>
                                </div>
                                @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 flex items-center gap-2">
                                    <i class="fas fa-info-circle text-blue-500 text-sm flex-shrink-0"></i>
                                    <div>
                                        <p class="text-xs font-bold text-blue-700">Menunggu Verifikasi Admin</p>
                                        <p class="text-[10px] text-blue-500">Laporan sedang ditinjau oleh admin</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($incidents->hasPages())
                    <div class="mt-5 flex justify-center">
                        <div class="flex gap-2 items-center">
                            @if($incidents->onFirstPage())
                            <span class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </span>
                            @else
                            <a href="{{ $incidents->previousPageUrl() }}" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            @endif

                            <span class="px-4 py-1.5 bg-red-600 text-white rounded-lg text-sm font-bold">
                                Halaman {{ $incidents->currentPage() }} dari {{ $incidents->lastPage() }}
                            </span>

                            @if($incidents->hasMorePages())
                            <a href="{{ $incidents->nextPageUrl() }}" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                            @else
                            <span class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-300 cursor-not-allowed">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif



                </div>
            </div>
        </div>

        <!-- NAV -->
        <div class="nav-wrapper">
            <div class="nav-bar">
                <button onclick="location.href='/user/dashboard'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                    <i class="fa-solid fa-house"></i>
                </button>
                <button onclick="location.href='/user/incidents/create'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </button>
                <button onclick="location.href='/user/incidents'"
                    class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-red-500 text-2xl shadow-xl -mt-6 border-[6px] border-[#0f172a]">
                    <i class="fa-solid fa-clipboard-list"></i>
                </button>
                <button onclick="location.href='/user/riwayat'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button onclick="location.href='/user/profile'"
                    class="w-12 h-12 flex items-center justify-center text-slate-400 text-xl hover:text-red-500 transition">
                    <i class="fa-solid fa-user"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- IMAGE VIEWER -->
    <div id="imgViewer" class="img-viewer hidden">
        <button class="img-viewer-close" onclick="closeViewer()">
            <i class="fas fa-times"></i>
        </button>
        <button class="img-viewer-nav" style="left:12px" onclick="viewerPrev()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="img-viewer-nav" style="right:12px" onclick="viewerNext()">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="viewerImg" class="img-viewer-img" src="" alt="Foto kejadian">
        <div id="viewerCounter" class="img-viewer-counter">1/1</div>
    </div>

    <script>
        /* ── IMAGE VIEWER ── */
        let viewerImages = [];
        let viewerIndex = 0;

        function openViewer(incidentId, startIndex) {
            const el = document.querySelector(`[data-incident-id="${incidentId}"]`);
            if (!el) return;

            const raw = JSON.parse(el.dataset.images || '[]');
            // Bangun URL lengkap dari nama file
            viewerImages = raw.map(f => {
                // Normalisasi: hapus semua prefix path, tinggal nama file
                const clean = f.replace(/^\//, '')
                    .replace(/^storage\/incidents\//, '')
                    .replace(/^incidents\//, '');
                return `/storage/incidents/${clean}`;
            });

            if (!viewerImages.length) return;

            viewerIndex = Math.min(startIndex, viewerImages.length - 1);
            renderViewer();
            document.getElementById('imgViewer').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function renderViewer() {
            const url = viewerImages[viewerIndex];
            console.log('Loading image:', url);
            document.getElementById('viewerImg').src = url;
            document.getElementById('viewerCounter').textContent =
                `${viewerIndex + 1} / ${viewerImages.length}`;
        }

        function viewerPrev() {
            if (viewerIndex > 0) {
                viewerIndex--;
                renderViewer();
            }
        }

        function viewerNext() {
            if (viewerIndex < viewerImages.length - 1) {
                viewerIndex++;
                renderViewer();
            }
        }

        function closeViewer() {
            document.getElementById('imgViewer').classList.add('hidden');
            document.getElementById('viewerImg').src = '';
            document.body.style.overflow = '';
            viewerImages = [];
            viewerIndex = 0;
        }

        // Pasang event listener ke semua thumbnail
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.image-thumb, .image-thumb-more').forEach(el => {
                el.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const index = parseInt(this.dataset.index);

                    openViewer(id, index);
                });
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (document.getElementById('imgViewer').classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft') viewerPrev();
            if (e.key === 'ArrowRight') viewerNext();
            if (e.key === 'Escape') closeViewer();
        });

        // Klik backdrop
        document.getElementById('imgViewer').addEventListener('click', function(e) {
            if (e.target === this) closeViewer();
        });

        /* ── RESOLVE / FALSE ALARM (admin only) ── */
        document.querySelectorAll('.resolve-btn, .false-alarm-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.incidentId;
                const isRsolve = this.classList.contains('resolve-btn');
                const action = isRsolve ? 'resolve' : 'false-alarm';
                const label = isRsolve ? 'menyelesaikan' : 'menandai sebagai false alarm';

                if (!confirm(`Yakin ingin ${label} insiden ini?`)) return;

                const orig = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<div class="spinner"></div> Memproses...';

                try {
                    const res = await fetch(`/user/incidents/${id}/${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();

                    if (res.ok) {
                        alert(data.message || `Insiden berhasil ${label}`);
                        window.location.reload();
                    } else {
                        alert(res.status === 403 ?
                            'Akses ditolak. Hanya admin yang dapat mengubah status.' :
                            (data.message || 'Terjadi kesalahan.'));
                        this.disabled = false;
                        this.innerHTML = orig;
                    }
                } catch {
                    alert('Koneksi gagal. Silakan coba lagi.');
                    this.disabled = false;
                    this.innerHTML = orig;
                }
            });
        });
    </script>

</body>

</html>