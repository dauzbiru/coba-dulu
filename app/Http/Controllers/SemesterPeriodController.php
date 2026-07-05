<?php

namespace App\Http\Controllers;

use App\Models\SemesterPeriod;
use Illuminate\Http\Request;

class SemesterPeriodController extends Controller
{
    public function index()
    {
        $periods = SemesterPeriod::orderByDesc('year')->orderByDesc('start_month')->get();
        return view('semester-periods.index', compact('periods'));
    }

    public function create()
    {
        return view('semester-periods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2099',
            'start_month' => 'required|integer|min:1|max:12',
            'end_month' => 'required|integer|min:1|max:12|gte:start_month',
        ]);

        SemesterPeriod::create($data);

        return redirect('/semester-periods')->with('success', 'Periode semester berhasil ditambahkan.');
    }

    public function edit(SemesterPeriod $semesterPeriod)
    {
        return view('semester-periods.edit', compact('semesterPeriod'));
    }

    public function update(Request $request, SemesterPeriod $semesterPeriod)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2099',
            'start_month' => 'required|integer|min:1|max:12',
            'end_month' => 'required|integer|min:1|max:12|gte:start_month',
        ]);

        $semesterPeriod->update($data);

        return redirect('/semester-periods')->with('success', 'Periode semester berhasil diperbarui.');
    }

    public function destroy(SemesterPeriod $semesterPeriod)
    {
        $semesterPeriod->delete();

        return redirect('/semester-periods')->with('success', 'Periode semester berhasil dihapus.');
    }
}
