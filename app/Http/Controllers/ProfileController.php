<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Settings;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected array $fileFields = [
        'image' => 'avatars/',
    ];


    public function show(): View
    {
        $user = Auth::user();

        $requireEmailVerification = Settings::shouldEmailVerify();
        $requirePhoneVerification = Settings::shouldPhoneVerify();

        $isEditing =
            request()->boolean('edit') ||
            old('_editing') === '1' ||
            session()->has('errors');

        $isVerified = method_exists($user, 'isVerified')
            ? (bool) $user->isVerified()
            : false;

        $avatarUrl = $user->avatar_url;
        $avatarInitial = $user->initials ?? '?';


        return view('profile.user-info', compact(
            'user',
            'isEditing',
            'isVerified',
            'avatarUrl',
            'avatarInitial',
            'requireEmailVerification',
            'requirePhoneVerification',
        ));
    }


    // public function update(UpdateProfileRequest $request): RedirectResponse
    // {
    //     $user = $request->user();
    //     $data = $request->validated();

    //     $updates = [];
    //     $removeImage = $request->boolean('remove_image');

    //     foreach (['name', 'surname', 'email', 'phone'] as $field) {
    //         if (array_key_exists($field, $data) && $data[$field] !== $user->{$field}) {
    //             $updates[$field] = $data[$field];
    //         }
    //     }

    //     // Reset verifications when identifiers change
    //     if (array_key_exists('email', $updates)) {
    //         $updates['email_verified_at'] = null;
    //     }
    //     if (array_key_exists('phone', $updates)) {
    //         $updates['phone_verified_at'] = null;
    //     }

    //     $uploadedFile = $request->file('image');

    //     // Handle avatar upload (only when fully verified)
    //     if ($uploadedFile && !$user->isVerified()) {
    //         return back()
    //             ->withInput()
    //             ->with('error', 'ფოტოს განახლება/ატვირთვა შესაძლებელია მხოლოდ ვერიფიცირებული მომხმარებლისთვის.');
    //     }

    //     // Handle avatar removal
    //     if ($removeImage && $user->image) {
    //         $this->deleteUploadedFile($user->image, $this->fileFields['image']);
    //         $updates['image'] = null;
    //     }

    //     // Handle avatar upload (adds a change even when other fields untouched)
    //     if ($uploadedFile) {
    //         $existingImage = $removeImage ? null : $user->image;

    //         $uploadedPath = $this->handleOptimizedImageUpload(
    //             $request,
    //             'image',
    //             $this->fileFields['image'],
    //             $existingImage,
    //             80,
    //             true,
    //         );

    //         if (!$uploadedPath) {
    //             return back()
    //                 ->withInput()
    //                 ->with('error', 'ახალი ფოტოს ატვირთვა/განახლება ვერ მოხერხდა. გთხოვ სცადო კიდევ.');
    //         }

    //         $updates['image'] = $uploadedPath;
    //     }

    //     if (empty($updates)) {
    //         return redirect()
    //             ->route('profile.user-info')
    //             ->withInput(['_editing' => '1'])
    //             ->with('info', 'ჯერჯერობით არაფერი შეცვლილა.');
    //     }

    //     $user->forceFill($updates)->save();

    //     return redirect()
    //         ->route('profile.user-info')
    //         ->with('success', 'პროფილი წარმატებით განახლდა.');
    // }





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
            $this->deleteUploadedFile($user->image, $this->fileFields['image']);
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

}

