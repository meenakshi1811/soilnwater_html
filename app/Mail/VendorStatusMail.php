<?php

namespace App\Mail;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{company_name: string, contact_person: ?string, email: ?string, status: string, store_url: ?string, reason: ?string}  $vendorDetails
     */
    public function __construct(
        public array $vendorDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forVendor(Vendor $vendor, string $action, ?string $reason = null): self
    {
        $vendor->loadMissing('user');

        $companyName = $vendor->company_name ?: $vendor->user?->name ?: 'Vendor';
        $status = match ($action) {
            'pending' => 'Under observation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($action),
        };

        return new self(
            vendorDetails: [
                'company_name' => $companyName,
                'contact_person' => $vendor->contact_person ?: $vendor->user?->name,
                'email' => $vendor->email ?: $vendor->user?->email,
                'status' => $status,
                'store_url' => $action === 'approved' ? $vendor->storeUrl() : null,
                'reason' => filled($reason) ? trim($reason) : null,
            ],
            action: $action,
            subjectLine: match ($action) {
                'pending' => 'Welcome to SoilNWater - Your Vendor Profile Is Under Observation',
                'approved' => 'Your Vendor Account Has Been Approved',
                'rejected' => 'Vendor Application Update',
                'deleted' => 'Vendor Account Removed',
                default => 'Vendor Account Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.vendor.status');
    }
}
