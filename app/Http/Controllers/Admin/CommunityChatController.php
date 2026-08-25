<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityChatController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.community-chats.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $topics = DiscussionTopic::query()
            ->with(['user:id,name,full_name', 'parent:id,title'])
            ->withCount(['members', 'children'])
            ->withMax('replies', 'created_at')
            ->latest();

        return DataTables::of($topics)
            ->addColumn('chat_title', function (DiscussionTopic $topic): string {
                $title = e($topic->title);
                $parent = $topic->parent?->title;

                if ($parent) {
                    return '<div class="fw-semibold">'.$title.'</div><small class="text-muted">In group: '.e($parent).'</small>';
                }

                return '<div class="fw-semibold">'.$title.'</div>';
            })
            ->addColumn('chat_type', function (DiscussionTopic $topic): string {
                if ($topic->isGroupContainer()) {
                    return '<span class="badge text-bg-info">Group</span>';
                }

                if ($topic->parent_topic_id) {
                    return '<span class="badge text-bg-secondary">Group topic</span>';
                }

                return '<span class="badge text-bg-primary">Public chat</span>';
            })
            ->addColumn('author_name', fn (DiscussionTopic $topic): string => e($topic->user?->authorDisplayName() ?? '—'))
            ->addColumn('messages_count', function (DiscussionTopic $topic): string {
                if ($topic->isGroupContainer()) {
                    return e((string) $topic->children_count).' topics';
                }

                $count = (int) $topic->replies_count + ($topic->body ? 1 : 0);

                return e((string) $count);
            })
            ->addColumn('last_activity', function (DiscussionTopic $topic): string {
                $last = $topic->replies_max_created_at
                    ? $topic->replies_max_created_at
                    : $topic->updated_at;

                return $last ? Carbon::parse($last)->format('d M Y H:i') : '—';
            })
            ->addColumn('actions', function (DiscussionTopic $topic): string {
                return '<a href="'.route('admin.community-chats.show', $topic).'" class="btn btn-sm btn-outline-primary">'
                    .'<i class="fa-solid fa-comments me-1"></i> View chat</a>';
            })
            ->filterColumn('chat_title', function ($query, $keyword): void {
                $query->where('title', 'like', '%'.$keyword.'%');
            })
            ->filterColumn('author_name', function ($query, $keyword): void {
                $query->whereHas('user', function ($userQuery) use ($keyword): void {
                    $userQuery->where(function ($inner) use ($keyword): void {
                        $inner->where('name', 'like', '%'.$keyword.'%')
                            ->orWhere('full_name', 'like', '%'.$keyword.'%');
                    });
                });
            })
            ->filterColumn('chat_type', function ($query, $keyword): void {
                $k = strtolower(trim((string) $keyword));

                if ($k === '' || $k === '^') {
                    return;
                }

                if (str_contains($k, 'group topic')) {
                    $query->whereNotNull('parent_topic_id');

                    return;
                }

                if (str_contains($k, 'group')) {
                    $query->where('is_group', true)->whereNull('parent_topic_id');

                    return;
                }

                if (str_contains($k, 'public')) {
                    $query->where('is_group', false)->whereNull('parent_topic_id');
                }
            })
            ->rawColumns(['chat_title', 'chat_type', 'actions'])
            ->make(true);
    }

    public function show(DiscussionTopic $topic): View
    {
        $topic->load([
            'user',
            'parent.user',
            'members',
            'replies.user',
            'children.user',
        ]);
        $topic->loadCount(['members', 'children']);

        $participants = $this->participantsFor($topic);

        return view('backend.admin.community-chats.show', [
            'topic' => $topic,
            'participants' => $participants,
        ]);
    }

    public function users(): View
    {
        return view('backend.admin.community-chats.users');
    }

    public function usersData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $users = User::query()
            ->select(['id', 'name', 'full_name', 'email', 'role', 'is_chat_blocked', 'created_at'])
            ->latest();

        return DataTables::of($users)
            ->addColumn('name_display', function (User $user): string {
                return '<div class="fw-semibold">'.e($user->authorDisplayName()).'</div>'
                    .'<small class="text-muted">ID #'.e((string) $user->id).'</small>';
            })
            ->addColumn('role_badge', function (User $user): string {
                $labels = [
                    'user' => ['General User', 'text-bg-primary'],
                    'vendor' => ['Vendor', 'text-bg-info'],
                    'consultant' => ['Consultant', 'text-bg-secondary'],
                    'service_provider' => ['Service Provider', 'text-bg-secondary'],
                    'admin' => ['Admin', 'text-bg-danger'],
                    'employee' => ['Employee', 'text-bg-dark'],
                ];
                [$label, $class] = $labels[$user->role] ?? [Str::headline((string) $user->role), 'text-bg-secondary'];

                return '<span class="badge '.$class.'">'.e($label).'</span>';
            })
            ->addColumn('chat_status', function (User $user): string {
                return $user->is_chat_blocked
                    ? '<span class="badge text-bg-danger"><i class="fa-solid fa-ban me-1"></i>Blocked</span>'
                    : '<span class="badge text-bg-success">Allowed</span>';
            })
            ->addColumn('chat_toggle', function (User $user): string {
                if ($user->isAdmin()) {
                    return '<span class="text-muted small">Admins cannot be blocked</span>';
                }

                $checked = $user->is_chat_blocked ? 'checked' : '';
                $title = $user->is_chat_blocked ? 'Unblock chat access' : 'Block chat access';

                return '<div class="form-check form-switch m-0 d-flex justify-content-center" title="'.e($title).'">'
                    .'<input class="form-check-input js-toggle-chat-block" type="checkbox" role="switch" data-id="'.$user->id.'" '.$checked.'>'
                    .'</div>';
            })
            ->editColumn('created_at', fn (User $user): string => $user->created_at?->format('d M Y') ?? '—')
            ->filterColumn('name_display', function ($query, $keyword): void {
                $query->where(function ($inner) use ($keyword): void {
                    $inner->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('full_name', 'like', '%'.$keyword.'%');
                });
            })
            ->filterColumn('chat_status', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);

                if (str_contains($k, 'block')) {
                    $query->where('is_chat_blocked', true);

                    return;
                }

                if (str_contains($k, 'allow')) {
                    $query->where('is_chat_blocked', false);
                }
            })
            ->rawColumns(['name_display', 'role_badge', 'chat_status', 'chat_toggle'])
            ->make(true);
    }

    public function toggleUserBlock(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin accounts cannot be blocked from chat.',
            ], 422);
        }

        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot block your own chat access.',
            ], 422);
        }

        $user->is_chat_blocked = ! $user->is_chat_blocked;
        $user->save();

        return response()->json([
            'message' => $user->is_chat_blocked
                ? 'User has been blocked from community chat.'
                : 'User can access community chat again.',
            'is_chat_blocked' => $user->is_chat_blocked,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function participantsFor(DiscussionTopic $topic)
    {
        if ($topic->isGroupContainer() || ($topic->parent_topic_id && $topic->parent?->isGroupContainer())) {
            $group = $topic->isGroupContainer() ? $topic : $topic->parent;
            $group?->loadMissing('members');

            return collect([$group?->user])
                ->merge($group?->members ?? [])
                ->filter()
                ->unique('id')
                ->values();
        }

        $replyAuthorIds = $topic->replies
            ->pluck('user_id')
            ->push($topic->user_id)
            ->unique()
            ->filter()
            ->all();

        return User::query()
            ->whereIn('id', $replyAuthorIds)
            ->orderBy('name')
            ->get();
    }
}
