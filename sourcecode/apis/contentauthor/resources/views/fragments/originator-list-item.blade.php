<li class="list-group-item clear-fix">
    <span class="pull-right">
        <button type="button" class="btn btn-xs btn-danger attr-remove" aria-label="{{ trans('common.remove') }}">&times;</button>
    </span>
    {{ $name }}
    <small>{{ $role }}</small>
    <input type="hidden" name="originators[{{ $index }}][name]" value="{{ $name }}">
    <input type="hidden" name="originators[{{ $index }}][role]" value="{{ $role }}">
</li>
