<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $apiKey;
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        if (empty($this->apiKey)) {
            throw new Exception('No se encontró la clave GEMINI_API_KEY en el archivo .env');
        }
    }

    /**
     * Analiza un archivo (PDF o imagen) en memoria usando Gemini
     */
    public function analizarArchivo(UploadedFile $file, string $prompt): array
    {
        if (!$file->isValid()) {
            throw new Exception('El archivo no es válido.');
        }

        // Detectar tipo MIME
        $mime = $file->getMimeType();

        // Solo permitir PDF o imágenes
        if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'])) {
            throw new Exception("Tipo de archivo no soportado: {$mime}");
        }

        // Codificar contenido en Base64
        $fileData = base64_encode(file_get_contents($file->getRealPath()));

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => $mime,
                            'data' => $fileData,
                        ],
                    ],
                ],
            ]],
        ];

        try {
            $response = Http::timeout(300)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint . '?key=' . $this->apiKey, $body);

            if ($response->failed()) {
                throw new Exception("Status: {$response->status()}, Body: {$response->body()}");
            }

            $data = $response->json();

            // Extraer texto
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $json = $text ? json_decode($text, true) : null;

            return [
                'text' => $text ?: 'Sin texto detectado.',
                'json' => $json,
            ];

        } catch (Exception $e) {
            throw new Exception("Error al llamar a Gemini: " . $e->getMessage());
        }
    }
}
