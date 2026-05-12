<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use Illuminate\View\View;

class ContactSupportController extends Controller
{
    public function index(): View
    {
        $requests = ContactSupport::query()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(20);

        return view('backend.admin.contact-support.index', compact('requests'));
    }
}
