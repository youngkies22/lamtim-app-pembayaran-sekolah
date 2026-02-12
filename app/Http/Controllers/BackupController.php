<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * List all backups
     */
    public function index()
    {
        try {
            $backups = $this->backupService->listBackups();
            return response()->json([
                'success' => true,
                'data' => $backups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list backups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new backup
     */
    public function store()
    {
        // Increase time limit for backup process
        set_time_limit(300); // 5 minutes

        $result = $this->backupService->createBackup();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $path = $this->backupService->getBackupPath($filename);

        if ($path && file_exists($path)) {
            return response()->download($path);
        }

        return response()->json([
            'success' => false,
            'message' => 'Backup file not found'
        ], 404);
    }

    /**
     * Delete a backup file
     */
    public function destroy($filename)
    {
        if ($this->backupService->deleteBackup($filename)) {
            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete backup or file not found'
        ], 500);
    }
}
