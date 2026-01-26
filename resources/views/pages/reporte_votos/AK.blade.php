@extends('layouts.bootstrap')

@section('titulo', 'Análisis de Archivos con Gemini')

@section('contenido')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="card-title text-center text-primary mb-4">Madara AI</h1>

            <form id="geminiForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Selecciona el archivo (PDF o Imagen):</label>
                    <input type="file" name="file" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Prompt:</label>
                    <textarea name="prompt" rows="3" class="form-control" required>
Extrae toda la información de este documento de manera detallada. Devuélvela en texto plano y formato JSON.
                    </textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Procesar Documento</button>
            </form>

            <div id="resultado" class="mt-4"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.getElementById('geminiForm').addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const resultadoDiv = document.getElementById('resultado');

    resultadoDiv.innerHTML = '<div class="text-center text-info">Procesando documento... ⏳</div>';

    try {
        const res = await axios.post('{{ route('gemini.process') }}', formData, {
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });

        const data = res.data;
        const texto = data.text || 'Sin texto detectado.';
        const json = data.json ? JSON.stringify(data.json, null, 2) : null;

        resultadoDiv.innerHTML = `
            <h5 class="mt-3">🧾 Texto extraído</h5>
            <pre class="bg-light border p-3 rounded">${texto}</pre>
            ${json ? `
            <h5 class="mt-3">📊 JSON estructurado</h5>
            <pre class="bg-light border p-3 rounded">${json}</pre>
            ` : ''}
        `;

    } catch (err) {
        console.error(err);
        let msg = 'Error desconocido';
        if (err.response && err.response.data) {
            msg = JSON.stringify(err.response.data, null, 2);
        } else if (err.message) {
            msg = err.message;
        }
        resultadoDiv.innerHTML = `<div class="text-danger"><pre>${msg}</pre></div>`;
    }
});
</script>
@endsection
