<?php

namespace App\Exports;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Puesto;
use App\Models\Mesa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoberturaTerritorioExport implements WithMultipleSheets
{
    protected $tipo;

    public function __construct($tipo = 'completo')
    {
        $this->tipo = $tipo;
    }

    public function sheets(): array
    {
        $sheets = [];

        switch ($this->tipo) {
            case 'resumen':
                $sheets[] = new ResumenCoberturaSheet();
                break;
            case 'detallado':
                $sheets[] = new DepartamentosCoberturaSheet();
                $sheets[] = new MunicipiosCoberturaSheet();
                break;
            case 'critico':
                $sheets[] = new ZonasCriticasSheet();
                break;
            default:
                $sheets[] = new ResumenCoberturaSheet();
                $sheets[] = new DepartamentosCoberturaSheet();
                $sheets[] = new MunicipiosCoberturaSheet();
                $sheets[] = new ZonasCriticasSheet();
        }

        return $sheets;
    }
}

class ResumenCoberturaSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        $data = collect();

        // Estadisticas generales
        $totalDepartamentos = Departamento::count();
        $totalMunicipios = Municipio::count();
        $totalPuestos = Puesto::count();
        $totalMesas = Mesa::count();

        $mesasConTestigos = 0;
        if (Schema::hasTable('testigos')) {
            $mesasConTestigos = Mesa::whereHas('testigos')->count();
        }

        $coberturaTestigos = $totalMesas > 0 ? round(($mesasConTestigos / $totalMesas) * 100, 1) : 0;

        $data->push(['Metrica', 'Valor', 'Porcentaje']);
        $data->push(['Total Departamentos', $totalDepartamentos, '-']);
        $data->push(['Total Municipios', $totalMunicipios, '-']);
        $data->push(['Total Puestos', $totalPuestos, '-']);
        $data->push(['Total Mesas', $totalMesas, '-']);
        $data->push(['Mesas con Testigos', $mesasConTestigos, $coberturaTestigos . '%']);
        $data->push(['Mesas sin Testigos', $totalMesas - $mesasConTestigos, (100 - $coberturaTestigos) . '%']);

        return $data;
    }

    public function headings(): array
    {
        return ['Resumen de Cobertura Territorial', '', ''];
    }

    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '20c997']
                ],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ],
            ],
        ];
    }
}

class DepartamentosCoberturaSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Departamento::with('municipios')
            ->get()
            ->map(function ($depto) {
                $totalMunicipios = $depto->municipios->count();
                $totalPuestos = Puesto::whereIn('municipio_id', $depto->municipios->pluck('id'))->count();
                $totalMesas = Mesa::whereHas('puesto', function ($q) use ($depto) {
                    $q->whereIn('municipio_id', $depto->municipios->pluck('id'));
                })->count();

                $mesasConTestigos = 0;
                if (Schema::hasTable('testigos')) {
                    $mesasConTestigos = Mesa::whereHas('puesto', function ($q) use ($depto) {
                        $q->whereIn('municipio_id', $depto->municipios->pluck('id'));
                    })->whereHas('testigos')->count();
                }

                $cobertura = $totalMesas > 0 ? round(($mesasConTestigos / $totalMesas) * 100, 1) : 0;
                $nivelCobertura = $this->getNivelCobertura($cobertura);

                return [
                    'departamento' => $depto->nombre,
                    'municipios' => $totalMunicipios,
                    'puestos' => $totalPuestos,
                    'mesas' => $totalMesas,
                    'mesas_con_testigos' => $mesasConTestigos,
                    'mesas_sin_testigos' => $totalMesas - $mesasConTestigos,
                    'cobertura' => $cobertura . '%',
                    'nivel' => $nivelCobertura,
                ];
            });
    }

    private function getNivelCobertura($indice)
    {
        if ($indice >= 80) return 'Optima';
        if ($indice >= 60) return 'Buena';
        if ($indice >= 40) return 'Regular';
        if ($indice >= 20) return 'Baja';
        return 'Critica';
    }

    public function headings(): array
    {
        return [
            'Departamento',
            'Municipios',
            'Puestos',
            'Mesas',
            'Con Testigos',
            'Sin Testigos',
            'Cobertura',
            'Nivel',
        ];
    }

    public function title(): string
    {
        return 'Departamentos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '17a2b8']
                ],
            ],
        ];
    }
}

class MunicipiosCoberturaSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        return Municipio::with(['departamento'])
            ->get()
            ->map(function ($municipio) {
                $totalPuestos = Puesto::where('municipio_id', $municipio->id)->count();
                $totalMesas = Mesa::whereHas('puesto', function ($q) use ($municipio) {
                    $q->where('municipio_id', $municipio->id);
                })->count();

                $mesasConTestigos = 0;
                if (Schema::hasTable('testigos')) {
                    $mesasConTestigos = Mesa::whereHas('puesto', function ($q) use ($municipio) {
                        $q->where('municipio_id', $municipio->id);
                    })->whereHas('testigos')->count();
                }

                $cobertura = $totalMesas > 0 ? round(($mesasConTestigos / $totalMesas) * 100, 1) : 0;
                $nivelCobertura = $this->getNivelCobertura($cobertura);

                return [
                    'departamento' => $municipio->departamento->nombre ?? '',
                    'municipio' => $municipio->nombre,
                    'puestos' => $totalPuestos,
                    'mesas' => $totalMesas,
                    'mesas_con_testigos' => $mesasConTestigos,
                    'mesas_sin_testigos' => $totalMesas - $mesasConTestigos,
                    'cobertura' => $cobertura . '%',
                    'nivel' => $nivelCobertura,
                ];
            })
            ->sortByDesc('cobertura');
    }

    private function getNivelCobertura($indice)
    {
        if ($indice >= 80) return 'Optima';
        if ($indice >= 60) return 'Buena';
        if ($indice >= 40) return 'Regular';
        if ($indice >= 20) return 'Baja';
        return 'Critica';
    }

    public function headings(): array
    {
        return [
            'Departamento',
            'Municipio',
            'Puestos',
            'Mesas',
            'Con Testigos',
            'Sin Testigos',
            'Cobertura',
            'Nivel',
        ];
    }

    public function title(): string
    {
        return 'Municipios';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6f42c1']
                ],
            ],
        ];
    }
}

class ZonasCriticasSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function collection()
    {
        $zonasCriticas = collect();

        // Municipios con cobertura critica (menos del 30%)
        Municipio::with(['departamento'])
            ->get()
            ->each(function ($municipio) use ($zonasCriticas) {
                $totalMesas = Mesa::whereHas('puesto', function ($q) use ($municipio) {
                    $q->where('municipio_id', $municipio->id);
                })->count();

                if ($totalMesas == 0) return;

                $mesasConTestigos = 0;
                if (Schema::hasTable('testigos')) {
                    $mesasConTestigos = Mesa::whereHas('puesto', function ($q) use ($municipio) {
                        $q->where('municipio_id', $municipio->id);
                    })->whereHas('testigos')->count();
                }

                $cobertura = round(($mesasConTestigos / $totalMesas) * 100, 1);

                if ($cobertura < 30) {
                    $zonasCriticas->push([
                        'tipo' => 'Cobertura Critica',
                        'departamento' => $municipio->departamento->nombre ?? '',
                        'municipio' => $municipio->nombre,
                        'detalle' => "Solo {$mesasConTestigos} de {$totalMesas} mesas tienen testigo",
                        'cobertura' => $cobertura . '%',
                        'prioridad' => 'ALTA',
                    ]);
                }
            });

        // Puestos sin mesas
        Puesto::whereDoesntHave('mesas')
            ->with('municipio.departamento')
            ->get()
            ->each(function ($puesto) use ($zonasCriticas) {
                $zonasCriticas->push([
                    'tipo' => 'Puesto sin Mesas',
                    'departamento' => $puesto->municipio->departamento->nombre ?? '',
                    'municipio' => $puesto->municipio->nombre ?? '',
                    'detalle' => "Puesto: {$puesto->nombre}",
                    'cobertura' => '0%',
                    'prioridad' => 'MEDIA',
                ]);
            });

        return $zonasCriticas->sortBy('prioridad');
    }

    public function headings(): array
    {
        return [
            'Tipo de Alerta',
            'Departamento',
            'Municipio',
            'Detalle',
            'Cobertura',
            'Prioridad',
        ];
    }

    public function title(): string
    {
        return 'Zonas Criticas';
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
