<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\AlarmState;
use App\Http\Controllers\User\AlarmController as UserAlarmController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


/*
|--------------------------------------------------------------------------
| USER Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\User\DashboardController as UserDashboard;
use App\Http\Controllers\User\AlarmController as UserAlarm;
use App\Http\Controllers\User\AlarmLogController as UserAlarmLog;
use App\Http\Controllers\User\ProfileController as UserProfile;
use App\Http\Controllers\User\IncidentController as UserIncident;

/*
|--------------------------------------------------------------------------
| ADMIN Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Admin\AlarmController as AdminAlarm;
use App\Http\Controllers\Admin\AlarmLogController as AdminAlarmLog;
use App\Http\Controllers\Admin\RiwayatController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\IncidentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Splash
Route::view('/', 'auth.splash');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Alarm Status API (Shared by User & Admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['check-auto-off'])
    ->get('/alarm/status', function () {
        $state = AlarmState::first();

        if ($state && $state->isExpired()) {
            // Gunakan UserAlarmController untuk forceAutoOff
            $alarmController = app(UserAlarmController::class);
            $alarmController->forceAutoOff($state);

            // Publish ALARM_OFF to MQTT to ensure relay is turned off
            app('mqtt')->publish(
                'projekiot/lampu/kendali',
                'ALARM_OFF',
                1
            );
        }

        return response()->json([
            'is_on' => $state?->is_on ?? false,
            'auto_off_at' => $state?->auto_off_at,
            'auto_off_duration' => $state?->auto_off_duration ?? 60,
        ]);
    });

// Route untuk debugging auto-off (tanpa middleware auth untuk testing)
Route::get('/debug/process-auto-off', [App\Http\Controllers\User\AlarmController::class, 'processAutoOff'])
    ->withoutMiddleware(['auth', 'role:user']);


/*
|--------------------------------------------------------------------------
| Protected User Routes
|--------------------------------------------------------------------------
*/

Route::get('/alarm/public-status', function () {
    $state = AlarmState::first();
    if (!$state) {
        $state = AlarmState::create([
            'is_on' => false,
            'auto_off_duration' => 60
        ]);
    }

    return response()->json([
        'is_on' => (bool) $state->is_on,
        'auto_off_at' => $state->auto_off_at,
        'message' => 'ok'
    ]);
})->name('api.alarm.public');


Route::middleware(['auth', 'role:user', 'nocache', 'check-auto-off'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [UserDashboard::class, 'index'])->name('dashboard');

        // Riwayat Alarm Log
        Route::get('/riwayat', [UserAlarmLog::class, 'index'])->name('riwayat');

        // Profile Routes
        Route::get('/profile', [UserProfile::class, 'index'])->name('profile');
        Route::post('/profile/update-phone', [UserProfile::class, 'updatePhone'])->name('profile.update-phone');

        // ==============================================
        // INCIDENT / REPORT ROUTES
        // ==============================================
        Route::prefix('incidents')->name('incidents.')->group(function () {
            // View routes
            Route::get('/', [UserIncident::class, 'index'])->name('index');
            Route::get('/create', [UserIncident::class, 'create'])->name('create');

            // CRUD operations
            Route::post('/', [UserIncident::class, 'store'])->name('store');
            Route::post('/{id}/resolve', [UserIncident::class, 'resolve'])->name('resolve');
            Route::post('/{id}/false-alarm', [UserIncident::class, 'falseAlarm'])->name('false-alarm');
            Route::delete('/{id}', [UserIncident::class, 'destroy'])->name('destroy');

            // Image management
            Route::delete('/{incident}/images/{imageName}', [UserIncident::class, 'deleteImage'])->name('images.delete');
            Route::get('/{incident}/images/{imageName}', [UserIncident::class, 'downloadImage'])->name('images.download');

            // Auto emergency incident
            Route::post('/auto-emergency', [UserIncident::class, 'autoEmergency'])->name('auto-emergency');
        });

        // ==============================================
        // API ENDPOINTS (Untuk Frontend)
        // ==============================================
        Route::prefix('api')->name('api.')->group(function () {
            // Incident APIs
            Route::get('/incidents/active', [UserIncident::class, 'getActive'])->name('incidents.active');
            Route::get('/can-activate-alarm', [UserIncident::class, 'canActivateAlarm'])->name('can-activate-alarm');

            // Alarm APIs (tambahan untuk frontend)
            Route::get('/alarm/check-before-report', [UserAlarm::class, 'checkBeforeReport'])->name('alarm.check-before-report');
            Route::post('/alarm/mark-report-created', [UserAlarm::class, 'markReportCreated'])->name('alarm.mark-report-created');
        });

        // ==============================================
        // ALARM ROUTES (Server-Side Auto-Off)
        // ==============================================
        Route::prefix('alarm')->name('alarm.')->group(function () {
            // Get current state (polling)
            Route::get('/current-state', [UserAlarm::class, 'getCurrentState'])->name('current-state');

            // Turn ON/OFF
            Route::post('/on', [UserAlarm::class, 'turnOn'])->name('on');
            Route::post('/off', [UserAlarm::class, 'turnOff'])->name('off');

            // Mark report as created
            Route::post('/mark-report-created', [UserAlarm::class, 'markReportCreated'])->name('mark-report-created');

            // Get history
            Route::get('/history', [UserAlarm::class, 'getHistory'])->name('history');

            // Update duration (admin only, but accessible via user)
            Route::post('/update-duration', [UserAlarm::class, 'updateDuration'])->name('update-duration');
        });

        // routes/web.php (tambahkan di dalam group atau di luar)
        Route::post('/api/temperature', function (Illuminate\Http\Request $request) {
            try {
                $suhu = $request->input('suhu');

                if ($suhu === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter suhu tidak ditemukan'
                    ], 400);
                }

                // Simpan ke cache
                Illuminate\Support\Facades\Cache::put('latest_temperature', $suhu, 600);

                \Illuminate\Support\Facades\Log::info('Temperature received', ['suhu' => $suhu]);

                return response()->json([
                    'success' => true,
                    'message' => 'Temperature received',
                    'suhu' => $suhu
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error'
                ], 500);
            }
        });


        // ==============================================
        // VERIFY PIN ROUTE
        // ==============================================
        Route::post('/verify-pin', function (Illuminate\Http\Request $request) {
            try {
                $request->validate([
                    'pin' => 'required|string'
                ]);

                $user = Auth::user();

                if (!$user) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'User tidak ditemukan'
                    ], 401);
                }

                $inputPin = trim($request->pin);
                $dbPin = trim($user->pin ?? '');

                $isValid = $inputPin === $dbPin;

                return response()->json([
                    'valid' => $isValid
                ]);
            } catch (\Exception $e) {
                Log::error('PIN Verification Error:', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        })->name('verify-pin');
    });

/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin', 'nocache', 'check-auto-off'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // MANAJEMEN PENGGUNA
        Route::get('/pengguna', [AdminUser::class, 'index'])->name('pengguna');
        Route::post('/pengguna/store', [AdminUser::class, 'store'])->name('pengguna.store');
        Route::post('/pengguna/update/{id}', [AdminUser::class, 'update'])->name('pengguna.update');  // ← Gunakan POST (sudah ada)
        Route::post('/pengguna/hapus/{id}', [AdminUser::class, 'destroy'])->name('pengguna.destroy');
        Route::get('/pengguna/export', [AdminUser::class, 'export'])->name('pengguna.export');
        Route::post('/pengguna/import', [AdminUser::class, 'import'])->name('pengguna.import');
        Route::get('/template/users', [\App\Http\Controllers\Admin\TemplateController::class, 'downloadUserTemplate'])->name('template.users');

        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/export', [RiwayatController::class, 'export'])->name('riwayat.export');
        Route::post('/riwayat/hapus/{id}', [RiwayatController::class, 'destroy'])->name('riwayat.destroy');
        Route::post('/riwayat/hapus-semua', [RiwayatController::class, 'clearAll'])->name('riwayat.clearAll');

        Route::get('/sirine', function () {
            return view('admin.sirine');
        })->name('sirine');

        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-read');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');

        Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{id}', [IncidentController::class, 'show'])->name('incidents.show');
        Route::put('/incidents/{id}', [IncidentController::class, 'update'])->name('incidents.update');
        Route::get('/incidents/export/csv', [IncidentController::class, 'exportCSV'])->name('incidents.export.csv');
        Route::get('/incidents/export/excel', [IncidentController::class, 'exportCSV'])->name('incidents.export.excel');

        Route::post('/alarm/log', [AdminAlarm::class, 'store'])->name('alarm.log');

        Route::post('/alarm/duration', [AdminAlarm::class, 'updateDuration'])->name('alarm.duration');

        Route::get('/alarm/logs', [AdminAlarm::class, 'getLogs'])->name('alarm.logs');

        Route::post('/alarm/check-auto-off', [AdminAlarm::class, 'checkAutoOff'])->name('alarm.check-auto-off');

        Route::get('/pengaturan', [SettingsController::class, 'index'])->name('pengaturan');

        Route::post('/pengaturan/sistem', [SettingsController::class, 'saveSystemSettings'])->name('pengaturan.sistem');

        Route::post('/pengaturan/aplikasi', [SettingsController::class, 'saveAppSettings'])->name('pengaturan.aplikasi');

        Route::post('/pengaturan/akun', [SettingsController::class, 'updateAccount'])->name('pengaturan.akun');

        Route::get('/pengaturan/data', [SettingsController::class, 'getSettings'])->name('pengaturan.data');
    });
