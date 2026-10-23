<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonColorsTest extends TestCase
{
    use RefreshDatabase;

    private function saveColors(array $colors, bool $reset = false)
    {
        return $this->put('/admin/settings', ['tab' => 'appearance', 'default_theme' => 'system', 'colors' => $colors, 'reset_colors' => $reset ? 1 : 0]);
    }

    public function test_default_colours_add_no_override_to_the_website(): void
    {
        $this->seedSite();

        $this->get('/')->assertOk()->assertDontSee('--accent:', false);
        $this->actingAs($this->admin())->get('/admin/settings?tab=appearance')->assertOk()->assertSee('value="#00afc4"', false);
    }

    public function test_chosen_colours_are_saved_and_applied_to_the_website(): void
    {
        $this->seedSite();
        $this->actingAs($this->admin());

        $this->saveColors(['primary' => '#E11D48', 'secondary' => '#4f6bff', 'tertiary' => '#f59e0b'])->assertSessionHasNoErrors();

        $this->assertEquals(['primary' => '#e11d48', 'secondary' => '#4f6bff', 'tertiary' => '#f59e0b'], Setting::get('appearance.colors'));
        $this->get('/')->assertSee(':root { --accent: #e11d48; --accent-3: #f59e0b; }', false);   // only the changed ones
    }

    public function test_invalid_colours_are_rejected_and_cannot_inject_css(): void
    {
        $this->seedSite();
        $this->actingAs($this->admin());

        $this->saveColors(['primary' => 'red; } body { display: none'])->assertSessionHasErrors('colors.primary');
        $this->saveColors(['primary' => '#12345'])->assertSessionHasErrors('colors.primary');
        $this->get('/')->assertDontSee('display: none', false);

        // A bad value stored some other way is ignored, falling back to the default
        Setting::put('appearance', ['default_theme' => 'system', 'colors' => ['primary' => '</style><script>x</script>']]);
        $this->get('/')->assertDontSee('<script>x</script>', false)->assertDontSee('--accent:', false);
    }

    public function test_reset_returns_to_the_defaults_and_editors_cannot_change_colours(): void
    {
        $this->seedSite();
        $this->actingAs($this->admin());
        $this->saveColors(['primary' => '#e11d48', 'secondary' => '#4f6bff', 'tertiary' => '#9b5cff']);

        $this->saveColors(['primary' => '#e11d48'], reset: true)->assertSessionHasNoErrors();
        $this->assertSame([], Setting::get('appearance.colors'));
        $this->get('/')->assertDontSee('--accent:', false);

        $this->actingAs($this->admin(User::ROLE_EDITOR));
        $this->saveColors(['primary' => '#000000'])->assertForbidden();
    }
}
