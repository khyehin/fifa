@props([
    'label' => 'Date Range',
    'from' => request('date_from'),
    'to' => request('date_to'),
    'all' => request('date_all'),
])

@php
    $from = trim((string) $from);
    $to = trim((string) $to);
    $all = trim((string) $all);
    $displayText = '';

    if ($all === '1') {
        $displayText = 'All';
    } elseif ($from !== '' && $to !== '') {
        $displayText = $from . ' to ' . $to;
    } elseif ($from !== '') {
        $displayText = $from;
    } elseif ($to !== '') {
        $displayText = $to;
    }
@endphp

<div class="form-group date-range-field" data-drp>
    <label class="form-label">{{ $label }}</label>
    <div class="date-filter-wrapper">
        <input
            type="text"
            class="form-control drp-display-input"
            placeholder="Select date range"
            readonly
            autocomplete="off"
            value="{{ $displayText }}"
            data-drp-display
        >
        <input type="hidden" name="date_from" value="{{ $from }}" data-drp-from>
        <input type="hidden" name="date_to" value="{{ $to }}" data-drp-to>
        <input type="hidden" name="date_all" value="{{ $all }}" data-drp-all>

        <div class="drp-container" data-drp-container style="display:none;">
            <div class="drp-wrapper">
                <div class="drp-header">
                    <button type="button" class="drp-nav" data-dir="-1">&lt;</button>
                    <div class="drp-month-label" data-drp-month></div>
                    <button type="button" class="drp-nav" data-dir="1">&gt;</button>
                </div>
                <div class="drp-week-row">
                    <div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div><div>Su</div>
                </div>
                <div class="drp-grid" data-drp-grid></div>
            </div>
            <div class="drp-quick-bar">
                <button type="button" class="drp-quick-item" data-range="today">Today</button>
                <button type="button" class="drp-quick-item" data-range="yesterday">Yesterday</button>
                <button type="button" class="drp-quick-item" data-range="this_week">This Week</button>
                <button type="button" class="drp-quick-item" data-range="last_week">Last Week</button>
                <button type="button" class="drp-quick-item" data-range="this_month">This Month</button>
                <button type="button" class="drp-quick-item" data-range="last_month">Last Month</button>
                <button type="button" class="drp-quick-item" data-range="this_year">This Year</button>
                <button type="button" class="drp-quick-item" data-range="last_year">Last Year</button>
                <button type="button" class="drp-quick-item" data-range="all">All</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('head')
        <style>
            .date-filter-wrapper { position:relative; }
            .drp-display-input { cursor:pointer; background:#fff; }
            .drp-container {
                position:absolute;
                top:calc(100% + 8px);
                left:0;
                z-index:60;
                display:flex;
                gap:.75rem;
                min-width: 520px;
                padding:.75rem;
                border:1px solid var(--line);
                border-radius:10px;
                background:#fff;
                box-shadow:0 22px 55px rgba(31,39,35,.16);
            }
            .drp-wrapper { width: 290px; }
            .drp-header {
                display:flex;
                align-items:center;
                justify-content:space-between;
                margin-bottom:.5rem;
            }
            .drp-nav {
                border:1px solid var(--line);
                background:#fff;
                border-radius:7px;
                width:30px;
                height:30px;
            }
            .drp-month-label { font-weight:700; }
            .drp-week-row, .drp-grid {
                display:grid;
                grid-template-columns: repeat(7, 1fr);
                gap:.22rem;
                text-align:center;
            }
            .drp-week-row {
                color:var(--muted);
                font-size:.72rem;
                font-weight:700;
                margin-bottom:.25rem;
            }
            .drp-day {
                border:0;
                border-radius:7px;
                background:#f6f8f7;
                min-height:32px;
                font-size:.82rem;
            }
            .drp-day:hover { background:var(--accent-soft); }
            .drp-day.is-muted { opacity:.35; }
            .drp-day.is-selected {
                background:var(--accent);
                color:#fff;
                font-weight:700;
            }
            .drp-day.is-range {
                background:#dcece7;
                color:var(--accent-dark);
            }
            .drp-quick-bar {
                min-width:150px;
                display:grid;
                gap:.25rem;
                align-content:start;
            }
            .drp-quick-item {
                text-align:left;
                border:0;
                border-radius:7px;
                background:#f6f8f7;
                padding:.45rem .6rem;
                font-weight:650;
                color:#3b4742;
            }
            .drp-quick-item:hover { background:var(--accent-soft); color:var(--accent-dark); }
            @media (max-width: 767.98px) {
                .drp-container { min-width: min(92vw, 520px); flex-direction:column; }
                .drp-wrapper { width:100%; }
                .drp-quick-bar { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            (() => {
                const pad = value => String(value).padStart(2, '0');
                const fmt = date => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
                const parse = value => {
                    if (!value) return null;
                    const [year, month, day] = value.split('-').map(Number);
                    return new Date(year, month - 1, day);
                };
                const startOfWeek = date => {
                    const copy = new Date(date);
                    const day = (copy.getDay() + 6) % 7;
                    copy.setDate(copy.getDate() - day);
                    return copy;
                };
                const endOfWeek = date => {
                    const copy = startOfWeek(date);
                    copy.setDate(copy.getDate() + 6);
                    return copy;
                };
                const setDisplay = root => {
                    const from = root.querySelector('[data-drp-from]').value;
                    const to = root.querySelector('[data-drp-to]').value;
                    const all = root.querySelector('[data-drp-all]').value;
                    const display = root.querySelector('[data-drp-display]');
                    if (all === '1') display.value = 'All';
                    else if (from && to) display.value = `${from} to ${to}`;
                    else display.value = from || to || '';
                };
                const rangeFor = range => {
                    const today = new Date();
                    let start = new Date(today);
                    let end = new Date(today);
                    if (range === 'yesterday') start.setDate(today.getDate() - 1), end = new Date(start);
                    if (range === 'this_week') start = startOfWeek(today), end = endOfWeek(today);
                    if (range === 'last_week') start = startOfWeek(today), start.setDate(start.getDate() - 7), end = endOfWeek(start);
                    if (range === 'this_month') start = new Date(today.getFullYear(), today.getMonth(), 1), end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    if (range === 'last_month') start = new Date(today.getFullYear(), today.getMonth() - 1, 1), end = new Date(today.getFullYear(), today.getMonth(), 0);
                    if (range === 'this_year') start = new Date(today.getFullYear(), 0, 1), end = new Date(today.getFullYear(), 11, 31);
                    if (range === 'last_year') start = new Date(today.getFullYear() - 1, 0, 1), end = new Date(today.getFullYear() - 1, 11, 31);
                    return {start, end};
                };
                const render = root => {
                    const grid = root.querySelector('[data-drp-grid]');
                    const monthLabel = root.querySelector('[data-drp-month]');
                    const view = parse(root.dataset.viewMonth) || parse(root.querySelector('[data-drp-from]').value) || new Date();
                    const selectedFrom = parse(root.querySelector('[data-drp-from]').value);
                    const selectedTo = parse(root.querySelector('[data-drp-to]').value);
                    const monthStart = new Date(view.getFullYear(), view.getMonth(), 1);
                    const first = startOfWeek(monthStart);
                    monthLabel.textContent = view.toLocaleString(undefined, {month: 'long', year: 'numeric'});
                    grid.innerHTML = '';
                    for (let i = 0; i < 42; i++) {
                        const day = new Date(first);
                        day.setDate(first.getDate() + i);
                        const key = fmt(day);
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'drp-day';
                        button.textContent = day.getDate();
                        if (day.getMonth() !== view.getMonth()) button.classList.add('is-muted');
                        if (selectedFrom && key === fmt(selectedFrom)) button.classList.add('is-selected');
                        if (selectedTo && key === fmt(selectedTo)) button.classList.add('is-selected');
                        if (selectedFrom && selectedTo && day > selectedFrom && day < selectedTo) button.classList.add('is-range');
                        button.addEventListener('click', () => {
                            const fromInput = root.querySelector('[data-drp-from]');
                            const toInput = root.querySelector('[data-drp-to]');
                            root.querySelector('[data-drp-all]').value = '';
                            if (!fromInput.value || (fromInput.value && toInput.value)) {
                                fromInput.value = key;
                                toInput.value = '';
                            } else if (parse(key) < parse(fromInput.value)) {
                                toInput.value = fromInput.value;
                                fromInput.value = key;
                            } else {
                                toInput.value = key;
                            }
                            setDisplay(root);
                            render(root);
                        });
                        grid.appendChild(button);
                    }
                };
                document.querySelectorAll('[data-drp]').forEach(root => {
                    const container = root.querySelector('[data-drp-container]');
                    const display = root.querySelector('[data-drp-display]');
                    root.dataset.viewMonth = root.querySelector('[data-drp-from]').value || fmt(new Date());
                    display.addEventListener('click', () => {
                        container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                        render(root);
                    });
                    root.querySelectorAll('[data-dir]').forEach(button => {
                        button.addEventListener('click', () => {
                            const view = parse(root.dataset.viewMonth) || new Date();
                            view.setMonth(view.getMonth() + Number(button.dataset.dir));
                            root.dataset.viewMonth = fmt(view);
                            render(root);
                        });
                    });
                    root.querySelectorAll('[data-range]').forEach(button => {
                        button.addEventListener('click', () => {
                            const range = button.dataset.range;
                            if (range === 'all') {
                                root.querySelector('[data-drp-from]').value = '';
                                root.querySelector('[data-drp-to]').value = '';
                                root.querySelector('[data-drp-all]').value = '1';
                            } else {
                                const dates = rangeFor(range);
                                root.querySelector('[data-drp-from]').value = fmt(dates.start);
                                root.querySelector('[data-drp-to]').value = fmt(dates.end);
                                root.querySelector('[data-drp-all]').value = '';
                                root.dataset.viewMonth = fmt(dates.start);
                            }
                            setDisplay(root);
                            render(root);
                            container.style.display = 'none';
                        });
                    });
                    document.addEventListener('click', event => {
                        if (!root.contains(event.target)) container.style.display = 'none';
                    });
                    setDisplay(root);
                    render(root);
                });
            })();
        </script>
    @endpush
@endonce
