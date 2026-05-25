@props(['value' => 0])
@php($amount = (float) $value)
<span class="{{ $amount < 0 ? 'money-negative' : '' }}">
    {{ $amount < 0 ? '(' . number_format(abs($amount), 2) . ')' : number_format($amount, 2) }}
</span>
