<div>
    @if (filled($duplicateMessage))
        <p role="alert" style="color: #dc2626; font-size: 14px;">
            {{ $duplicateMessage }}
        </p>
    @endif
</div>