<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('backend.users.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $users = User::query()
            ->where('role', 'user')
            ->select([
                'id',
                'name',
                'email',
                'phone_number',
                'email_verified_at',
                'phone_verified_at',
                'is_active',
                'created_at',
            ]);

        return DataTables::of($users)
            ->addColumn('email_display', function (User $user): string {
                $verificationBadge = $user->email_verified_at
                    ? '<span class="badge text-bg-success mt-1">Verified</span>'
                    : '<span class="badge text-bg-warning mt-1">Unverified</span>';

                return '<div class="d-flex flex-column">'
                    . '<span>'.e($user->email).'</span>'
                    . $verificationBadge
                    . '</div>';
            })
            ->addColumn('phone_display', function (User $user): string {
                $verificationBadge = $user->phone_verified_at
                    ? '<span class="badge text-bg-success mt-1">Verified</span>'
                    : '<span class="badge text-bg-warning mt-1">Unverified</span>';

                return '<div class="d-flex flex-column">'
                    . '<span>'.e((string) $user->phone_number).'</span>'
                    . $verificationBadge
                    . '</div>';
            })
            ->editColumn('created_at', function (User $user) {
                return $user->created_at ? $user->created_at->format('Y-m-d') : '';
            })
            ->addColumn('status_badge', function (User $user): string {
                return $user->is_active
                    ? '<span class="badge text-bg-success">Active</span>'
                    : '<span class="badge text-bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function (User $user): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-user" data-id="'.$user->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-user" data-id="'.$user->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if ($k === '' || $k === '^') {
                    return;
                }
                if (str_contains($k, 'inactive')) {
                    $query->where('is_active', false);

                    return;
                }
                if (str_contains($k, 'active')) {
                    $query->where('is_active', true);
                }
            })
            ->rawColumns(['email_display', 'phone_display', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(User $user): JsonResponse
    {
        abort_if($user->role !== 'user', 404);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'is_active' => (bool) $user->is_active,
            ],
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if($user->role !== 'user', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['required', 'digits_between:10,15'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone_number' => $validated['phone_number'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);
        $user->save();

        return response()->json([
            'message' => 'User updated successfully.',
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        abort_if($user->role !== 'user', 404);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
