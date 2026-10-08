@props(['paragraph'])
@php
    $emphasis = $paragraph['emphasis'] ?? [];
    $parts = $emphasis
        ? preg_split('/('.implode('|', array_map(static fn ($text) => preg_quote($text, '/'), $emphasis)).')/u', $paragraph['text'], -1, PREG_SPLIT_DELIM_CAPTURE)
        : [$paragraph['text']];
    $paragraphHtml = '';
    foreach ($parts as $part) {
        $paragraphHtml .= in_array($part, $emphasis, true) ? '<strong>'.e($part).'</strong>' : e($part);
    }
@endphp
<p>{!! $paragraphHtml !!}</p>
