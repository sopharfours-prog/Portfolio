<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Listing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD for small, manually ordered collections (services, skills, experience, testimonials).
 * Subclasses describe the model, views, search columns and filters; the flow is shared.
 */
abstract class SortableResourceController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    /** View folder under admin/, also used for route names */
    protected string $name;

    protected array $searchColumns = [];

    /** filter param => allowed values */
    protected function filters(): array
    {
        return ['status' => $this->model::STATUSES];
    }

    protected function sorts(): array
    {
        return ['order' => ['sort_order', 'asc'], 'newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc']];
    }

    abstract protected function label(): string;

    public function index(Request $request): View
    {
        $listing = Listing::for($this->model::query(), $request)->search($this->searchColumns)->sorts($this->sorts(), 'order');
        foreach ($this->filters() as $param => $allowed) {
            $listing->filter($param, $allowed);
        }
        $items = $listing->paginate(25);

        return view("admin.{$this->name}.index", [
            'items' => $items,
            'sort' => $listing->currentSort(),
            // Drag & drop only makes sense on the unfiltered list in manual order
            'canReorder' => $listing->currentSort() === 'order' && ! $request->filled(['search']) && collect($this->filters())->keys()->every(fn ($p) => ! $request->filled($p)),
        ]);
    }

    public function create(): View
    {
        return view("admin.{$this->name}.form", ['item' => new $this->model($this->defaults())]);
    }

    public function edit(int $id): View
    {
        return view("admin.{$this->name}.form", ['item' => $this->model::findOrFail($id)]);
    }

    protected function defaults(): array
    {
        return ['status' => 'published', 'sort_order' => (int) $this->model::max('sort_order') + 1];
    }

    protected function saved(Model $item, string $message): RedirectResponse
    {
        return redirect()->route("admin.{$this->name}.index")->with('toast', ['success', $message]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->model::findOrFail($id)->delete();

        return redirect()->route("admin.{$this->name}.index")->with('toast', ['success', __(':item deleted', ['item' => $this->label()])]);
    }

    protected function persist(FormRequest $request, ?Model $item = null): Model
    {
        $data = $request->payload();
        if ($item) {
            $item->update($data);

            return $item;
        }

        return $this->model::create($data);
    }
}
