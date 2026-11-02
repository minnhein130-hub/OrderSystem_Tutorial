@props(['status'])

@php($value = $status instanceof \App\Enums\OrderStatus ? $status->value : $status)

<span @class([
    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
    'bg-amber-50 text-amber-800' => $value === 'pending',
    'bg-emerald-50 text-emerald-800' => $value === 'completed',
    'bg-slate-100 text-slate-700' => $value === 'cancelled',
])>{{ $value }}</span>
