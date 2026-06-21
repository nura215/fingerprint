<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ScheduleController extends BaseCrudController
{
    protected string $modelClass = Schedule::class;

    protected string $routePrefix = 'admin.schedules';

    protected string $title = 'Schedule Perkuliahan';

    protected string $description = 'Kelola schedules perkuliahan dan validasi bentrok rooms.';

    protected string $viewNamespace = 'admin.aktivitas.jadwal';

    protected string $sectionTitle = 'Aktivitas';

    protected array $with = ['academicYear', 'lecturer', 'class', 'subject', 'room'];

    protected array $columns = [
        ['label' => 'Tahun Akademik', 'key' => 'academicYear.year'],
        ['label' => 'Hari', 'key' => 'day_label'],
        ['label' => 'Jam', 'key' => 'time_range'],
        ['label' => 'Kelas', 'key' => 'class.code'],
        ['label' => 'Mata Kuliah', 'key' => 'subject.name'],
        ['label' => 'room', 'key' => 'room.name'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $detailFields = [
        ['label' => 'Tahun Akademik', 'key' => 'academicYear.year'],
        ['label' => 'Semester', 'key' => 'academicYear.semester'],
        ['label' => 'lecturer', 'key' => 'lecturer.name'],
        ['label' => 'Kelas', 'key' => 'class.name'],
        ['label' => 'Mata Kuliah', 'key' => 'subject.name'],
        ['label' => 'room', 'key' => 'room.name'],
        ['label' => 'Hari', 'key' => 'day_label'],
        ['label' => 'Jam', 'key' => 'time_range'],
        ['label' => 'Status', 'key' => 'status', 'badge' => true],
    ];

    protected array $searchColumns = ['day', 'status'];

    protected string $orderBy = 'day';

    protected string $orderDirection = 'asc';

    public function index(Request $request): View
    {
        $weekStart = CarbonImmutable::parse($request->input('week', now()))->startOfWeek();
        $weekEnd = $weekStart->addDays(6);

        $items = $this->baseQuery()
            ->when($request->filled('day'), fn (Builder $query) => $query->where('day', $request->input('day')))
            ->when($request->filled('room_id'), fn (Builder $query) => $query->where('room_id', $request->integer('room_id')))
            ->when($request->filled('lecturer_id'), fn (Builder $query) => $query->where('lecturer_id', $request->integer('lecturer_id')))
            ->when($request->filled('class_id'), fn (Builder $query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('academic_year_id'), fn (Builder $query) => $query->where('academic_year_id', $request->integer('academic_year_id')))
            ->where('status', 'active')
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        return view('admin.aktivitas.jadwal.daftar', [
            'items' => $items,
            'schedulesByDay' => $items->groupBy('day'),
            'routePrefix' => $this->routePrefix,
            'rooms' => Room::where('status', 'active')->orderBy('name')->get(),
            'lecturers' => Lecturer::where('status', 'active')->orderBy('name')->get(),
            'classes' => AcademicClass::where('status', 'active')->orderBy('code')->get(),
            'academicYears' => AcademicYear::orderByDesc('year')->get(),
            'dayOptions' => $this->dayOptions(),
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => collect($this->dayOptions())->keys()->values(),
            'timeSlots' => range(7, 17),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['status' => 'active']);
        $validated = $this->validatedSchedule($request);
        $item = $this->modelClass::create($this->prepareData($validated, true));
        AuditLogger::log('create', $item, null, $item->fresh()?->toArray());

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', $this->title.' berhasil ditambahkan.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $item = $this->findModel($id);
        $validated = $this->validatedSchedule($request, $item);
        $oldData = $item->getOriginal();
        $item->update($this->prepareData($validated, false));
        AuditLogger::log('update_Schedule', $item, $oldData, $item->fresh()?->toArray());

        return redirect()
            ->route($this->routePrefix.'.index')
            ->with('success', $this->title.' berhasil diperbarui.');
    }

    protected function rules(?Model $item = null): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'day' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function resolvedFormFields(): array
    {
        return [
            ['name' => 'academic_year_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'required' => true, 'options' => AcademicYear::orderByDesc('year')->get()->mapWithKeys(fn ($year) => [$year->id => $year->year.' - '.ucfirst($year->semester)])->all()],
            ['name' => 'lecturer_id', 'label' => 'lecturer', 'type' => 'select', 'required' => true, 'options' => Lecturer::where('status', 'active')->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'class_id', 'label' => 'Kelas', 'type' => 'select', 'required' => true, 'options' => AcademicClass::where('status', 'active')->orderBy('code')->get()->mapWithKeys(fn ($class) => [$class->id => $class->code.' - '.$class->name])->all()],
            ['name' => 'subject_id', 'label' => 'Mata Kuliah', 'type' => 'select', 'required' => true, 'options' => Subject::where('status', 'active')->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'room_id', 'label' => 'room', 'type' => 'select', 'required' => true, 'options' => Room::where('status', 'active')->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'day', 'label' => 'Hari', 'type' => 'select', 'required' => true, 'options' => $this->dayOptions()],
            ['name' => 'start_time', 'label' => 'Jam Mulai', 'type' => 'time', 'required' => true],
            ['name' => 'end_time', 'label' => 'Jam Selesai', 'type' => 'time', 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
        ];
    }

    private function validatedSchedule(Request $request, ?Model $item = null): array
    {
        $validator = Validator::make($request->all(), $this->rules($item));

        $validator->after(function ($validator) use ($request, $item) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $exists = Schedule::query()
                ->where('room_id', $request->input('room_id'))
                ->where('day', $request->input('day'))
                ->where('status', 'active')
                ->when($item, fn ($query) => $query->whereKeyNot($item->getKey()))
                ->where('start_time', '<', $request->input('end_time'))
                ->where('end_time', '>', $request->input('start_time'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_time', 'Jadwal bentrok dengan jadwal aktif lain pada ruangan dan hari yang sama.');
            }
        });

        return $validator->validate();
    }

    private function dayOptions(): array
    {
        return [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
    }
}







