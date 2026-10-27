@props(['icon'=>'📌','label'=>'','count'=>null,'href'=>'#','color'=>'gray'])
@php $colors=['gray'=>'bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100','purple'=>'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100','green'=>'bg-green-50 border-green-200 text-green-700 hover:bg-green-100','red'=>'bg-red-50 border-red-200 text-red-700 hover:bg-red-100','blue'=>'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100'];$cls=$colors[$color]??$colors['gray']; @endphp
<a href="{{ $href }}" class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs transition {{ $cls }}">
    <span class="text-base leading-tight">{{ $icon }}</span>
    @if($count!==null)<span class="font-bold text-sm leading-tight">{{ $count }}</span>@endif
    <span class="leading-tight">{{ $label }}</span>
</a>
