<div
    x-data
    x-on:team-switched.window="
        let match = window.location.pathname.match(/^\/teams\/\d+(\/.*)?$/);
        let target = match ? '/teams/' + $event.detail.teamId + (match[1] || '') : '/teams/' + $event.detail.teamId + '/sprints';
        Livewire.navigate(target);
    "
>
    @if ($teams->isNotEmpty())
        <flux:select wire:model.live="currentTeamId" size="sm">
            @foreach ($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }}</option>
            @endforeach
        </flux:select>
    @endif
</div>
