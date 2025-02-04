<div class="p-6 bg-white shadow sm:rounded-lg">
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label for="terms_and_conditions_ar" class="block text-sm font-medium text-gray-700">الشروط والأحكام (عربي)</label>
            <textarea id="terms_and_conditions_ar" wire:model="terms_and_conditions_ar" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="terms_and_conditions_en" class="block text-sm font-medium text-gray-700">Terms and Conditions (English)</label>
            <textarea id="terms_and_conditions_en" wire:model="terms_and_conditions_en" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="privacy_policy_ar" class="block text-sm font-medium text-gray-700">سياسة الخصوصية (عربي)</label>
            <textarea id="privacy_policy_ar" wire:model="privacy_policy_ar" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="privacy_policy_en" class="block text-sm font-medium text-gray-700">Privacy Policy (English)</label>
            <textarea id="privacy_policy_en" wire:model="privacy_policy_en" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="about_us_ar" class="block text-sm font-medium text-gray-700">من نحن (عربي)</label>
            <textarea id="about_us_ar" wire:model="about_us_ar" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="about_us_en" class="block text-sm font-medium text-gray-700">About Us (English)</label>
            <textarea id="about_us_en" wire:model="about_us_en" rows="5" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
        </div>

        <div>
            <label for="invitation_profit" class="block text-sm font-medium text-gray-700">ربح الدعوة</label>
            <input type="number" id="invitation_profit" wire:model="invitation_profit" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <div>
            <label for="periodic_inspection_service_tax" class="block text-sm font-medium text-gray-700">ضريبة خدمة الفحص الدوري</label>
            <input type="number" id="periodic_inspection_service_tax" wire:model="periodic_inspection_service_tax" class="w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <button type="submit" style="width: 100%; padding: 12px 24px; color: white; background-color: rgba(var(--primary-600),var(--tw-text-opacity,1)); border-radius: 0.375rem; transition: background-color 0.3s ease; border: none; cursor: pointer; outline: none;" onmouseover="this.style.backgroundColor='#0077b6'" onmouseout="this.style.backgroundColor='#fca311'" onfocus="this.style.boxShadow='0 0 0 2px rgba(59, 130, 246, 0.5)'" onblur="this.style.boxShadow='none'">
            حفظ
        </button>
    </form>

    @if (session()->has('success'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        <span class="font-medium">نجاح:</span> {{ session('success') }}
    </div>
    @endif

</div>
