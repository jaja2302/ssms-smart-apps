<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\DataServiceImportRealisasi;

class RealisasiTaksasiImport implements WithHeadingRow, WithMultipleSheets
{
    /**
     * @param Collection $collection
     */

    public function headingRow(): int
    {
        return 2;
    }

    protected $dataService;
    protected $finaldataTaksasiRealisasiGM;
    protected $month;
    protected $finaldataProdAfdInti;

    public function __construct(DataServiceImportRealisasi $dataService, $month, DataServiceImportRealisasi $finaldataTaksasiRealisasiGM, DataServiceImportRealisasi $finaldataProdAfdInti)
    {
        $this->dataService = $dataService;
        $this->month = $month;
        $this->finaldataTaksasiRealisasiGM = $finaldataTaksasiRealisasiGM;
        $this->finaldataProdAfdInti = $finaldataProdAfdInti;
    }


    public function sheets(): array
    {
        return [
            'TK' => new TenagaKerjaSheetImport($this->dataService, $this->month),
            'TAKSASI VS REALISASI GM' => new RealisasiImport($this->dataService, $this->month, $this->finaldataTaksasiRealisasiGM, $this->finaldataProdAfdInti),
            'PROD-AFD-INTI' => new ProdAfdIntiImport($this->finaldataTaksasiRealisasiGM, $this->month, 'PROD-AFD-INTI', $this->finaldataProdAfdInti),
            'KEHADIRAN PEMANEN' => new KahadiranPemanenImport($this->finaldataProdAfdInti, $this->month, 'KEHADIRAN PEMANEN'),
        ];
    }
}
