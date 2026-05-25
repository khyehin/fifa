@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $match->title }}</h1>
        <div class="text-muted">{{ $match->match_date->format('Y-m-d') }} · {{ $match->home_team }} vs {{ $match->away_team }}</div>
    </div>
    <div class="d-flex gap-2">
        <button id="save-all-entries" class="btn btn-success">Save</button>
        <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-primary">Edit Match</a>
        <a href="{{ route('matches.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-3 mb-3" id="match-totals">
    <div class="col-md"><div class="card card-body"><div class="text-muted small">Bet Amount</div><div class="summary-number" data-total="bet_amount">0.00</div></div></div>
    <div class="col-md"><div class="card card-body"><div class="text-muted small">Win/Loss</div><div class="summary-number" data-total="black_red_amount">0.00</div></div></div>
    <div class="col-md"><div class="card card-body"><div class="text-muted small">My Win/Loss</div><div class="summary-number" data-total="my_winlose">0.00</div></div></div>
    <div class="col-md"><div class="card card-body"><div class="text-muted small">Run Ticket</div><div class="summary-number" data-total="run_ticket">0.00</div></div></div>
    <div class="col-md"><div class="card card-body"><div class="text-muted small">Net Total</div><div class="summary-number" data-total="net_total">0.00</div></div></div>
</div>

<div class="card card-body mb-3">
    <form id="new-entry-form" class="row g-2 align-items-end">
        @csrf
        <div class="col-lg-2">
            <label class="form-label">User</label>
            <input id="entry-username" class="form-control" list="agent-list" autocomplete="off" required>
            <input type="hidden" name="agent_id" id="entry-agent-id">
            <datalist id="agent-list">
                @foreach($agents as $agent)
                    <option value="{{ $agent->username }}"></option>
                @endforeach
            </datalist>
        </div>
        <div class="col-lg-1"><label class="form-label">Bet</label><input type="number" step="0.01" name="bet_amount" class="form-control"></div>
        <div class="col-lg-1"><label class="form-label">H/A</label><input name="ha" inputmode="decimal" class="form-control signed-money-input sum-source"></div>
        <div class="col-lg-1"><label class="form-label">O/U</label><input name="ou" inputmode="decimal" class="form-control signed-money-input sum-source"></div>
        <div class="col-lg-2"><label class="form-label">Black/Red</label><input type="text" inputmode="decimal" name="black_red_amount" class="form-control signed-money-input" value="0.00"></div>
        <div class="col-lg-1"><label class="form-label">MY %</label><input type="number" step="0.0001" name="my_percent" class="form-control percent-input"></div>
        <div class="col-lg-1"><label class="form-label">Bet x MY%</label><input id="entry-bet-share" class="form-control" value="0.00" readonly></div>
        <div class="col-lg-1"><label class="form-label">Run</label><input type="text" inputmode="decimal" name="run_ticket" class="form-control signed-money-input"></div>
        <div class="col-lg-1"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
        <div class="col-lg-1"><button class="btn btn-primary w-100">Add</button></div>
    </form>
</div>

<div class="card card-body">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle auto-table" id="entries-table">
            <thead><tr><th>User</th><th>Bet Amount</th><th>H/A</th><th>O/U</th><th>Black H/O, Red A/U</th><th class="percent-col">MY %</th><th>Bet x MY%</th><th>My Win/Lose</th><th>Run Tickets</th><th>Total</th><th>Remarks</th><th></th></tr></thead>
            <tbody>
            @foreach($match->entries->sortBy(fn($entry) => $entry->agent->username) as $entry)
                <tr data-entry-id="{{ $entry->id }}">
                    <td><a href="{{ route('agents.history.show', $entry->agent) }}">{{ $entry->agent->username }}</a></td>
                    <td><input class="excel-input row-number" data-field="bet_amount" type="number" step="0.01" value="{{ $entry->bet_amount }}" @readonly($entry->agent->bet_amount_locked)></td>
                    <td><input class="excel-input signed-money-input sum-source" data-field="ha" inputmode="decimal" value="{{ is_numeric($entry->ha) && (float) $entry->ha < 0 ? '(' . number_format(abs((float) $entry->ha), 2) . ')' : $entry->ha }}"></td>
                    <td><input class="excel-input signed-money-input sum-source" data-field="ou" inputmode="decimal" value="{{ is_numeric($entry->ou) && (float) $entry->ou < 0 ? '(' . number_format(abs((float) $entry->ou), 2) . ')' : $entry->ou }}"></td>
                    <td><input class="excel-input row-number signed-money-input" data-field="black_red_amount" type="text" inputmode="decimal" value="{{ (float) $entry->black_red_amount < 0 ? '(' . number_format(abs((float) $entry->black_red_amount), 2) . ')' : number_format((float) $entry->black_red_amount, 2) }}"></td>
                    <td class="percent-col"><input class="excel-input row-number percent-input" data-field="my_percent" type="number" step="0.0001" value="{{ $entry->my_percent }}"></td>
                    <td data-field-display="bet_share" data-value="{{ $entry->bet_share }}"><x-money :value="$entry->bet_share" /></td>
                    <td data-field-display="my_winlose" data-value="{{ $entry->my_winlose }}"><x-money :value="$entry->my_winlose" /></td>
                    <td><input class="excel-input row-number signed-money-input" data-field="run_ticket" type="text" inputmode="decimal" value="{{ (float) $entry->run_ticket < 0 ? '(' . number_format(abs((float) $entry->run_ticket), 2) . ')' : number_format((float) $entry->run_ticket, 2) }}"></td>
                    <td data-field-display="net_total" data-value="{{ $entry->net_total }}"><x-money :value="$entry->net_total" /></td>
                    <td><input class="excel-input" data-field="remarks" value="{{ $entry->remarks }}"></td>
                    <td class="text-end sticky-action"><button class="btn btn-sm btn-outline-danger" data-delete-entry>Delete</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@php
    $agentDefaults = $agents->mapWithKeys(fn ($agent) => [
        $agent->username => [
            'id' => $agent->id,
            'default_bet_amount' => $agent->default_bet_amount,
            'my_percent' => $agent->my_percent,
            'run_ticket' => $agent->run_ticket,
            'bet_amount_locked' => $agent->bet_amount_locked,
        ],
    ]);
@endphp
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const agents = @json($agentDefaults);

function money(value) {
    const amount = Number(value || 0);
    const text = Math.abs(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    return amount < 0 ? `(${text})` : text;
}

function parseMoneyInput(value) {
    const text = String(value || '').trim();
    const negative = text.startsWith('(') && text.endsWith(')');
    const number = Number(text.replace(/[(),]/g, ''));

    return negative ? -Math.abs(number) : number;
}

function formatSignedInput(input) {
    const numeric = parseMoneyInput(input.value);
    if (Number.isNaN(numeric)) return;
    input.classList.toggle('is-negative', numeric < 0);
    if (numeric < 0) input.value = `(${Math.abs(numeric).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
    if (numeric >= 0 && input.classList.contains('signed-money-input')) input.value = numeric.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function unformatSignedInput(input) {
    const numeric = parseMoneyInput(input.value);
    if (!Number.isNaN(numeric)) input.value = String(numeric);
}

function setMoneyCell(cell, value) {
    cell.dataset.value = Number(value || 0);
    cell.textContent = money(value);
    cell.classList.toggle('money-negative', Number(value) < 0);
}

function calculateBetShare(row) {
    const bet = Number(row.querySelector('[data-field="bet_amount"]')?.value || 0);
    const percent = Number(row.querySelector('[data-field="my_percent"]')?.value || 0);

    return bet * percent;
}

function fitInput(input) {
    const length = Math.max(String(input.value || input.placeholder || '').length, 4);
    input.style.width = `${Math.min(Math.max(length + 2, 7), 22)}ch`;
}

function markDirty(input) {
    input.classList.add('is-dirty');
    const row = input.closest('tr');
    if (row) row.dataset.dirty = '1';
}

function updateRowBlackRed(row) {
    const ha = parseMoneyInput(row.querySelector('[data-field="ha"]')?.value || 0);
    const ou = parseMoneyInput(row.querySelector('[data-field="ou"]')?.value || 0);
    const target = row.querySelector('[data-field="black_red_amount"]');

    if (!target || Number.isNaN(ha) || Number.isNaN(ou)) return;
    target.value = String(ha + ou);
    formatSignedInput(target);
    markDirty(target);
}

function updateNewBlackRed() {
    const form = document.querySelector('#new-entry-form');
    const total = parseMoneyInput(form.ha.value || 0) + parseMoneyInput(form.ou.value || 0);
    form.black_red_amount.value = String(total);
    formatSignedInput(form.black_red_amount);
}

function updateNewBetShare() {
    const form = document.querySelector('#new-entry-form');
    const target = document.querySelector('#entry-bet-share');
    target.value = money(Number(form.bet_amount.value || 0) * Number(form.my_percent.value || 0));
}

function recalcTotals() {
    const totals = {bet_amount:0, black_red_amount:0, my_winlose:0, run_ticket:0, net_total:0};
    document.querySelectorAll('#entries-table tbody tr').forEach(row => {
        totals.bet_amount += Number(row.querySelector('[data-field="bet_amount"]')?.value || 0);
        totals.black_red_amount += parseMoneyInput(row.querySelector('[data-field="black_red_amount"]')?.value || 0);
        totals.my_winlose += Number(row.querySelector('[data-field-display="my_winlose"]')?.dataset.value || 0);
        totals.run_ticket += parseMoneyInput(row.querySelector('[data-field="run_ticket"]')?.value || 0);
        totals.net_total += Number(row.querySelector('[data-field-display="net_total"]')?.dataset.value || 0);
    });
    Object.entries(totals).forEach(([key, value]) => {
        const target = document.querySelector(`[data-total="${key}"]`);
        target.textContent = money(value);
        target.classList.toggle('money-negative', value < 0);
    });
}

async function quickCreate(username) {
    const response = await fetch('{{ route('agents.quick-create') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: JSON.stringify({username})
    });
    if (!response.ok) throw new Error('Unable to create user');
    const agent = await response.json();
    agents[agent.username] = agent;
    const option = document.createElement('option');
    option.value = agent.username;
    document.querySelector('#agent-list').appendChild(option);
    return agent;
}

document.querySelector('#entry-username').addEventListener('change', async event => {
    const username = event.target.value.trim();
    if (!username) return;
    let agent = agents[username];
    if (!agent && confirm(`Create new agent "${username}"?`)) agent = await quickCreate(username);
    if (!agent) return;
    document.querySelector('#entry-agent-id').value = agent.id;
    const form = document.querySelector('#new-entry-form');
    form.bet_amount.value = agent.default_bet_amount ?? 0;
    form.my_percent.value = agent.my_percent ?? 1;
    form.run_ticket.value = agent.run_ticket ?? 0;
    form.bet_amount.readOnly = Boolean(agent.bet_amount_locked);
    updateNewBetShare();
});

document.querySelector('#new-entry-form').bet_amount.addEventListener('input', updateNewBetShare);
document.querySelector('#new-entry-form').my_percent.addEventListener('input', updateNewBetShare);
document.querySelector('#new-entry-form').ha.addEventListener('input', () => {
    updateNewBlackRed();
});
document.querySelector('#new-entry-form').ou.addEventListener('input', () => {
    updateNewBlackRed();
});

document.querySelector('#new-entry-form').addEventListener('submit', async event => {
    event.preventDefault();
    const username = document.querySelector('#entry-username').value.trim();
    if (!document.querySelector('#entry-agent-id').value && username) {
        const agent = agents[username] || await quickCreate(username);
        document.querySelector('#entry-agent-id').value = agent.id;
    }
    const response = await fetch('{{ route('matches.entries.store', $match) }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: new FormData(event.target)
    });
    if (!response.ok) return alert('Entry could not be saved.');
    window.location.reload();
});

async function saveRow(row) {
    const data = {};
    row.querySelectorAll('[data-field]').forEach(field => data[field.dataset.field] = field.classList.contains('signed-money-input') ? parseMoneyInput(field.value) : field.value);
    const response = await fetch(`/entries/${row.dataset.entryId}`, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
        body: JSON.stringify(data)
    });
    if (!response.ok) throw new Error('Save failed');
    const payload = await response.json();
    row.querySelectorAll('.signed-money-input').forEach(formatSignedInput);
    setMoneyCell(row.querySelector('[data-field-display="bet_share"]'), payload.bet_share ?? calculateBetShare(row));
    setMoneyCell(row.querySelector('[data-field-display="my_winlose"]'), payload.my_winlose);
    setMoneyCell(row.querySelector('[data-field-display="net_total"]'), payload.net_total);
    row.querySelectorAll('.is-dirty').forEach(input => input.classList.remove('is-dirty'));
    delete row.dataset.dirty;
}

document.querySelectorAll('#entries-table [data-field]').forEach(input => {
    fitInput(input);
    input.classList.toggle('is-negative', parseMoneyInput(input.value || 0) < 0);
    input.addEventListener('input', event => {
        event.target.classList.toggle('is-negative', parseMoneyInput(event.target.value || 0) < 0);
        fitInput(event.target);
        markDirty(event.target);
        if (event.target.classList.contains('sum-source')) {
            updateRowBlackRed(event.target.closest('tr'));
        }
    });
});

document.querySelector('#save-all-entries').addEventListener('click', async event => {
    const button = event.currentTarget;
    const dirtyRows = [...document.querySelectorAll('#entries-table tbody tr[data-dirty="1"]')];
    if (!dirtyRows.length) return;

    button.disabled = true;
    button.textContent = 'Saving...';
    try {
        for (const row of dirtyRows) {
            await saveRow(row);
        }
        recalcTotals();
        button.textContent = 'Saved';
        setTimeout(() => button.textContent = 'Save', 1000);
    } catch (error) {
        alert('Save failed. Please check the row values.');
        button.textContent = 'Save';
    } finally {
        button.disabled = false;
    }
});

document.querySelectorAll('[data-delete-entry]').forEach(button => {
    button.addEventListener('click', async event => {
        if (!confirm('Delete this row?')) return;
        const row = event.target.closest('tr');
        const response = await fetch(`/entries/${row.dataset.entryId}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
        });
        if (response.ok) {
            row.remove();
            recalcTotals();
        }
    });
});

recalcTotals();
document.querySelectorAll('.signed-money-input').forEach(input => {
    formatSignedInput(input);
    input.addEventListener('focus', event => unformatSignedInput(event.target));
    input.addEventListener('blur', event => {
        formatSignedInput(event.target);
        fitInput(event.target);
    });
});
updateNewBetShare();
updateNewBlackRed();
</script>
@endpush
