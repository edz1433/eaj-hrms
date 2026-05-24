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

class PdsReferencesController extends Controller
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

    public function references($id = null){
        $guard = $this->getGuard();
        $empid = pdsRouteEmployeeId($id, $guard);
        $employee = Employee::findOrFail($empid);
        $references = PdsReference::firstOrCreate(['empid' => $employee->emp_ID]);
        $columnstatus = $this->columnStat($employee->emp_ID);

        return view("emp.references", compact('guard', 'empid', 'employee', 'references', 'columnstatus'));
    }

    public function update(Request $request)
    {
        $empid = $request->input('empid'); 
        $columnWithSuffix = $request->input('column');
        $index = (int) $request->input('index');
        $value = trim(str_replace(';', '', (string) $request->input('value')));
    
        $column = preg_replace('/_\d+$/', '', $columnWithSuffix);
        if (!in_array($column, ['refname', 'refadd', 'reftelno'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid field.'], 422);
        }
    
        $employee = Employee::find($empid);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $references = PdsReference::firstOrCreate(['empid' => $employee->emp_ID]);
    
        if ($references) {
            $currentValue = $references->$column;
            $valuesArray = explode(';', $currentValue); // Use semicolon as separator
            $valuesArray = array_pad($valuesArray, max($index + 1, 3), '');
    
            $valuesArray[$index] = $value;
    
            $newValue = implode(';', $valuesArray); // Use semicolon as separator
    
            $references->$column = $newValue;
    
            $references->save();
    
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false], 404);
    }
       
}


