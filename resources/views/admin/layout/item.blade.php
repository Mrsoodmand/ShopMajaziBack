<div>
    <p class="text-sm text-gray-700 leading-5">
        {!! __('نمایش') !!}
        <span class="font-medium">{{ $paginator->firstItem() }}</span>
        {!! __('تا') !!}
        <span class="font-medium">{{ $paginator->lastItem() }}</span>
        {!! __('از') !!}
        <span class="font-medium">{{ $paginator->total() }}</span>
        {!! __('نتیجه') !!}
    </p>
</div>
