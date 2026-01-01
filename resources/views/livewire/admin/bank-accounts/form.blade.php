<div class="space-y-4">
    <x-forms.input label="Nomor Rekening" name="form.rekening_number" placeholder="Nomor rekening"
        wire:model.blur="form.rekening_number" required />

    <x-forms.input label="Nama Pemilik Rekening" name="form.account_name" placeholder="Nama pemilik"
        wire:model.blur="form.account_name" required />

    <x-forms.input label="Nama Bank" name="form.bank_name" placeholder="Nama bank"
        wire:model.blur="form.bank_name" required />
</div>
