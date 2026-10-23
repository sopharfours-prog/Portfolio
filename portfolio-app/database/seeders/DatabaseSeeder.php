<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Experience;
use App\Models\Media;
use App\Models\Message;
use App\Models\PageView;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds the portfolio with the content of the original static site
 * (database/seeders/data/portfolio.json) plus demo messages and analytics.
 *
 *   php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    private array $data;

    /** Seed image file name => media path on the public disk */
    private array $media = [];

    public function run(): void
    {
        ActivityLog::$enabled = false;
        $this->data = json_decode(file_get_contents(__DIR__.'/data/portfolio.json'), true);

        $admin = $this->seedUsers();
        $this->seedMedia($admin);
        $this->seedSettings();
        $this->seedSocialLinks();
        $this->seedProjects();
        $this->seedServices();
        $this->seedSkills();
        $this->seedExperience();
        $this->seedTestimonials();
        $this->call(EducationSeeder::class);
        $this->seedMessages();
        $this->seedAnalytics();
        $this->seedActivity($admin);

        ActivityLog::$enabled = true;
    }

    private function seedUsers(): User
    {
        $credentials = [];
        $make = function (string $name, string $email, string $role, ?string $password) use (&$credentials) {
            $password = $password ?: Str::password(16, symbols: false);
            $credentials[] = [$role, $email, $password];

            return User::updateOrCreate(['email' => $email], [
                'name' => $name, 'password' => $password, 'role' => $role, 'email_verified_at' => now(),
            ]);
        };

        $admin = $make(env('ADMIN_NAME', 'Sophar'), env('ADMIN_EMAIL', 'admin@sophar.dev'), User::ROLE_SUPER_ADMIN, env('ADMIN_PASSWORD'));
        $make('Content Editor', 'editor@sophar.dev', User::ROLE_EDITOR, null);

        $this->command?->newLine();
        $this->command?->warn('  Admin accounts (store these passwords; they are not shown again):');
        $this->command?->table(['Role', 'Email', 'Password'], $credentials);

        return $admin;
    }

    private function seedMedia(User $admin): void
    {
        $disk = Storage::disk('public');
        foreach (glob(__DIR__.'/images/*') as $file) {
            $name = basename($file);
            $folder = str_starts_with($name, 'project-') ? 'projects' : 'site';
            $path = "{$folder}/{$name}";
            $disk->put($path, file_get_contents($file));
            [$width, $height] = getimagesize($file) ?: [null, null];

            Media::updateOrCreate(['path' => $path], [
                'name' => pathinfo($name, PATHINFO_FILENAME),
                'original_name' => $name,
                'disk' => 'public',
                'mime_type' => mime_content_type($file),
                'size' => filesize($file),
                'width' => $width,
                'height' => $height,
                'uploaded_by' => $admin->id,
            ]);
            $this->media[$name] = $path;
        }
    }

    private function seedSettings(): void
    {
        $hero = $this->data['hero'];
        $about = $this->data['about'];
        $contact = $this->data['contact'];
        $meta = $this->data['meta'];
        $site = $this->data['site'];
        $both = fn (array $group, string $key) => ['lo' => $group['lo'][$key], 'en' => $group['en'][$key]];

        Setting::put('general', [
            'site_name' => 'SOPHAR',
            'tagline' => ['lo' => 'Portfolio ສ່ວນຕົວ', 'en' => 'Personal Portfolio'],
            'logo' => null,
            'favicon' => null,
            'description' => ['lo' => $meta['lo']['home']['description'], 'en' => $meta['en']['home']['description']],
        ]);

        Setting::put('seo', [
            'meta_title' => ['lo' => $meta['lo']['home']['title'], 'en' => $meta['en']['home']['title']],
            'meta_description' => ['lo' => $meta['lo']['home']['description'], 'en' => $meta['en']['home']['description']],
            'keywords' => 'Sophar, portfolio, web developer, UI/UX designer, Laravel, Vientiane, Laos, ນັກພັດທະນາເວັບ, ອອກແບບເວັບໄຊ',
            'og_image' => $this->media['og-image.jpg'] ?? null,
        ]);

        Setting::put('hero', [
            'label' => $both($hero, 'label'),
            'greeting' => $both($hero, 'greeting'),
            'name' => 'SOPHAR',
            'title' => $both($hero, 'role1'),
            'subtitle' => $both($hero, 'role2'),
            'description' => $both($hero, 'intro'),
            'image' => $this->media['profile.webp'] ?? null,
            'badge' => $both($hero, 'badge'),
            'location' => $both($hero, 'location'),
            'primary_label' => $both($hero, 'ctaWork'),
            'primary_url' => '#projects',
            'secondary_label' => $both($hero, 'ctaContact'),
            'secondary_url' => '#contact',
        ]);

        Setting::put('about', [
            'image' => $this->media['profile.webp'] ?? null,
            'page_title' => ['lo' => 'ກ່ຽວກັບ', 'en' => 'About'],
            'page_title_accent' => ['lo' => 'ຂ້ອຍ', 'en' => 'Me'],
            'subtitle' => ['lo' => 'ນັກພັດທະນາຮອບດ້ານ', 'en' => 'Full-Stack Developer'],
            'cta_label' => ['lo' => 'ຕິດຕໍ່', 'en' => 'Contact Me'],
            'cta_url' => '#contact',
            'headline' => $both($about, 'heading'),
            'introduction' => $both($about, 'intro'),
            'biography' => $both($about, 'personal'),
            'focus_title' => $both($about, 'focusTitle'),
            'focus' => $both($about, 'focus'),
            'philosophy_title' => $both($about, 'philosophyTitle'),
            'philosophy' => $both($about, 'philosophy'),
            'skills_summary' => [
                'lo' => 'ເຕັກໂນໂລຢີ ແລະ ເຄື່ອງມືທີ່ຂ້ອຍເລືອກໃຊ້ ເພື່ອສ້າງຜະລິດຕະພັນທີ່ມີຄຸນນະພາບ.',
                'en' => 'The technologies and tools I rely on to ship high-quality products.',
            ],
            'stats' => collect($site['stats'])->map(fn ($stat) => [
                'value' => $stat['value'],
                'suffix' => $stat['suffix'],
                'label' => $both($about, str_replace('about.', '', $stat['label'])),
            ])->all(),
        ]);

        Setting::put('contact', [
            'email' => $site['email'],
            'phone' => $site['phone'],
            'address' => $site['location'],
            'reply_note' => $both($contact, 'reply'),
        ]);

        Setting::put('appearance', ['default_theme' => 'system']);
        Setting::put('language', ['enabled' => ['lo', 'en'], 'default' => 'lo']);
    }

    private function seedSocialLinks(): void
    {
        $urls = [
            'facebook' => 'https://www.facebook.com/',
            'instagram' => 'https://www.instagram.com/',
            'github' => 'https://github.com/',
            'linkedin' => 'https://www.linkedin.com/',
            'whatsapp' => null,
            'telegram' => null,
        ];
        $order = 0;
        foreach ($urls as $platform => $url) {
            SocialLink::updateOrCreate(['platform' => $platform], ['url' => $url, 'enabled' => (bool) $url, 'sort_order' => $order++]);
        }
    }

    private function seedProjects(): void
    {
        foreach ($this->data['projects'] as $i => $p) {
            $project = Project::create([
                'slug' => $p['slug'],
                'title' => $p['title'],
                'category' => $p['category'],
                'summary' => $p['summary'],
                'description' => $p['description'],
                'challenge' => $p['challenge'],
                'solution' => $p['solution'],
                'features' => $p['features'],
                'role' => $p['role'],
                'technologies' => $p['technologies'],
                'client' => $p['client'],
                'project_date' => "{$p['year']}-".str_pad((string) (12 - $i), 2, '0', STR_PAD_LEFT).'-01',
                'url' => $p['url'] ?: null,
                'github_url' => $p['github'] ?: null,
                'featured' => $i < 6,
                'status' => 'published',
                'sort_order' => $i,
                'views' => random_int(120, 1800),
                'published_at' => now()->subDays(30 * ($i + 1)),
            ]);

            $images = array_merge([$p['image']], $p['gallery']);
            foreach ($images as $order => $image) {
                $file = basename($image);
                $path = $this->media[$file] ?? null;
                if (! $path) {
                    continue;
                }
                [$w, $h] = getimagesize(__DIR__.'/images/'.$file) ?: [null, null];
                $project->images()->create([
                    'path' => $path, 'width' => $w, 'height' => $h, 'is_cover' => $order === 0, 'sort_order' => $order,
                ]);
            }
        }

        // One draft, to show the workflow in the admin
        Project::create([
            'slug' => 'pos-system',
            'title' => 'POS System',
            'category' => 'system',
            'summary' => ['lo' => 'ລະບົບຂາຍໜ້າຮ້ານສຳລັບຮ້ານຄ້າຍ່ອຍ.', 'en' => 'A point-of-sale system for small retailers.'],
            'technologies' => ['Laravel', 'MySQL', 'JavaScript'],
            'status' => 'draft',
            'sort_order' => 99,
        ]);
    }

    private function seedServices(): void
    {
        foreach ($this->data['services'] as $i => $s) {
            Service::create([
                'title' => $s['title'], 'description' => $s['description'], 'features' => $s['items'],
                'icon' => $s['icon'], 'sort_order' => $i, 'status' => 'published',
            ]);
        }
    }

    private function seedSkills(): void
    {
        $category = [
            'Laravel' => 'backend', 'PHP' => 'backend', 'MySQL' => 'database',
            'Git' => 'tools', 'GitHub' => 'tools',
            'UI/UX' => 'design', 'Figma' => 'design', 'Photoshop' => 'design', 'Illustrator' => 'design',
        ];
        foreach ($this->data['skills'] as $i => $s) {
            Skill::create([
                'name' => $s['name'], 'category' => $category[$s['name']] ?? 'frontend', 'icon' => $s['icon'],
                'level' => $s['level'] ?? null, 'sort_order' => $i, 'status' => 'published',
            ]);
        }
    }

    private function seedExperience(): void
    {
        foreach ($this->data['experience'] as $i => $e) {
            preg_match_all('/\d{4}/', $e['period']['en'], $years);
            [$start, $end] = [$years[0][0], $years[0][1] ?? null];
            $current = str_contains($e['period']['en'], 'Present');
            Experience::create([
                'company' => is_array($e['company']) ? $e['company']['en'] : $e['company'],
                'position' => $e['role'],
                'location' => 'Vientiane, Laos',
                'start_date' => "{$start}-01-01",
                'end_date' => $current || ! $end ? null : "{$end}-12-31",
                'is_current' => $current,
                'description' => $e['description'],
                'technologies' => match ($i) {
                    0, 1 => ['Laravel', 'MySQL', 'JavaScript'],
                    2 => ['Laravel', 'Figma', 'Bootstrap'],
                    default => ['Figma', 'Photoshop'],
                },
                'sort_order' => $i,
                'status' => 'published',
            ]);
        }
    }

    private function seedTestimonials(): void
    {
        foreach ($this->data['testimonials'] as $i => $t) {
            Testimonial::create([
                'client_name' => $t['name'], 'position' => $t['position'], 'company' => null,
                'message' => $t['quote'], 'rating' => 5, 'sort_order' => $i, 'status' => 'published',
            ]);
        }
    }

    private function seedMessages(): void
    {
        $samples = [
            ['John Doe', 'john@example.com', 'Web Development Project', 'Hello, I would like to discuss a new company website with online booking. Could we schedule a call next week?', 5, null],
            ['Sarah Lee', 'sarah@example.com', 'UI/UX Design Inquiry', 'We are redesigning our mobile banking app and are looking for a UX designer for a 3-month engagement.', 90, null],
            ['Alex Kim', 'alex@example.com', 'Portfolio Collaboration', 'Loved your Kinetic Design System case study. Would you be open to collaborating on a design system for our SaaS?', 60 * 26, now()->subHours(20)],
            ['ສົມພອນ', 'somphone@example.com', 'ສອບຖາມລາຄາເວັບໄຊ', 'ສະບາຍດີ, ຢາກສອບຖາມລາຄາການເຮັດເວັບໄຊບໍລິສັດ ພ້ອມລະບົບຈັດການຂ່າວ.', 60 * 50, now()->subDays(1)],
            ['Maria Garcia', 'maria@example.com', 'Branding for a coffee shop', 'We are opening a new coffee shop in Vientiane and need a logo and brand identity.', 60 * 24 * 5, null],
        ];
        foreach ($samples as [$name, $email, $subject, $body, $minutesAgo, $readAt]) {
            Message::withoutEvents(fn () => Message::create([
                'name' => $name, 'email' => $email, 'subject' => $subject, 'body' => $body,
                'read_at' => $readAt, 'ip' => '127.0.0.1',
                'created_at' => now()->subMinutes($minutesAgo), 'updated_at' => now()->subMinutes($minutesAgo),
            ]));
        }

        foreach (User::all() as $user) {
            $user->adminNotifications()->createMany([
                ['type' => 'message', 'title' => 'New contact message', 'body' => 'John Doe — Web Development Project', 'url' => '/admin/messages', 'created_at' => now()->subMinutes(5)],
                ['type' => 'project', 'title' => 'Project published', 'body' => 'Lumen Coffee', 'url' => '/admin/projects', 'created_at' => now()->subHours(2)],
                ['type' => 'system', 'title' => 'Welcome to your portfolio admin', 'body' => 'Content from your website has been imported.', 'url' => null, 'created_at' => now()->subDay()],
            ]);
        }
    }

    /** ~13 months of synthetic, first-party page views for the dashboard charts. */
    private function seedAnalytics(): void
    {
        $paths = ['/' => 50, '/projects' => 20, '/about' => 12, '/contact' => 8, '/projects/' => 10];
        $projectIds = Project::published()->pluck('id', 'slug');
        $rows = [];

        for ($day = 400; $day >= 0; $day--) {
            $date = Carbon::today()->subDays($day);
            $growth = 1 + (400 - $day) / 400;              // traffic roughly doubles over the year
            $weekday = $date->isWeekend() ? 0.7 : 1.0;
            $visitors = (int) round(random_int(18, 34) * $growth * $weekday);

            for ($v = 0; $v < $visitors; $v++) {
                $visitor = hash('sha256', $date->toDateString().'-'.$v);
                $pages = random_int(1, 4);
                for ($p = 0; $p < $pages; $p++) {
                    $path = $this->weighted($paths);
                    $projectId = null;
                    if ($path === '/projects/') {
                        $slug = $projectIds->keys()->random();
                        $path .= $slug;
                        $projectId = $projectIds[$slug];
                    }
                    $rows[] = ['path' => $path, 'visitor' => $visitor, 'project_id' => $projectId, 'viewed_on' => $date->toDateString(), 'created_at' => $date->copy()->addMinutes(random_int(0, 1439))];
                }
            }
            if (count($rows) > 2000) {
                PageView::insert($rows);
                $rows = [];
            }
        }
        PageView::insert($rows);
    }

    private function seedActivity(User $admin): void
    {
        ActivityLog::$enabled = true;
        $entries = [
            ['login', null, null, 60 * 24 * 2],
            ['updated', 'Project', 'Lumen Coffee', 60 * 30],
            ['created', 'Service', 'System Development', 60 * 5],
            ['updated', 'Setting', 'Hero section', 90],
            ['login', null, null, 12],
        ];
        foreach ($entries as [$action, $type, $target, $minutesAgo]) {
            DB::table('activity_logs')->insert([
                'user_id' => $admin->id, 'action' => $action, 'subject_type' => $type, 'target' => $target,
                'ip' => '127.0.0.1', 'status' => 'success', 'created_at' => now()->subMinutes($minutesAgo),
            ]);
        }
    }

    private function weighted(array $weights): string
    {
        $pick = random_int(1, array_sum($weights));
        foreach ($weights as $value => $weight) {
            if (($pick -= $weight) <= 0) {
                return $value;
            }
        }

        return array_key_first($weights);
    }
}
