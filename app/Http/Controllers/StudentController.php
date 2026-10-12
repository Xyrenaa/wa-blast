<?php

namespace App\Http\Controllers;

use App\Exports\StudentTemplateExport;
use App\Imports\StudentsImport;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $r)
    {
        $query = Student::query()
            ->when($r->q, fn ($x, $s) => $x->where(fn ($w) => $w->where('nama', 'like', "%$s%")->orWhere('nim', 'like', "%$s%")))
            ->when($r->angkatan, fn ($x, $v) => $x->where('angkatan', $v))
            ->when($r->jurusan, fn ($x, $v) => $x->where('jurusan', $v))
            ->when(in_array($r->status, ['0', '1'], true), fn ($x) => $x->where('is_active', $r->status === '1'))
            ->when($r->wa === 'invalid', fn ($x) => $x->waInvalid());

        return view('students.index', [
            'students' => $query->orderBy('nama')->paginate(10)->withQueryString(),
            'angkatanList' => Student::select('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'jurusanList' => Student::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
            'total' => Student::count(),
            'invalid' => Student::waInvalid()->count(),
        ]);
    }

    public function create()
    {
        return view('students.form', ['student' => new Student(['is_active' => true])]);
    }

    public function store(Request $r)
    {
        $data = $r->validate($this->rules());
        $data['is_active'] = $r->boolean('is_active');
        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        return view('students.form', ['student' => $student]);
    }

    public function update(Request $r, Student $student)
    {
        $data = $r->validate($this->rules($student));
        $data['is_active'] = $r->boolean('is_active');
        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Data mahasiswa diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return back()->with('success', 'Mahasiswa dihapus.');
    }

    public function import(Request $r)
    {
        $r->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120']]);

        $import = new StudentsImport();
        try {
            Excel::import($import, $r->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'File tidak bisa dibaca: ' . $e->getMessage()]);
        }

        $sheet = $import->sheet;

        return back()->with('import', ['done' => $sheet->done, 'skipped' => $sheet->skipped, 'mapping' => $sheet->mapping]);
    }

    public function template()
    {
        return Excel::download(new StudentTemplateExport(), 'template-mahasiswa.xlsx');
    }

    private function rules(?Student $student = null): array
    {
        return [
            'nim' => ['required', 'string', 'max:30', Rule::unique('students', 'nim')->ignore($student)],
            'nama' => ['required', 'string', 'max:255'],
            'angkatan' => ['required', 'integer', 'between:2000,2100'],
            'jurusan' => ['required', 'string', 'max:255'],
            'no_wa' => ['nullable', 'string', 'max:25'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
