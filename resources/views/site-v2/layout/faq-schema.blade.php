@php
    // json_encode (not Blade echo) so quotes/newlines in answers can't break the JSON-LD.
    $faqEntities = collect($FAQ)->map(function ($item) {
        $q = trim(strip_tags($item['question'] ?? $item['q'] ?? ''));
        $a = trim(strip_tags($item['answer'] ?? $item['a'] ?? ''));
        return $q !== '' && $a !== '' ? ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]] : null;
    })->filter()->values();
@endphp
@if($faqEntities->isNotEmpty())
<script type="application/ld+json">{!! json_encode(['@@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqEntities], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
