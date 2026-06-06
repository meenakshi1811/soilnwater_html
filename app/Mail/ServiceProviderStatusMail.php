<?php

namespace App\Mail;

use App\Models\ServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceProviderStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{company_name: string, contact_person: ?string, email: ?string, status: string, service_provider_url: ?string}  $service_providerDetails
     */
    public function __construct(
        public array $service_providerDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forServiceProvider(ServiceProvider $service_provider, string $action): self
    {
        $service_provider->loadMissing('user');

        $companyName = $service_provider->company_name ?: $service_provider->user?->name ?: 'Service';
        $status = match ($action) {
            'pending' => 'Under observation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($action),
        };

        return new self(
            service_providerDetails: [
                'company_name' => $companyName,
                'contact_person' => $service_provider->contact_person ?: $service_provider->user?->name,
                'email' => $service_provider->email ?: $service_provider->user?->email,
                'status' => $status,
                'service_provider_url' => $action === 'approved' ? $service_provider->serviceProviderUrl() : null,
            ],
            action: $action,
            subjectLine: match ($action) {
                'pending' => 'Welcome to SoilNWater - Your Service Profile Is Under Observation',
                'approved' => 'Your Service Account Has Been Approved',
                'rejected' => 'Service Application Update',
                'deleted' => 'Service Account Removed',
                default => 'Service Account Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.service_provider.status');
    }
}
