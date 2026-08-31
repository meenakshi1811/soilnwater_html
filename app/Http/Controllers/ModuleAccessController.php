<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\ModulePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ModuleAccessController extends Controller
{
    public function show(Request $request, string $module): View|RedirectResponse
    {
        abort_unless(array_key_exists($module, ModulePermissions::modules()), 404);

        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canModule($module, 'read')) {
            abort(403, 'You do not have permission to view this module.');
        }

        $entryRoute = ModulePermissions::entryRouteName($module);
        if ($entryRoute && Route::has($entryRoute)) {
            return redirect()->route($entryRoute);
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
