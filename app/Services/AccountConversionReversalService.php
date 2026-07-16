<?php

namespace App\Services;

use App\Models\Consultant;
use App\Models\ServiceProvider;
use App\Models\Vendor;
use App\Support\ConsultantFileUploader;
use App\Support\ServiceProviderFileUploader;
use App\Support\VendorFileUploader;
use Illuminate\Support\Facades\DB;

class AccountConversionReversalService
{
    public static function shouldRevertOnRejection(Vendor|Consultant|ServiceProvider $profile, bool $wasNeverApproved): bool
    {
        return (bool) $profile->converted_from_user && $wasNeverApproved;
    }

    public static function revertVendorOnRejection(Vendor $vendor, bool $wasNeverApproved): bool
    {
        if (! self::shouldRevertOnRejection($vendor, $wasNeverApproved)) {
            return false;
        }

        DB::transaction(function () use ($vendor): void {
            $user = $vendor->user;
            self::deleteVendorProfile($vendor);
            $user?->forceFill(['role' => 'user'])->save();
        });

        return true;
    }

    public static function revertConsultantOnRejection(Consultant $consultant, bool $wasNeverApproved): bool
    {
        if (! self::shouldRevertOnRejection($consultant, $wasNeverApproved)) {
            return false;
        }

        DB::transaction(function () use ($consultant): void {
            $user = $consultant->user;
            self::deleteConsultantProfile($consultant);
            $user?->forceFill(['role' => 'user'])->save();
        });

        return true;
    }

    public static function revertServiceProviderOnRejection(ServiceProvider $serviceProvider, bool $wasNeverApproved): bool
    {
        if (! self::shouldRevertOnRejection($serviceProvider, $wasNeverApproved)) {
            return false;
        }

        DB::transaction(function () use ($serviceProvider): void {
            $user = $serviceProvider->user;
            self::deleteServiceProviderProfile($serviceProvider);
            $user?->forceFill(['role' => 'user'])->save();
        });

        return true;
    }

    private static function deleteVendorProfile(Vendor $vendor): void
    {
        $vendor->loadMissing(['bannerSlides', 'pageSections']);

        foreach ($vendor->bannerSlides as $slide) {
            VendorFileUploader::deleteIfExists($slide->image_path);
        }

        foreach ($vendor->pageSections as $section) {
            VendorFileUploader::deleteIfExists($section->image_path);
        }

        VendorFileUploader::deleteIfExists($vendor->logo);

        if (is_array($vendor->gallery)) {
            foreach ($vendor->gallery as $path) {
                VendorFileUploader::deleteIfExists($path);
            }
        }

        $vendor->delete();
    }

    private static function deleteConsultantProfile(Consultant $consultant): void
    {
        $consultant->loadMissing(['bannerSlides', 'pageSections']);

        foreach ($consultant->bannerSlides as $slide) {
            ConsultantFileUploader::deleteIfExists($slide->image_path);
        }

        foreach ($consultant->pageSections as $section) {
            ConsultantFileUploader::deleteIfExists($section->image_path);
        }

        ConsultantFileUploader::deleteIfExists($consultant->logo);

        if (is_array($consultant->gallery)) {
            foreach ($consultant->gallery as $path) {
                ConsultantFileUploader::deleteIfExists($path);
            }
        }

        $consultant->delete();
    }

    private static function deleteServiceProviderProfile(ServiceProvider $serviceProvider): void
    {
        $serviceProvider->loadMissing(['bannerSlides', 'pageSections']);

        foreach ($serviceProvider->bannerSlides as $slide) {
            ServiceProviderFileUploader::deleteIfExists($slide->image_path);
        }

        foreach ($serviceProvider->pageSections as $section) {
            ServiceProviderFileUploader::deleteIfExists($section->image_path);
        }

        ServiceProviderFileUploader::deleteIfExists($serviceProvider->logo);

        if (is_array($serviceProvider->gallery)) {
            foreach ($serviceProvider->gallery as $path) {
                ServiceProviderFileUploader::deleteIfExists($path);
            }
        }

        $serviceProvider->delete();
    }
}
