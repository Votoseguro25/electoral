<?php

namespace App\Exports;

use App\Models\Votante;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VotantesLiderExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $liderId;
    protected $liderNombre;

    public function __construct($liderId, $liderNombre = null)
    {
        $this->liderId = $liderId;
        $this->liderNombre = $liderNombre;
    }

    public function collection()
{
    $query = Votante::where('lider_id', $this->liderId)
        ->with([
            'persona.municipio',
            'persona.mesa',
            'persona.mesa.puesto', // relación correcta
            'persona.genero',
            'compromiso'
        ]);

    return $query->get()->map(function ($votante) {
        return [
            'ID' => $votante->id,
            'Nombre' => $votante->persona?->nombre,
            'Cédula' => $votante->persona?->cedula,
            'Teléfono' => $votante->persona?->telefono,
            'Profesión' => $votante->persona?->profesion,
            'Municipio' => $votante->persona?->municipio?->nombre,
            'Puesto' => $votante->persona?->mesa?->puesto?->nombre,
            'Mesa' => $votante->persona?->mesa?->descripcion,
            'Género' => $votante->persona?->genero?->nombre,
            'Compromiso' => $votante->compromiso?->nombre,
            
        ];
    });
}


    public function headings(): array
    {
        return ['ID', 'Nombre', 'Cédula', 'Teléfono', 'Profesión', 'Municipio','Puesto', 'Mesa','Género', 'Compromiso'];
    }

    public function title(): string
    {
        return $this->liderNombre ? 'Votantes - ' . substr($this->liderNombre, 0, 25) : 'Votantes del Líder';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
