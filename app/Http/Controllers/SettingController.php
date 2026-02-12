<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use App\Http\Requests\SaveSettingRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = $this->service->getSettings();

        if (request()->expectsJson()) {
            return ResponseHelper::success($settings);
        }

        return view('settings.index', compact('settings'));
    }

    /**
     * Public settings endpoint (no auth required).
     */
    public function public()
    {
        return ResponseHelper::success($this->service->getPublicSettings());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveSettingRequest $request)
    {
        try {
            $validated = $request->safe()->only(['nama_aplikasi']);
            $logoFile = $request->file('logo_aplikasi');

            $settings = $this->service->save($validated, $logoFile);

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
        $settings = $this->service->find($id);

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
    public function update(SaveSettingRequest $request, string $id)
    {
        try {
            $validated = $request->safe()->only(['nama_aplikasi']);
            $logoFile = $request->file('logo_aplikasi');

            $settings = $this->service->update($id, $validated, $logoFile);

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
            $this->service->delete($id);

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
