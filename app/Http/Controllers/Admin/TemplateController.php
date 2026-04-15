<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class TemplateController extends Controller
{
    /**
     * Download sample CSV template for user import
     */
    public function downloadUserTemplate()
    {
        $filename = 'template_import_users.csv';

        // Create CSV content
        $csv = chr(0xEF).chr(0xBB).chr(0xBF); // UTF-8 BOM
        
        // Header dengan kolom baru
        $csv .= implode(';', [
            'ID',
            'UNIQUE_ID',
            'Nama',
            'Email',
            'No. Telepon',
            'Role',
            'Status',
            'PIN'
        ]) . "\n";

        // Sample data rows
        $csv .= implode(';', [
            '1',
            '01',
            'Admin Pertama',
            'admin@example.com',
            '081234567890',
            'ADMIN',
            'Aktif',
            '123456'
        ]) . "\n";

        $csv .= implode(';', [
            '2',
            '02',
            'User Pertama',
            'user@example.com',
            '082345678901',
            'USER',
            'Aktif',
            '789012'
        ]) . "\n";

        $csv .= implode(';', [
            '3',
            '03',
            'Guru Pertama',
            'guru@example.com',
            '083456789012',
            'GURU',
            'Aktif',
            '345678'
        ]) . "\n";

        $csv .= implode(';', [
            '',
            '',
            'John Doe',
            'john@example.com',
            '084567890123',
            'USER',
            'Aktif',
            ''
        ]) . "\n";

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}