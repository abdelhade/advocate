<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Store a newly uploaded document in private storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Document::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'case_id' => ['nullable', 'exists:legal_cases,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,docx,doc,png,jpg,jpeg'],
        ]);

        $tenantId = app(TenantContext::class)->id();
        $file = $request->file('file');
        $filePath = $file->store("private/tenants/{$tenantId}/documents");

        Document::create([
            'tenant_id' => $tenantId,
            'case_id' => $validated['case_id'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'title' => $validated['title'],
            'file_path' => $filePath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploader_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم رفع المستند بنجاح حماية كاملة.');
    }

    /**
     * Download document securely after authorization & audit logging.
     */
    public function download(Document $document): StreamedResponse
    {
        Gate::authorize('download', $document);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'الملف غير موجود في التخزين الخاص.');
        }

        // Log sensitive download action
        AuditLog::create([
            'tenant_id' => $document->tenant_id,
            'user_id' => Auth::id(),
            'action' => 'downloaded',
            'entity_type' => Document::class,
            'entity_id' => (string) $document->id,
            'old_values' => null,
            'new_values' => ['title' => $document->title, 'file_path' => $document->file_path],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return Storage::download($document->file_path, $document->title);
    }
}
