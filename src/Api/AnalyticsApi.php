<?php

namespace Vizor\Laravel\Api;

/**
 * Analytics API methods.
 */
class AnalyticsApi
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * Get analytics overview (total views, unique viewers, avg duration, etc.).
     *
     * @return array<string, mixed>
     */
    public function overview(int $days = 30): array
    {
        return $this->client->get('/api/v1/analytics/overview', ['days' => $days])->json();
    }

    /**
     * Get views over time chart data.
     *
     * @return array<string, mixed>
     */
    public function viewsOverTime(int $days = 30): array
    {
        return $this->client->get('/api/v1/analytics/views', ['days' => $days])->json();
    }

    /**
     * Get top content by views.
     *
     * @return array<string, mixed>
     */
    public function topContent(int $days = 30, int $limit = 10): array
    {
        return $this->client->get('/api/v1/analytics/top-content', [
            'days' => $days,
            'limit' => $limit,
        ])->json();
    }

    /**
     * Get engagement metrics.
     *
     * @return array<string, mixed>
     */
    public function engagement(int $days = 30): array
    {
        return $this->client->get('/api/v1/analytics/engagement', ['days' => $days])->json();
    }

    /**
     * Get summary for a specific content item.
     *
     * @return array<string, mixed>
     */
    public function contentSummary(string $contentId, int $days = 30): array
    {
        return $this->client->get("/api/v1/analytics/content/{$contentId}", ['days' => $days])->json();
    }

    /**
     * Get gaze heatmap data for a content item.
     *
     * @return array<string, mixed>
     */
    public function gazeData(string $contentId, int $days = 30): array
    {
        return $this->client->get("/api/v1/analytics/content/{$contentId}/gaze", ['days' => $days])->json();
    }
}
