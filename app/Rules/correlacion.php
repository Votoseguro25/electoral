<?php

namespace App\Rules;

use App\Models\Mesa;
use Closure;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class correlacion implements ValidationRule
{
    protected $tabla;
    protected $llaveForanea;
    protected $valorForaneo;
    protected $mensajeError;

    public function __construct(string $tabla, string $llaveForanea, mixed $valorForaneo, string $mensajeError = null)
    {
        $this->tabla = $tabla;
        $this->llaveForanea = $llaveForanea;
        $this->valorForaneo = $valorForaneo;
        $this->mensajeError = $mensajeError;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $id, Closure $fail): void
    {
        $exists = DB::table($this->tabla)
            ->where('id', $id)
            ->where($this->llaveForanea, $this->valorForaneo)
            ->exists();

        if (!$exists) {
            $fail($this->mensajeError ?: "El campo $attribute no es válido.");
        }
    }
}
