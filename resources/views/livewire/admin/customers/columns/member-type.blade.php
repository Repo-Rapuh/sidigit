<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $row->member_type?->badgeClasses() }}">
    {{ $row->member_type?->label() ?? '-' }}
</span>
