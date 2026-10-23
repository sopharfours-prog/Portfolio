<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesInput;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    use NormalizesInput;

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('title', ''))]);
    }

    public function rules(): array
    {
        // Admin routes bind {project}; API routes pass {id} (slug or numeric id)
        $project = $this->route('project');
        if (! $project && ($id = $this->route('id'))) {
            $project = Project::where('slug', $id)->orWhere('id', ctype_digit((string) $id) ? $id : 0)->first();
        }

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'alpha_dash', 'max:160', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'category' => ['required', Rule::in(Project::CATEGORIES)],
            ...self::bilingualRules('summary', true, 300),
            ...self::bilingualRules('description', false, 5000),
            ...self::bilingualRules('challenge', false, 3000),
            ...self::bilingualRules('solution', false, 3000),
            ...self::bilingualRules('role', false, 150),
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable'],
            'technologies' => ['nullable'],
            'client' => ['nullable', 'string', 'max:150'],
            'project_date' => ['nullable', 'date'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
            'github_url' => ['nullable', 'url:http,https', 'max:255'],
            'featured' => ['boolean'],
            'status' => ['required', Rule::in(Project::STATUSES)],
        ];
    }

    /** "Save draft" / "Publish" buttons send `action`; the status select is used otherwise. */
    public function validationData(): array
    {
        $data = parent::validationData();
        $data['status'] = match ($this->input('action')) {
            'draft' => 'draft',
            'publish' => 'published',
            default => $this->input('status', 'draft'),
        };
        $data['featured'] = $this->boolean('featured');

        return $data;
    }

    public function payload(): array
    {
        $data = $this->validated();

        return [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'category' => $data['category'],
            'summary' => $this->bilingual('summary'),
            'description' => $this->bilingual('description'),
            'challenge' => $this->bilingual('challenge'),
            'solution' => $this->bilingual('solution'),
            'role' => $this->bilingual('role'),
            'features' => $this->bilingualLines('features'),
            'technologies' => $this->tags('technologies'),
            'client' => $data['client'] ?? null,
            'project_date' => $data['project_date'] ?? null,
            'url' => $data['url'] ?? null,
            'github_url' => $data['github_url'] ?? null,
            'featured' => $data['featured'],
            'status' => $data['status'],
        ];
    }
}
