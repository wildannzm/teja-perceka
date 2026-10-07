<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkey;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Security settings')]
class Security extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    #[Locked]
    public bool $canManageTwoFactor;

    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    public bool $showPasswordModal = false;

    public bool $showPasskeyModal = false;

    public bool $showDeletePasskeyModal = false;

    #[Locked]
    public ?int $passkeyIdToDelete = null;

    public bool $showDisableTwoFactorModal = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    #[Locked]
    public bool $canManagePasskeys;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        $this->canManagePasskeys = Features::canManagePasskeys();
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->showPasswordModal = false;

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Open the change password modal.
     */
    public function openPasswordModal(): void
    {
        $this->reset('current_password', 'password', 'password_confirmation');

        $this->resetErrorBag();

        $this->showPasswordModal = true;
    }

    /**
     * Close the change password modal.
     */
    public function closePasswordModal(): void
    {
        $this->reset('current_password', 'password', 'password_confirmation', 'showPasswordModal');

        $this->resetErrorBag();
    }

    /**
     * Open the add passkey modal.
     */
    public function openPasskeyModal(): void
    {
        abort_unless($this->canManagePasskeys, 403);

        $this->showPasskeyModal = true;
    }

    /**
     * Close the add passkey modal and refresh the list.
     */
    public function closePasskeyModal(): void
    {
        $this->reset('showPasskeyModal');

        unset($this->passkeys);
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Gagal memuat data penyiapan. Coba lagi.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Kode salah', text: 'Kode verifikasi tidak valid. Periksa kembali kode 6 digit dari aplikasi authenticator.');

            throw $e;
        }

        try {
            $confirmTwoFactorAuthentication(auth()->user(), $this->code);
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Kode salah', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        $this->closeModal();

        $this->twoFactorEnabled = true;

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Autentikasi dua faktor berhasil diaktifkan.');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Ask for confirmation before disabling 2FA.
     */
    public function confirmDisableTwoFactor(): void
    {
        abort_unless($this->canManageTwoFactor, 403);

        $this->showDisableTwoFactorModal = true;
    }

    /**
     * Cancel disabling 2FA.
     */
    public function cancelDisableTwoFactor(): void
    {
        $this->reset('showDisableTwoFactorModal');
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;

        $this->reset('showDisableTwoFactorModal');

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Autentikasi dua faktor dinonaktifkan.');
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Passkeys owned by the user (id, name, authenticator info, last used).
     *
     * @return array<int, array{id: int, name: string, authenticator: string|null, last_used_at: string|null, created_at: string|null}>
     */
    #[Computed]
    public function passkeys(): array
    {
        if (! $this->canManagePasskeys) {
            return [];
        }

        return auth()->user()->passkeys()
            ->latest()
            ->get(['id', 'name', 'credential', 'last_used_at', 'created_at'])
            ->map(fn (Passkey $passkey): array => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'last_used_at' => $passkey->last_used_at?->toDateTimeString(),
                'created_at' => $passkey->created_at?->toDateTimeString(),
            ])
            ->all();
    }

    /**
     * Ask for confirmation before deleting a passkey.
     */
    public function confirmDeletePasskey(int $passkeyId): void
    {
        abort_unless($this->canManagePasskeys, 403);

        $passkey = auth()->user()->passkeys()->findOrFail($passkeyId);

        $this->passkeyIdToDelete = $passkey->id;

        $this->showDeletePasskeyModal = true;
    }

    /**
     * Cancel passkey deletion.
     */
    public function cancelDeletePasskey(): void
    {
        $this->reset('showDeletePasskeyModal', 'passkeyIdToDelete');
    }

    /**
     * Delete one passkey owned by the current user.
     */
    public function deletePasskey(): void
    {
        abort_unless($this->canManagePasskeys, 403);
        abort_if($this->passkeyIdToDelete === null, 404);

        $passkey = auth()->user()->passkeys()->findOrFail($this->passkeyIdToDelete);

        $passkey->delete();

        unset($this->passkeys);

        $this->reset('showDeletePasskeyModal', 'passkeyIdToDelete');

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Passkey berhasil dihapus.');
    }

    /**
     * Get the current modal configuration state.
     *
     * @return array{title: string, description: string, buttonText: string}
     */
    #[Computed]
    public function modalConfig(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => 'Autentikasi dua faktor aktif',
                'description' => 'Autentikasi dua faktor sudah aktif. Pindai kode QR atau masukkan kunci penyiapan di aplikasi authenticator Anda.',
                'buttonText' => 'Tutup',
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => 'Verifikasi kode autentikasi',
                'description' => 'Masukkan kode 6 digit dari aplikasi authenticator Anda.',
                'buttonText' => 'Lanjutkan',
            ];
        }

        return [
            'title' => 'Aktifkan autentikasi dua faktor',
            'description' => 'Untuk menyelesaikan aktivasi, pindai kode QR atau masukkan kunci penyiapan di aplikasi authenticator Anda.',
            'buttonText' => 'Lanjutkan',
        ];
    }
}
