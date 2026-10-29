<?php

namespace App\Services;

use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Virtual-number offers: one virtual__products row for each service × country × provider where both the service
 * and the country have that provider's code ("empty" or blank = not offered). The price checks
 * (Api\VirtualNumberController, run by cron) fill in the prices and put a row on sale; a new row stays off sale
 * (status 0) until its first price arrives. Rebuilding never touches prices or sale status.
 * Used by the old admin's «بروز رسانی» buttons and by the React admin's «بازسازی پیشنهادها».
 */
class VirtualNumberOffers
{
    /** Providers that sell numbers, with the server number the storefront shows. "land" has codes but no offers. */
    public const PROVIDERS = ['pva' => 1, 'man' => 2, 'active' => 3];

    /** Every provider column on services and countries, with its admin label. */
    public const LABELS = ['pva' => 'SMS-PVA', 'man' => 'SMS-Man', 'active' => 'SMS-Activate', 'land' => 'Land'];

    public static function offered($code): bool
    {
        $code = trim((string) $code);
        return $code !== '' && $code !== 'empty';
    }

    /** @return array{created: int, updated: int} */
    public function rebuildService(Virtual_Service $service): array
    {
        return $this->rebuild(collect([$service]), Virtual_Country::orderBy('id')->get());
    }

    /** @return array{created: int, updated: int} */
    public function rebuildCountry(Virtual_Country $country): array
    {
        return $this->rebuild(Virtual_Service::orderBy('id')->get(), collect([$country]));
    }

    private function rebuild(Collection $services, Collection $countries): array
    {
        $existing = Virtual_Products::whereIn('service_id', $services->pluck('id'))
            ->whereIn('country_id', $countries->pluck('id'))
            ->orderBy('id')->get()
            ->groupBy(fn (Virtual_Products $p) => "$p->service_id:$p->country_id:$p->type")
            ->map->first();
        $created = $updated = 0;

        DB::transaction(function () use ($services, $countries, $existing, &$created, &$updated) {
            foreach ($services as $service) {
                foreach ($countries as $country) {
                    foreach (self::PROVIDERS as $type => $server) {
                        if (!self::offered($service->$type) || !self::offered($country->$type)) {
                            continue;
                        }
                        $row = $existing["$service->id:$country->id:$type"] ?? null;
                        $isNew = is_null($row);
                        $row ??= (new Virtual_Products())->forceFill(['commission_percent' => 10]);
                        // Same names and paths the old admin wrote ("… -1"), so unchanged rows stay unchanged.
                        $row->forceFill([
                            'service_id' => $service->id,
                            'service_code' => trim((string) $service->$type),
                            'country_id' => $country->id,
                            'country_code' => trim((string) $country->$type),
                            'faName' => str_replace('شماره مجازی', '', (string) $service->faName) . ' -' . $server,
                            'enName' => $service->enName,
                            'serviceImage' => str_replace('http://localhost:8000/', '', (string) $service->image),
                            'countryName' => str_replace('شماره مجازی', '', (string) $country->faName) . ' -' . $server,
                            'countryFaName' => $country->enName,
                            'countryImage' => 'image/svg/' . strtolower((string) $country->code) . '.svg',
                            'country_sort' => $country->sort,
                            'type' => $type,
                        ]);
                        if ($row->isDirty()) {
                            $row->save();
                            $isNew ? $created++ : $updated++;
                        }
                    }
                }
            }
        });

        return ['created' => $created, 'updated' => $updated];
    }
}
