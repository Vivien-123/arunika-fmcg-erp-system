@props(['stages'=>[],'current'=>''])
<div class="flex items-center gap-0 text-xs select-none">
    @foreach($stages as $i=>$stage)
    @php $isDone=array_search($stage,$stages)<array_search($current,$stages);$isCurrent=$stage===$current; @endphp
    @if($i>0)<div class="w-4 flex items-center justify-center text-gray-300">›</div>@endif
    <span class="px-3 py-1 rounded @if($isCurrent) bg-[#714B67] text-white font-semibold @elseif($isDone) bg-green-100 text-green-700 @else bg-gray-100 text-gray-400 @endif">
        @if($isDone)<span class="mr-1">✓</span>@endif{{ $stage }}
    </span>
    @endforeach
</div>
