<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\CvFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Upload, replace, download and remove the CV (one PDF per language). Super admin only. */
class CvController extends Controller
{
    public function edit(): View
    {
        return view('admin.cv.edit', [
            'files' => collect(locales())->mapWithKeys(fn ($label, $code) => [$code => CvFile::get($code)])->all(),
            'showOnSite' => CvFile::isPublic(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $maxMb = round(CvFile::maxBytes() / 1024 / 1024, 1);
        $rules = ['show_on_site' => ['boolean']];
        $messages = [];
        foreach (array_keys(locales()) as $code) {
            $rules["file_{$code}"] = ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.intdiv(CvFile::maxBytes(), 1024)];
            // PHP drops files over its upload limit before Laravel sees them; say why instead of "failed to upload"
            $messages["file_{$code}.uploaded"] = __('This file is too large. The maximum is :size MB.', ['size' => $maxMb]);
            $messages["file_{$code}.max"] = __('This file is too large. The maximum is :size MB.', ['size' => $maxMb]);
        }
        $request->validate($rules, $messages, collect(locales())->mapWithKeys(fn ($label, $code) => ["file_{$code}" => __('CV').' ('.$label.')'])->all());

        $uploaded = [];
        foreach (array_keys(locales()) as $code) {
            if ($request->hasFile("file_{$code}")) {
                CvFile::store($code, $request->file("file_{$code}"));
                $uploaded[] = strtoupper($code);
            }
        }
        CvFile::setPublic($request->boolean('show_on_site'));
        ActivityLog::record('updated', null, ['uploaded' => $uploaded], target: __('CV'));

        return back()->with('toast', ['success', __(':item updated successfully', ['item' => __('CV')])]);
    }

    public function download(string $locale): StreamedResponse
    {
        abort_unless(array_key_exists($locale, locales()) && ($file = CvFile::get($locale)), 404);

        return CvFile::download($file, $locale);
    }

    public function destroy(string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, locales()), 404);
        CvFile::remove($locale);
        ActivityLog::record('deleted', null, ['locale' => $locale], target: __('CV').' ('.strtoupper($locale).')');

        return back()->with('toast', ['success', __('CV removed')]);
    }
}
