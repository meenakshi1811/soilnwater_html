<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\ModulePermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleAccessController extends Controller
{
    public function show(Request $request, string $module): View
    {
        abort_unless(array_key_exists($module, ModulePermissions::modules()), 404);

        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canModule($module, 'read')) {
            abort(403, 'You do not have permission to view this module.');
        }

        $allowedActions = [];
        foreach (ModulePermissions::ACTIONS as $action) {
            if ($user->isAdmin() || $user->canModule($module, $action)) {
                $allowedActions[] = $action;
            }
        }

        return view('backend.modules.show', [
            'module' => $module,
            'title' => ModulePermissions::modules()[$module],
            'allowedActions' => $allowedActions,
            'isEmployee' => $user instanceof Employee,
        ]);
    }
}
