<?php

namespace App\Livewire\Admin\Accounting\Journals;

use App\Services\AccountingJournalService;
use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Jurnal Umum')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    protected AccountingJournalService $service;

    public string $journal_date = '';
    public ?string $entry_description = null;
    public array $lines = [];

    public function boot(AccountingJournalService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->authorize('journal.view');

        $this->setPageMeta(
            'Jurnal Umum',
            'Input jurnal manual dengan validasi debit dan kredit seimbang.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Akuntansi', 'current' => true],
                ['label' => 'Jurnal Umum', 'current' => true],
            ]
        );

        $this->resetEntryForm();
    }

    protected function rules(): array
    {
        return [
            'journal_date' => ['required', 'date'],
            'entry_description' => ['nullable', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['nullable', 'integer'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function addLine(): void
    {
        $this->lines[] = $this->blankLine();
    }

    public function removeLine(int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);

        if (count($this->lines) < 2) {
            $this->lines[] = $this->blankLine();
        }
    }

    public function save(): void
    {
        $this->authorize('journal.create');

        try {
            $data = $this->validate();
            $journal = $this->service->store(
                [
                    'journal_date' => $data['journal_date'],
                    'description' => $data['entry_description'] ?? null,
                ],
                $data['lines'] ?? []
            );

            $journalNo = $journal->journal_no;
            $this->resetEntryForm();

            $this->dispatch('toast', message: "Jurnal {$journalNo} berhasil disimpan.", type: 'success');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->toastError($e, 'Gagal menyimpan jurnal.');
        }
    }

    protected function resetEntryForm(): void
    {
        $this->journal_date = now()->toDateString();
        $this->entry_description = null;
        $this->lines = [$this->blankLine(), $this->blankLine()];
    }

    protected function blankLine(): array
    {
        return [
            'account_id' => null,
            'debit' => null,
            'credit' => null,
            'description' => null,
        ];
    }

    public function render()
    {
        return view('livewire.admin.accounting.journals.index', [
            'accounts' => $this->service->accountOptions(),
            'recentJournals' => $this->service->recent(12),
        ]);
    }
}
