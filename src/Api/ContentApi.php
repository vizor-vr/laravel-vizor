<?php

namespace Vizor\Laravel\Api;

/**
 * Content management API methods.
 */
class ContentApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * List all content items.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function list(?string $search = null, int $limit = 25, int $offset = 0, array $filters = []): array
    {
        $query = array_filter([
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ]);

        return $this->client->get('/api/v1/content', $query)->json();
    }

    /**
     * Get a single content item by ID.
     *
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->client->get("/api/v1/content/{$id}")->json();
    }

    /**
     * Create a new content item.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(string $title, string $format, array $data = []): array
    {
        return $this->client->post('/api/v1/content', [
            'title' => $title,
            'format' => $format,
            ...$data,
        ])->json();
    }

    /**
     * Update a content item.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(string $id, array $data): array
    {
        return $this->client->patch("/api/v1/content/{$id}", $data)->json();
    }

    /**
     * Delete a content item.
     *
     * @return array<string, mixed>
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/api/v1/content/{$id}")->json();
    }
}
