<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Persona;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NoVotaronExport implements FromCollection, WithHeadings,WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Persona::where('reporte_voto', 0)
            ->with(['municipio','mesa','genero']);

        

        return $query->get()->map(function ($p) {
            return [
                'ID' => $p->id,
                'Nombre' => $p->nombre,
                'Cédula' => $p->cedula,
                'Teléfono' => $p->telefono,
                'Municipio'=>$p->municipio?->nombre,
                'Género' => $p->genero?->nombre,
                'Mesa' => $p->mesa?->descripcion
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Nombre', 'Cédula','Género', 'Teléfono','Municipio','Mesa',];
    }
       public function title(): string
    {
        return '🔻 No Votaron';  
    }
}
