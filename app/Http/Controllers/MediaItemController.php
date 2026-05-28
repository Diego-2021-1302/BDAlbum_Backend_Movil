<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MediaItemController extends Controller
{
    // GET /api/media
public function index(Request $request)
{
    $query = MediaItem::query();

    // filtro etiqueta
    if ($request->has('tag') && $request->tag !== '') {
        $query->where('tag', $request->tag);
    }

    // filtro fechas
    if ($request->has('year') && $request->year !== '') {
        $query->whereYear('taken_at', $request->year);
    }


    // filtro por texto libre en descripción
    if ($request->has('q') && $request->q !== '') {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('description', 'like', "%{$q}%");
        });
    }

    $items = $query->orderBy('taken_at', 'desc')
                   ->orderBy('id', 'desc')
                   ->get();

    return response()->json($items);
}

    // POST /api/media
    public function store(Request $request)
    {
        // validación
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:4194304'], // ~4 GB
            'taken_at' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'tag' => ['required', Rule::in(['B','D','BD'])],
        ]);

        // detectar tipo por mimetype
        $mime = $request->file('file')->getMimeType();
        $type = str_starts_with($mime, 'image/')
            ? 'image'
            : (str_starts_with($mime, 'video/') ? 'video' : null);

        if (!$type) {
            return response()->json([
                'error' => 'El archivo debe ser imagen o video válido.'
            ], 422);
        }

        // guardar archivo en storage/app/public/media
        $path = $request->file('file')->store('media', 'public');

        $media = MediaItem::create([
            'file_path'   => 'storage/'.$path, // para servirlo fácil
            'type'        => $type,
            'taken_at'    => $validated['taken_at'],
            'description' => $validated['description'] ?? null,
            'tag'         => $validated['tag'],
        ]);

        return response()->json($media, 201);
    }

    // DELETE /api/media/{id}
    public function destroy(string $id)
    {
        $media = MediaItem::findOrFail($id);

        // borrar archivo físico también
        // file_path es tipo "storage/media/abc.jpg"
        // necesitamos convertirlo a "media/abc.jpg" para Storage
        $publicPrefix = 'storage/';
        if (str_starts_with($media->file_path, $publicPrefix)) {
            $relative = substr($media->file_path, strlen($publicPrefix));
            Storage::disk('public')->delete($relative);
        }

        $media->delete();

        return response()->json(['ok' => true]);
    }
    public function update(Request $request, string $id)
{
    $media = MediaItem::findOrFail($id);

    // validación: el archivo NO es obligatorio en edición
    $validated = $request->validate([
        'file'        => ['nullable', 'file', 'max:51200'],
        'taken_at'    => ['required', 'date'],
        'description' => ['nullable', 'string'],
        'tag'         => ['required', Rule::in(['B','D','BD'])],
    ]);

    // si subieron archivo nuevo, reemplazar físico+path+type
    if ($request->hasFile('file')) {
        // borrar viejo
        $publicPrefix = 'storage/';
        if (str_starts_with($media->file_path, $publicPrefix)) {
            $relative = substr($media->file_path, strlen($publicPrefix));
            Storage::disk('public')->delete($relative);
        }

        $mime = $request->file('file')->getMimeType();
        $type = str_starts_with($mime, 'image/')
            ? 'image'
            : (str_starts_with($mime, 'video/') ? 'video' : null);

        if (!$type) {
            return response()->json([
                'error' => 'El archivo debe ser imagen o video válido.'
            ], 422);
        }

        $path = $request->file('file')->store('media', 'public');

        $media->file_path = 'storage/'.$path;
        $media->type = $type;
    }

    $media->taken_at    = $validated['taken_at'];
    $media->description = $validated['description'] ?? null;
    $media->tag         = $validated['tag'];
    $media->save();

    return response()->json($media);
}

}
