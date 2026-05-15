<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Link;
use App\Services\LinkService;
use App\Support\Exceptions\HttpException;
use App\Support\Exceptions\ValidationException;
use App\Support\Http\JsonResponder;
use App\Validators\LinkValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LinkController
{
    public function __construct(
        private readonly LinkService $links,
        private readonly LinkValidator $validator,
    ) {
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        try {
            $dto = $this->validator->validateCreate($body);
            $link = $this->links->create($dto, $user);
        } catch (ValidationException $e) {
            return JsonResponder::error($e->getMessage(), 422, $e->errors);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::created(
            data: $this->present($link),
            location: $this->shortUrl($link->short_code),
        );
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(100, max(5, (int) ($params['per_page'] ?? 20)));

        $query = Link::query()->where('user_id', $user->id)->latest();

        if (! empty($params['search'])) {
            $term = '%' . trim((string) $params['search']) . '%';
            $query->where(static function ($q) use ($term): void {
                $q->where('title', 'like', $term)
                    ->orWhere('original_url', 'like', $term)
                    ->orWhere('short_code', 'like', $term);
            });
        }

        $total = (clone $query)->count();
        $links = $query->forPage($page, $perPage)->get();

        return JsonResponder::success(
            $links->map(fn (Link $link) => $this->present($link))->all(),
            meta: [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        );
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');

        try {
            $link = $this->links->findByCode((string) $args['code']);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        if ($link->user_id !== $user->id) {
            return JsonResponder::error('Bu linki görmək üçün icazəniz yoxdur.', 403);
        }

        return JsonResponder::success($this->present($link));
    }

    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');

        try {
            $link = $this->links->findByCode((string) $args['code']);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        if ($link->user_id !== $user->id) {
            return JsonResponder::error('Bu linki silmək üçün icazəniz yoxdur.', 403);
        }

        $link->delete();

        return JsonResponder::noContent();
    }

    private function present(Link $link): array
    {
        return [
            'uuid'           => $link->uuid,
            'short_code'     => $link->short_code,
            'short_url'      => $this->shortUrl($link->short_code),
            'original_url'   => $link->original_url,
            'title'          => $link->title,
            'click_count'    => $link->click_count,
            'unique_clicks'  => $link->unique_click_count,
            'has_password'   => $link->requiresPassword(),
            'expires_at'     => $link->expires_at?->toIso8601String(),
            'max_clicks'     => $link->max_clicks,
            'is_active'      => $link->is_active,
            'is_flagged'     => $link->is_flagged,
            'created_at'     => $link->created_at?->toIso8601String(),
        ];
    }

    private function shortUrl(string $code): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/');

        return $base . '/' . $code;
    }
}
