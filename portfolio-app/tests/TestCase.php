<?php

namespace Tests;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function admin(string $role = User::ROLE_SUPER_ADMIN): User
    {
        return User::create([
            'name' => 'Test '.$role, 'email' => $role.uniqid().'@example.com',
            'password' => 'Secret-Pass-123', 'role' => $role, 'locale' => 'en',
        ]);
    }

    /** Minimum website settings so the public pages render. */
    protected function seedSite(): void
    {
        Setting::put('general', ['site_name' => 'SOPHAR']);
        Setting::put('seo', ['meta_title' => ['lo' => 'ຫົວຂໍ້', 'en' => 'Title'], 'meta_description' => ['lo' => 'ຄຳອະທິບາຍ', 'en' => 'Description']]);
        Setting::put('hero', ['name' => 'SOPHAR', 'greeting' => ['lo' => 'ສະບາຍດີ', 'en' => 'Hello'], 'title' => ['lo' => 'ນັກພັດທະນາ', 'en' => 'Developer'], 'description' => ['lo' => 'ແນະນຳ', 'en' => 'Intro']]);
        Setting::put('about', ['headline' => ['lo' => 'ກ່ຽວກັບ', 'en' => 'About'], 'introduction' => ['lo' => 'ບົດແນະນຳ', 'en' => 'Introduction']]);
        Setting::put('contact', ['email' => 'hello@example.com']);
        Setting::put('language', ['enabled' => ['lo', 'en'], 'default' => 'lo']);
    }

    protected function projectData(array $overrides = []): array
    {
        return [
            'title' => 'Test Project', 'slug' => '', 'category' => 'web',
            'summary' => ['lo' => 'ສະຫຼຸບ', 'en' => 'Summary'],
            'features' => ['lo' => "ໜຶ່ງ\nສອງ", 'en' => "One\nTwo"],
            'technologies' => 'Laravel, MySQL', 'status' => 'draft', 'action' => 'publish',
            ...$overrides,
        ];
    }
}
