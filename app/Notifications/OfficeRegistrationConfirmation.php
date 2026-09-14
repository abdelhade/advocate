<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Support\TenantUrl;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class OfficeRegistrationConfirmation extends BaseVerifyEmail
{
    public function __construct(public ?Tenant $tenant = null)
    {
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $officeName = $this->tenant?->name ?? 'مكتبك';
        $officeUrl = $this->tenant
            ? TenantUrl::for($this->tenant, '/dashboard')
            : config('app.url');
        $phone = $notifiable->phone ?? '—';

        return (new MailMessage)
            ->subject('تأكيد تسجيل مكتبك في جلسات')
            ->greeting('مرحباً '.$notifiable->name.'،')
            ->line('تم إنشاء مكتبك بنجاح على منصة جلسات.')
            ->line('**اسم المكتب:** '.$officeName)
            ->line('**رقم الهاتف:** '.$phone)
            ->line('**رابط لوحة التحكم:** '.$officeUrl)
            ->line('يرجى تأكيد بريدك الإلكتروني بالضغط على الزر أدناه لإكمال التفعيل:')
            ->action('تأكيد البريد الإلكتروني', $verificationUrl)
            ->line('رابط التأكيد صالح لمدة '.Config::get('auth.verification.expire', 60).' دقيقة.')
            ->line('إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة.')
            ->salutation('مع تحيات فريق جلسات');
    }

    protected function verificationUrl($notifiable): string
    {
        $tenant = $this->tenant ?? $notifiable->tenants()->first();
        $parameters = [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        if ($tenant) {
            $parameters['tenant_slug'] = $tenant->slug;
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            $parameters
        );
    }
}
