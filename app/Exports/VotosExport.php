<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VotosExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
{
    return [
        new VotaronExport($this->filters),
        new NoVotaronExport($this->filters),
    ];
}

}
