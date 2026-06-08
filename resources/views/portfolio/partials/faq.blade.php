<section id="faq" class="faq-section fade-in" aria-labelledby="faq-title">
    <div class="faq-shell">
        <span class="section-chip">FAQ</span>
        <h2 id="faq-title">{{ $page['faq']['title'] }}</h2>
        <p class="section-lead">{{ $page['faq']['lead'] }}</p>

        <div class="faq-list">
            @foreach ($page['faq']['items'] as $faqItem)
                <details class="faq-item" @if ($loop->first) open @endif>
                    <summary>
                        <span>{{ $faqItem['question'] }}</span>
                    </summary>
                    <div class="faq-answer">
                        <p>{{ $faqItem['answer'] }}</p>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
