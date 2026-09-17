<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LegalCase;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseAttachmentController extends Controller
{
    /**
     * Upload one or more attachments to a case.
     */
    public function store(Request $request, LegalCase $case): RedirectResponse
    {
        Gate::authorize('update', $case);

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,gif'],
        ], [
            'files.required' => 'يرجى اختيار ملف واحد على الأقل.',
            'files.max' => 'لا يمكن رفع أكثر من 10 ملفات في المرة الواحدة.',
            'files.*.max' => 'حجم الملف يجب ألا يتجاوز 50 ميغابايت.',
            'files.*.mimes' => 'نوع الملف غير مدعوم. الأنواع المسموحة: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, WEBP, GIF.',
        ]);

        $tenantId = app(TenantContext::class)->id();

        foreach ($request->file('files') as $file) {
            $case->addMedia($file)
                ->withCustomProperties([
                    'uploader_id' => Auth::id(),
                    'uploader_name' => Auth::user()->name,
                    'tenant_id' => $tenantId,
                ])
                ->toMediaCollection('attachments');
        }

        $count = count($request->file('files'));
        $message = $count === 1
            ? 'تم رفع المرفق بنجاح.'
            : "تم رفع {$count} مرفقات بنجاح.";

        return back()->with('success', $message);
    }

    /**
     * Download an attachment securely with audit logging.
     */
    public function download(LegalCase $case, Media $media): StreamedResponse
    {
        Gate::authorize('view', $case);

        // Ensure media belongs to this case
        if ($media->model_id !== $case->id || $media->model_type !== LegalCase::class) {
            abort(403, 'هذا المرفق لا ينتمي لهذه القضية.');
        }

        // Log download action
        AuditLog::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'user_id' => Auth::id(),
            'action' => 'downloaded_attachment',
            'entity_type' => LegalCase::class,
            'entity_id' => (string) $case->id,
            'old_values' => null,
            'new_values' => [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return response()->streamDownload(function () use ($media) {
            echo file_get_contents($media->getPath());
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    /**
     * Delete an attachment from a case.
     */
    public function destroy(LegalCase $case, Media $media): RedirectResponse
    {
        Gate::authorize('update', $case);

        // Ensure media belongs to this case
        if ($media->model_id !== $case->id || $media->model_type !== LegalCase::class) {
            abort(403, 'هذا المرفق لا ينتمي لهذه القضية.');
        }

        $media->delete();

        return back()->with('success', 'تم حذف المرفق بنجاح.');
    }
}
