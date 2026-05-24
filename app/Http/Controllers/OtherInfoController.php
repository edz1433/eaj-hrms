<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\FamilyBg;
use App\Models\EducBg;
use App\Models\Eligibility;
use App\Models\WorkExperience;
use App\Models\VoluntaryWork;
use App\Models\LearningDev;
use App\Models\OtherInfo;
use App\Models\InfoQuestion;
use App\Models\PdsReference;
use App\Models\GovId;

class OtherInfoController extends Controller
{
    public function getGuard()
    {
        if(\Auth::guard('web')->check()) {
            return 'web';
        } elseif(\Auth::guard('employee')->check()) {
            return 'employee';
        }
    }

    public function columnStat($empid){
        $familyBg = FamilyBg::firstOrCreate(['empid' => $empid]);
        $educBg = EducBg::firstOrCreate(['empid' => $empid]);
        $eligibility = Eligibility::where('empid', $empid)->get();
        $workexperience = WorkExperience::where('empid', $empid)->get();
        $voluntaryworks = VoluntaryWork::where('empid', $empid)->get();
        $learningdev = LearningDev::where('empid', $empid)->get();
        $otherinfo = OtherInfo::firstOrCreate(['empid' => $empid]);
        $infoquestion = InfoQuestion::firstOrCreate(['empid' => $empid]);
        $references = PdsReference::firstOrCreate(['empid' => $empid]);
        $govids= GovId::firstOrCreate(['empid' => $empid]);
        
        $columnstatus = [
            'colfamstat' => $familyBg->famhasAnyValue(),
            'coleducstat' => $educBg->educhasAnyValue(),
            'eligibility' => $eligibility,
            'workexperience' => $workexperience,
            'voluntaryworks' => $voluntaryworks,
            'learningdev' => $learningdev,
            'colotherinfo' => $otherinfo->otherinfoAnyValue(),
            'colinfoquestion' => $infoquestion->infoquestionValue(),
            'colreferences' => $references->referencesValue(),
            'colgovids' => $govids->govidsValue(),
        ];

        return $columnstatus;
    }

    public function otherInfo($id = null){
        $guard = $this->getGuard();
        $empid = pdsRouteEmployeeId($id, $guard);
        $employee = Employee::findOrFail($empid);
        $otherinfo = OtherInfo::firstOrCreate(['empid' => $employee->emp_ID]);
        $columnstatus = $this->columnStat($employee->emp_ID);

        return view("emp.other-info", compact('guard', 'empid', 'employee', 'otherinfo', 'columnstatus'));
    }

    public function updateChild(Request $request)
    {
        $request->validate([
            'empid' => 'required|integer',
            'skills_hob' => 'required|array',
            'recognition' => 'required|array',
            'mem_org' => 'required|array',
        ]);
    
        $empid = $request->input('empid');        
        $employee = Employee::find($empid);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $otherinfo = OtherInfo::firstOrCreate(['empid' => $employee->emp_ID]);
    
        $skillsHob = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('skills_hob'));
        $recognition = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('recognition'));
        $memOrg = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('mem_org'));
    
        if (count($skillsHob) !== count($recognition) || count($skillsHob) !== count($memOrg)) {
            return response()->json(['success' => false, 'message' => 'Mismatch between fields.']);
        }
    
        $otherinfo->update([
            'skills_hob' => implode(',', $skillsHob),
            'recognition' => implode(',', $recognition),
            'mem_org' => implode(',', $memOrg),
        ]);
    
        return response()->json(['success' => true]);
    }    
    
    public function otherInfoUpdate(Request $request){
        $employee = Employee::find($request->id);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $otherinfo = OtherInfo::firstOrCreate(['empid' => $employee->emp_ID]);
        $column = $request->column;
        $value = $request->value;

        $otherinfo->update([
            $column => $value,
        ]);
        
        return response()->json(['success' => true]);
    }

    public function otherInfoUpdateArray(Request $request)
    {
        $request->validate([
            'empid' => 'required|integer',
            'skills_hob' => 'required|array',
            'recognition' => 'required|array',
            'mem_org' => 'required|array',
            'skills_hob.*' => 'nullable|string',
            'recognition.*' => 'nullable|string',
            'mem_org.*' => 'nullable|string',
        ]);
    
        $empid = $request->input('empid'); 
        $employee = Employee::find($empid);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $otherinfo = OtherInfo::firstOrCreate(['empid' => $employee->emp_ID]);
    
        $skills_hob = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('skills_hob'));
        $recognition = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('recognition'));
        $mem_org = array_map(fn($value) => trim(str_replace(',', '', $value)), $request->input('mem_org'));
    
        $otherinfo->update([
            'skills_hob' => implode(',', $skills_hob),
            'recognition' => implode(',', $recognition),
            'mem_org' => implode(',', $mem_org),
        ]);
        
        return response()->json(['success' => true]);
    }
    
}


