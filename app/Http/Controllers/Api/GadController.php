<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class GadController extends Controller
{
    public function genderCount() {
        $allcampus = Employee::query()
            ->where('stat_1', 1)
            ->select('sex')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('sex')
            ->get();

        $byCampus = Employee::query()
            ->where('employees.stat_1', 1)
            ->leftJoin('camp_branches', 'employees.camp_id', '=', 'camp_branches.id')
            ->selectRaw('COALESCE(camp_branches.name, "Unassigned") as campus_name')
            ->addSelect('sex')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw('COALESCE(camp_branches.name, "Unassigned"), sex')
            ->get();

        return response()->json([
            'allcampus' => $allcampus,
            'bycampus' => $byCampus
        ]);
    }
}
