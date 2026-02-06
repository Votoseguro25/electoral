<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PendientesVotarExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Cedula',
            'Telefono',
            'Municipio',
            'Mesa',
            'Estado',
            'Genero',
        ];
    }

    public function map($persona): array
    {
        return [
            $persona->nombre,
            $persona->cedula,
            $persona->telefono ?? '',
            $persona->municipio->nombre ?? '',
            $persona->mesa->descripcion ?? '',
            $persona->estadoVotante->nombre ?? 'Sin estado',
            $persona->genero->nombre ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'dc3545']
                ],
            ],
        ];
    }
}
