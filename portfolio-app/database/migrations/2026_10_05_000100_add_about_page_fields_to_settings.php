<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the About page hero fields (title, highlighted word, subtitle, button)
 * to the existing "about" setting. Existing values are never overwritten.
 */
return new class extends Migration
{
    public const DEFAULTS = [
        'page_title' => ['lo' => 'ກ່ຽວກັບ', 'en' => 'About'],
        'page_title_accent' => ['lo' => 'ຂ້ອຍ', 'en' => 'Me'],
        'subtitle' => ['lo' => 'ນັກພັດທະນາຮອບດ້ານ', 'en' => 'Full-Stack Developer'],
        'cta_label' => ['lo' => 'ຕິດຕໍ່', 'en' => 'Contact Me'],
        'cta_url' => '#contact',
    ];

    public function up(): void
    {
        $about = Setting::where('key', 'about')->value('value');
        if ($about === null) {
            return; // fresh install: the seeder writes the full setting
        }
        $about = is_string($about) ? json_decode($about, true) : $about;

        Setting::put('about', [...self::DEFAULTS, ...array_filter($about, fn ($v) => $v !== null)]);
    }

    public function down(): void
    {
        $about = Setting::get('about');
        if (is_array($about)) {
            Setting::put('about', array_diff_key($about, self::DEFAULTS));
        }
    }
};
