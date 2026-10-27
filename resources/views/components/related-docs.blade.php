@props(['docs'=>[]])
@if(count($docs)>0)
<div class="mt-3">
    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Related Documents</h4>
    <div class="flex flex-wrap gap-2">
        @foreach($docs as $doc)
        <a href="{{ $doc['url']??'#' }}" class="inline-flex items-center gap-1.5 border border-gray-200 rounded px-2 py-1 text-xs bg-white hover:bg-gray-50 text-gray-700 transition">
            <span class="text-gray-400">📄</span>
            <span class="font-mono font-semibold">{{ $doc['id'] }}</span>
            <span class="text-gray-500">{{ $doc['type'] }}</span>
            @if(isset($doc['status']))<span class="text-[10px] px-1 rounded @if(in_array($doc['status'],['Done','Posted','Paid'])) bg-green-100 text-green-700 @elseif($doc['status']==='Confirmed') bg-blue-100 text-blue-700 @else bg-gray-100 text-gray-500 @endif">{{ $doc['status'] }}</span>@endif
        </a>
        @endforeach
    </div>
</div>
@endif
