<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shared search + filter + sort + pagination for admin index pages and the API.
 *
 *   Listing::for(Project::query(), $request)
 *       ->search(['title', 'client'])
 *       ->filter('status', Project::STATUSES)
 *       ->sorts(['newest' => ['created_at', 'desc'], ...], 'newest')
 *       ->paginate();
 */
class Listing
{
    private array $sorts = [];

    private string $defaultSort = '';

    public function __construct(private Builder $query, private Request $request) {}

    public static function for(Builder $query, Request $request): self
    {
        return new self($query, $request);
    }

    /** Case-insensitive LIKE across columns. JSON (bilingual) columns are searched as text. */
    public function search(array $columns): self
    {
        $term = trim((string) $this->request->query('search', ''));
        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $this->query->where(function (Builder $q) use ($columns, $like) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', $like);
                }
            });
        }

        return $this;
    }

    /** Filter on an exact column value, only when the value is allowed. */
    public function filter(string $param, array $allowed, ?string $column = null): self
    {
        $value = $this->request->query($param);
        if (is_string($value) && in_array($value, $allowed, true)) {
            $this->query->where($column ?? $param, $value);
        }

        return $this;
    }

    /** @param array<string, array{0: string, 1: string}> $sorts key => [column, direction] */
    public function sorts(array $sorts, string $default): self
    {
        $this->sorts = $sorts;
        $this->defaultSort = $default;

        return $this;
    }

    public function currentSort(): string
    {
        $sort = (string) $this->request->query('sort', $this->defaultSort);

        return array_key_exists($sort, $this->sorts) ? $sort : $this->defaultSort;
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        if ($this->sorts) {
            [$column, $direction] = $this->sorts[$this->currentSort()];
            $this->query->orderBy($column, $direction)->orderBy('id', $direction);
        }
        $perPage = (int) min(max((int) $this->request->query('per_page', $perPage), 5), 100);

        return $this->query->paginate($perPage)->withQueryString();
    }
}
