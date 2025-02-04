<?php

namespace App\Livewire;

use Livewire\Component;

class EditSettings extends Component
{

    public $terms_and_conditions_ar;
    public $terms_and_conditions_en;
    public $privacy_policy_ar;
    public $privacy_policy_en;
    public $about_us_ar;
    public $about_us_en;
    public $invitation_profit;

    public $periodic_inspection_service_tax;

    public function mount()
    {
        $this->terms_and_conditions_ar = settings()->get('terms_and_conditions_ar') ?? '';
        $this->terms_and_conditions_en = settings()->get('terms_and_conditions_en') ?? '';
        $this->privacy_policy_ar = settings()->get('privacy_policy_ar') ?? '';
        $this->privacy_policy_en = settings()->get('privacy_policy_en') ?? '';
        $this->about_us_ar = settings()->get('about_us_ar') ?? '';
        $this->about_us_en = settings()->get('about_us_en') ?? '';
        $this->invitation_profit = settings()->get('invitation_profit') ?? '';
        $this->periodic_inspection_service_tax = settings()->get('periodic_inspection_service_tax') ?? '';
    }

    public function save()
    {

        settings()->set('terms_and_conditions_ar', $this->terms_and_conditions_ar);
        settings()->set('terms_and_conditions_en', $this->terms_and_conditions_en);
        settings()->set('privacy_policy_ar', $this->privacy_policy_ar);
        settings()->set('privacy_policy_en', $this->privacy_policy_en);
        settings()->set('about_us_ar', $this->about_us_ar);
        settings()->set('about_us_en', $this->about_us_en);
        settings()->set('invitation_profit', $this->invitation_profit);
        settings()->set('periodic_inspection_service_tax', $this->periodic_inspection_service_tax);

        session()->flash('message', 'Settings saved successfully.');
        return redirect()->route('filament.manage.pages.dashboard');
    }

    public function render()
    {
        return view('livewire.edit-settings');
    }
}
