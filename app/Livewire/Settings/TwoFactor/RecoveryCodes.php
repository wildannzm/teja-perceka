<?php

namespace App\Livewire\Settings\TwoFactor;

use Exception;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RecoveryCodes extends Component
{
    /** @var list<string> */
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        try {
            $generateNewRecoveryCodes(auth()->user());
        } catch (Exception) {
            $this->addError('recoveryCodes', 'Gagal membuat kode baru. Coba lagi.');

            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal', text: 'Gagal membuat kode pemulihan baru. Coba lagi.');

            return;
        }

        $this->loadRecoveryCodes();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Kode pemulihan baru berhasil dibuat. Simpan di tempat aman.');
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Gagal memuat kode pemulihan.');

                $this->recoveryCodes = [];
            }
        }
    }
}
