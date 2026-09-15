@if (isset($insights) && count($insights))
    <div class="card">
        <h3 style="margin-top:0;">Needs your attention</h3>
        <div style="display:flex; flex-direction:column; gap:8px;">
            @foreach ($insights as $insight)
                @php
                    $colors = $insight['level'] === 'danger'
                        ? ['bg' => '#fbe7e9', 'border' => '#dc3545', 'text' => '#7a1620']
                        : ['bg' => '#fff8e1', 'border' => '#ffc107', 'text' => '#6b5300'];
                @endphp
                <div style="background:{{ $colors['bg'] }}; border-left:4px solid {{ $colors['border'] }}; color:{{ $colors['text'] }}; padding:10px 14px; border-radius:6px; font-size:0.88rem;">
                    {{ $insight['message'] }}
                </div>
            @endforeach
        </div>
    </div>
@endif
