<?php

namespace SantosDave\Paystack\Resources;

class BulkCharge extends BaseResource
{
    protected string $basePath = 'bulkcharge';

    /**
     * Initiate bulk charge.
     *
     * @param array $data Bulk charge data
     * @return array
     */
    public function initiate(array $data): array
    {
        $this->validateRequired($data, ['body']);

        // Convert amounts to subunits for each charge
        if (isset($data['body']) && is_array($data['body'])) {
            foreach ($data['body'] as $key => $charge) {
                if (isset($charge['amount'])) {
                    $data['body'][$key]['amount'] = $this->toSubunit(
                        $charge['amount'],
                        $charge['currency'] ?? null
                    );
                }
            }
        }

        return $this->client->post($this->buildPath(), $data);
    }

    /**
     * List all bulk charge batches.
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
     * Fetch bulk charge batch.
     *
     * @param string $idOrCode Batch ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Fetch charges in a batch.
     *
     * @param string $idOrCode Batch ID or code
     * @param array $params Query parameters
     * @return array
     */
    public function fetchCharges(string $idOrCode, array $params = []): array
    {
        $allowed = ['status', 'perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath("{$idOrCode}/charges"), $query);
    }

    /**
     * Pause bulk charge batch.
     *
     * @param string $batchCode Batch code
     * @return array
     */
    public function pause(string $batchCode): array
    {
        return $this->client->get($this->buildPath("pause/{$batchCode}"));
    }

    /**
     * Resume bulk charge batch.
     *
     * @param string $batchCode Batch code
     * @return array
     */
    public function resume(string $batchCode): array
    {
        return $this->client->get($this->buildPath("resume/{$batchCode}"));
    }
}
