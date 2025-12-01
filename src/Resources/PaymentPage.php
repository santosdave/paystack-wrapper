<?php

namespace SantosDave\Paystack\Resources;

class PaymentPage extends BaseResource
{
    protected string $basePath = 'page';

    /**
     * Create a payment page.
     *
     * @param array $data Page data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['name']);

        // Convert amount to subunits if provided
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'name',
            'description',
            'amount',
            'slug',
            'metadata',
            'redirect_url',
            'custom_fields'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * List all payment pages.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a payment page.
     *
     * @param string $idOrSlug Page ID or slug
     * @return array
     */
    public function fetch(string $idOrSlug): array
    {
        return $this->client->get($this->buildPath($idOrSlug));
    }

    /**
     * Update a payment page.
     *
     * @param string $idOrSlug Page ID or slug
     * @param array $data Update data
     * @return array
     */
    public function update(string $idOrSlug, array $data): array
    {
        // Convert amount to subunits if provided
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'name',
            'description',
            'amount',
            'active',
            'metadata',
            'redirect_url',
            'custom_fields'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath($idOrSlug), $params);
    }

    /**
     * Check slug availability.
     *
     * @param string $slug Slug to check
     * @return array
     */
    public function checkSlugAvailability(string $slug): array
    {
        return $this->client->get($this->buildPath("check_slug_availability/{$slug}"));
    }

    /**
     * Add products to a payment page.
     *
     * @param string $id Page ID
     * @param array $products Product IDs
     * @return array
     */
    public function addProducts(string $id, array $products): array
    {
        return $this->client->post($this->buildPath("{$id}/product"), [
            'product' => $products
        ]);
    }
}
