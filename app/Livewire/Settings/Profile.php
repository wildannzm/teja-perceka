<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public bool $showProfileModal = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Open the edit profile modal.
     */
    public function openProfileModal(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        $this->resetErrorBag();

        $this->showProfileModal = true;
    }

    /**
     * Close the edit profile modal.
     */
    public function closeProfileModal(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        $this->reset('showProfileModal');

        $this->resetErrorBag();
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        try {
            $validated = $this->validate($this->profileRules($user->id));
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->reset('showProfileModal');

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Profil berhasil diperbarui.');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        $user = Auth::user();

        return $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail();
    }
}
