<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:24px; background-color:#f4f4f5; font-family:Arial, Helvetica, sans-serif; color:#18181b;">
    <table role="presentation" width="100%" style="max-width:560px; margin:0 auto; background-color:#ffffff; border-radius:12px; padding:24px;">
        <tr>
            <td>
                <h1 style="font-size:20px; margin:0 0 8px;">Hallo {{ $user->name }},</h1>
                <p style="font-size:14px; color:#52525b; margin:0 0 24px;">
                    hier ist deine tägliche Scrumly-Erinnerung.
                </p>

                @unless ($hasJournalEntryToday)
                    <div style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px; margin-bottom:24px;">
                        <strong style="font-size:14px;">Noch kein Tagebucheintrag für heute</strong>
                        <p style="font-size:13px; color:#3f3f46; margin:8px 0 12px;">
                            Nimm dir kurz Zeit für die drei Reflexionsfragen des Tages.
                        </p>
                        <a href="{{ route('journal.index') }}" style="font-size:13px; color:#2563eb;">Jetzt eintragen &rarr;</a>
                    </div>
                @endunless

                @foreach ($teams as $team)
                    @php
                        $overdueMembers = $team->teamMembers->filter(fn ($member) => $member->needsOneOnOneReminder());
                    @endphp

                    @if ($overdueMembers->isNotEmpty())
                        <div style="margin-bottom:16px;">
                            <strong style="font-size:14px;">1:1-Erinnerungen &middot; {{ $team->name }}</strong>
                            <ul style="font-size:13px; color:#3f3f46; margin:8px 0; padding-left:20px;">
                                @foreach ($overdueMembers as $member)
                                    <li style="margin-bottom:4px;">
                                        <a href="{{ route('team-members.show', $member) }}" style="color:#2563eb;">{{ $member->fullName() }}</a>
                                        &mdash; {{ $member->lastOneOnOneAt() ? 'zuletzt am '.$member->lastOneOnOneAt()->format('d.m.Y') : 'noch nie' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($team->actionItems->isNotEmpty())
                        <div style="margin-bottom:16px;">
                            <strong style="font-size:14px;">Offene Action Items aus Retros &middot; {{ $team->name }}</strong>
                            <p style="font-size:13px; color:#3f3f46; margin:8px 0;">
                                {{ $team->actionItems->count() }} offen &mdash;
                                <a href="{{ route('sprints.index', $team) }}" style="color:#2563eb;">ansehen &rarr;</a>
                            </p>
                        </div>
                    @endif
                @endforeach

                <p style="font-size:12px; color:#a1a1aa; margin-top:24px;">
                    &copy; {{ now()->year }} Scrumly
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
