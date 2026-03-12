<?php

namespace App\Services;

use App\Repository\RekapNteRepository;

class RekapNteService
{
    protected RekapNteRepository $rekapNteRepository;

    public function __construct(RekapNteRepository $rekapNteRepository)
    {
        $this->rekapNteRepository = $rekapNteRepository;
    }

    private function formatJumlah($angka)
    {
        $floatVal = (float) $angka;
        return ($floatVal == (int)$floatVal) ? (int)$floatVal : $floatVal;
    }

    public function getRekapitulasi(array $filters): array
    {
        $periode = $filters['periode'] ?? now()->year;
        $type = $filters['type'];
        $search = $filters['search'] ?? null;

        $query = $this->rekapNteRepository->getBaseQuery($type, $periode);
        $query = $this->rekapNteRepository->applySearch($query, $search, $type);
        $query->having('total_nte', '>', 0);

        $grandTotalNte = $this->rekapNteRepository->getGrandTotal($periode);
        $paginator = $query->orderBy('total_nte', 'desc')->paginate(20)->withQueryString();

        $collectionToTransform = $paginator->getCollection();
        $collectionToTransform->transform(function ($item) use ($periode, $type) {
            if ($type === 'komoditas') {
                $item->produksi = $this->formatJumlah($item->jumlah_produksi);
            } else {
                $produksiData = $this->rekapNteRepository->getProduksiDetail($type, $periode, $item);
                $item->produksi = $produksiData->map(function ($prod) {
                    $prod->jumlah = $this->formatJumlah($prod->jumlah);
                    return $prod;
                });
            }
            return $item;
        });

        return [
            'paginator'   => $paginator,
            'grand_total' => $grandTotalNte
        ];
    }
}