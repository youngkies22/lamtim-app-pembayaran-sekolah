<?php

namespace App\Http\Controllers;

use App\Models\LamtimSetting;
use App\Services\PublicDataService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = LamtimSetting::first();

        if (request()->expectsJson()) {
            return ResponseHelper::success($settings);
        }

        return view('settings.index', compact('settings'));
    }

    /**
     * Public settings endpoint (no auth required)
     * Returns only logo and app name for login page
     */
    public function public()
    {
        $settings = LamtimSetting::first();

        $publicData = [
            'logo_aplikasi' => $settings->logo_aplikasi ?? null,
            'nama_aplikasi' => $settings->nama_aplikasi ?? 'Sistem Manajemen SPP',
        ];

        return ResponseHelper::success($publicData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_aplikasi' => 'nullable|string|max:255',
                'logo_aplikasi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // 2MB max
            ]);

            // Handle logo upload
            if ($request->hasFile('logo_aplikasi')) {
                $logo = $request->file('logo_aplikasi');
                $logoPath = $logo->store('settings', 'public');
                $validated['logo_aplikasi'] = $logoPath;
            }

            // Check if settings already exists
            $settings = LamtimSetting::first();
            if ($settings) {
                // Update existing
                if ($request->hasFile('logo_aplikasi') && $settings->logo_aplikasi) {
                    Storage::disk('public')->delete($settings->logo_aplikasi);
                }
                $settings->update($validated);
            } else {
                // Create new
                $settings = LamtimSetting::create($validated);
            }

            // Clear public data cache after update
            $this->publicDataService->clearCache();

            if ($request->expectsJson()) {
                return ResponseHelper::success($settings, 'Settings berhasil disimpan');
            }

            return redirect()->route('settings.index')->with('success', 'Settings berhasil disimpan');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $settings = LamtimSetting::find($id);

        if (!$settings) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Settings tidak ditemukan');
            }
            abort(404);
        }

        if (request()->expectsJson()) {
            return ResponseHelper::success($settings);
        }

        return view('settings.show', compact('settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'nama_aplikasi' => 'nullable|string|max:255',
                'logo_aplikasi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // 2MB max
            ]);

            $settings = LamtimSetting::find($id);
            if (!$settings) {
                return ResponseHelper::notFound('Settings tidak ditemukan');
            }

            // Handle logo upload
            if ($request->hasFile('logo_aplikasi')) {
                // Delete old logo
                if ($settings->logo_aplikasi) {
                    Storage::disk('public')->delete($settings->logo_aplikasi);
                }

                $logo = $request->file('logo_aplikasi');
                $logoPath = $logo->store('settings', 'public');
                $validated['logo_aplikasi'] = $logoPath;
            }

            $settings->update($validated);

            // Clear public data cache after update
            $this->publicDataService->clearCache();

            if ($request->expectsJson()) {
                return ResponseHelper::success($settings, 'Settings berhasil diupdate');
            }

            return redirect()->route('settings.index')->with('success', 'Settings berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $settings = LamtimSetting::find($id);
            if (!$settings) {
                return ResponseHelper::notFound('Settings tidak ditemukan');
            }

            // Delete logo file
            if ($settings->logo_aplikasi) {
                Storage::disk('public')->delete($settings->logo_aplikasi);
            }

            $settings->delete();

            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Settings berhasil dihapus');
            }

            return redirect()->route('settings.index')->with('success', 'Settings berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
