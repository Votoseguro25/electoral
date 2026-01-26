<?php

namespace App\Rules;

use Closure;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class liderDiferente implements ValidationRule
{
    protected $personaId;

    public function __construct($personaId)
    {
        $this->personaId = $personaId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $liderId, Closure $fail): void
    {
        if (!$liderId) {
            return; // Si no hay líder seleccionado, no hay nada que validar
        }

        $personaIdDB = DB::table('lideres')->where('id', $liderId)->value('persona_id');

        if ($personaIdDB == $this->personaId) {
            $fail('El líder no se puede escoger a sí mismo.');
        }
    }
}
