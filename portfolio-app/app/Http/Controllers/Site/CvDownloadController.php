<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\CvFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Public "Download CV": the PDF in the visitor's language (or the other one if only that exists). */
class CvDownloadController extends Controller
{
    public function __invoke(?string $locale = null): StreamedResponse
    {
        $locale = array_key_exists((string) $locale, locales()) ? $locale : app()->getLocale();
        abort_unless(CvFile::isPublic() && ($file = CvFile::forLocale($locale)), 404);

        return CvFile::download($file, $locale);
    }
}
