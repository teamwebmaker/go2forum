<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Conversation;
use App\Models\PhoneVerificationOtp;
use App\Models\Settings;
use App\Models\Topic;
use App\Models\User;
use App\Support\BadgeColors;
use App\Services\AccountDeletionService;
use App\Services\FileUploadService;
use App\Services\ImageUploadService;
use App\Services\PasswordUpdateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected array $fileFields = [
        'image' => User::AVATAR_DIR,
    ];

    public function profile(): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('profile.badges');
        }

        return redirect()->route('profile.user-info');
    }

    public function profileBadges(): View
    {
        $user = Auth::user();

        return view('profile.badges', [
            'user' => $user,
            'data' => config('badges.examples'),
            'badgeColor' => BadgeColors::forUser($user),
        ]);
    }

    public function profileMessages(Request $request): View
    {
        $initialConversationId = $request->integer('conversation') ?: null;

        return view('profile.messages', [
            'initialConversationId' => $initialConversationId,
        ]);
    }

    public function profileActivity(): View
    {
        $user = Auth::user();
        $topicsPerPage = 5;
        $conversationsPerPage = 5;

        $topics = Topic::query()
            ->where('user_id', $user->id)
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->paginate($topicsPerPage, ['*'], 'topics_page')
            ->withQueryString();

        $conversations = Conversation::query()
            ->select([
                'id',
                'kind',
                'topic_id',
                'direct_user1_id',
                'direct_user2_id',
                'last_message_at',
                'updated_at',
            ])
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('participants')
            ->with([
                'topic:id,title,slug',
                'directUser1:id,name,surname,image,is_expert,is_top_commentator',
                'directUser2:id,name,surname,image,is_expert,is_top_commentator',
                'participants' => function ($query) use ($user) {
                    $query
                        ->select(['conversation_id', 'user_id', 'joined_at'])
                        ->where('user_id', $user->id);
                },
            ])
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->paginate($conversationsPerPage, ['*'], 'conversations_page')
            ->withQueryString();

        return view('profile.activity', compact('user', 'topics', 'conversations'));
    }

    public function profileVerification(Request $request): RedirectResponse|View
    {
        $user = Auth::user();

        if ($user->shouldVerify() === false) {
            $referer = $request->headers->get('referer');

            return $referer
                ? redirect()->back()
                : redirect()->route('profile.user-info');
        }
        // Email status
        $email_verified = method_exists($user, 'hasVerifiedEmail')
            ? $user->hasVerifiedEmail()
            : !is_null($user->email_verified_at);
        $email_pending = session('email_verification_sent', false);
        $is_email_verification_enabled = Settings::shouldEmailVerify();

        // Phone status (adjust field names to your DB)
        $phone_verified = !is_null($user->phone_verified_at ?? null);
        $phone_pending = PhoneVerificationOtp::activeFor($user->id, 'phone')->exists();
        $phone_expired = PhoneVerificationOtp::expiredFor($user->id, 'phone')->exists();

        $is_phone_verification_enabled = Settings::shouldPhoneVerify();

        // InvalidSignatureException is thrown from InvalidSignatureException handler in app.php
        $email_expired = (bool) session('verification_expired');

        // Forget email verification sent flag if verification link is expired
        if ($email_expired) {
            $request->session()->forget('email_verification_sent');
            $email_pending = false;
        }

        return view('profile.verification', [
            'user' => $user,

            'email_verified' => $email_verified,
            'email_pending' => $email_pending,
            'email_expired' => $email_expired,
            'is_email_verification_enabled' => $is_email_verification_enabled,

            'phone_verified' => $phone_verified,
            'phone_pending' => $phone_pending,
            'phone_expired' => $phone_expired,
            'is_phone_verification_enabled' => $is_phone_verification_enabled,
        ]);
    }


    public function show(): View
    {
        $user = Auth::user();

        $requireEmailVerification = Settings::shouldEmailVerify();
        $requirePhoneVerification = Settings::shouldPhoneVerify();

        $isPasswordEditing =
            request()->boolean('password') ||
            old('_password_edit') === '1';

        $isEditing =
            request()->boolean('edit') ||
            old('_editing') === '1' ||
            session()->has('errors');

        // Keep the sections mutually exclusive
        if ($isPasswordEditing) {
            $isEditing = false;
        }

        $isVerified = method_exists($user, 'isVerified')
            ? (bool) $user->isVerified()
            : false;

        return view('profile.user-info', compact(
            'user',
            'isEditing',
            'isVerified',
            'requireEmailVerification',
            'requirePhoneVerification',
            'isPasswordEditing',
        ));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $updates = [];
        $removeImage = $request->boolean('remove_image');

        foreach (['name', 'surname', 'email', 'phone'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== $user->{$field}) {
                $updates[$field] = $data[$field];
            }
        }

        // Reset verifications when identifiers change
        if (array_key_exists('email', $updates)) {
            $updates['email_verified_at'] = null;
        }
        if (array_key_exists('phone', $updates)) {
            $updates['phone_verified_at'] = null;
        }

        $uploadedFile = $request->file('image');

        // Handle avatar upload (only when fully verified)
        if ($uploadedFile && !$user->isVerified()) {
            return back()
                ->withInput()
                ->with('error', 'ფოტოს განახლება/ატვირთვა შესაძლებელია მხოლოდ ვერიფიცირებული მომხმარებლისთვის.');
        }

        // Handle avatar removal
        if ($removeImage && $user->image) {
            FileUploadService::deleteUploadedFile($user->image, $this->fileFields['image'], 'public');
            $updates['image'] = null;
        }

        // Handle avatar upload (adds a change even when other fields untouched)
        if ($uploadedFile) {
            $existingImage = $removeImage ? null : $user->image;

            $uploadedPath = ImageUploadService::handleOptimizedImageUpload(
                file: $uploadedFile,
                destinationPath: $this->fileFields['image'],
                oldFile: $existingImage,
                webpQuality: 80,
                optimize: true,
                disk: 'public',
                maxWidth: 256,
                maxHeight: 256,
            );

            $updates['image'] = $uploadedPath;
        }

        if (empty($updates)) {
            return redirect()
                ->route('profile.user-info')
                ->withInput(['_editing' => '1'])
                ->with('info', 'ჯერჯერობით არაფერი შეცვლილა.');
        }

        $user->forceFill($updates)->save();

        return redirect()
            ->route('profile.user-info')
            ->with('success', 'პროფილი წარმატებით განახლდა.');
    }

    public function updatePassword(
        UpdatePasswordRequest $request,
        PasswordUpdateService $passwordUpdater
    ): RedirectResponse {
        $passwordUpdater->update($request->user(), $request->validated('password'));

        return redirect()
            ->route('profile.user-info')
            ->with('success', 'პაროლი წარმატებით განახლდა.');
    }

    public function destroy(AccountDeletionService $accountDeletionService): RedirectResponse
    {
        $user = Auth::user();

        $accountDeletionService->delete($user);

        return redirect()
            ->route('page.home')
            ->with('success', 'ანგარიში წარმატებით წაიშალა.');
    }

}
