@php
    use App\Support\InstituteGrades;

    /** @var \App\Models\Institute $institute */
    $gradeEntries = $institute->gradesOfferedEntries();
@endphp

@if($gradeEntries !== [])
    <div class="table-responsive school-grades-table-wrap">
        <table class="table table-sm school-grades-table align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col">Class</th>
                    <th scope="col" class="text-center">Sections</th>
                    <th scope="col" class="text-center">Students / section</th>
                    <th scope="col" class="text-end">Total students</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gradeEntries as $entry)
                    @php $total = InstituteGrades::totalStudents($entry); @endphp
                    <tr>
                        <td>{{ $entry['class'] }}</td>
                        <td class="text-center">{{ $entry['sections'] ?? '—' }}</td>
                        <td class="text-center">{{ $entry['students_per_section'] ?? '—' }}</td>
                        <td class="text-end">{{ $total ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
