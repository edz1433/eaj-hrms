<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Office;
use App\Models\Employee;

class OfficeController extends Controller
{
    public function getGuaard()
    {
        if(\Auth::guard('web')->check()) {
            return 'web';
        } elseif(\Auth::guard('employee')->check()) {
            return 'employee';
        }
    }
    
    public function officeList(Request $request) {
        $guard = $this->getGuaard();
        [$office, $stats] = $this->officeDirectoryData($request);
        $employee = Employee::where('emp_status', 1)->orderBy('lname')->orderBy('fname')->get();

        return view("offdept.officelist", compact('office', 'employee', 'guard', 'stats'));
    }

    public function officeCreate(Request $request){

        $validator = Validator::make($request->all(), [
            'OfficeName'=>'required',
            'OfficeAbbreviation'=>'required',
            'office_head_id'=>'nullable',
            'oic_id'=>'nullable',
            'GroupBy'=>'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        else{
            $select = Office::where('office_name', $request->OfficeName)->exists();
            if ($select) {
                return redirect()->back()->with('error', 'Office Already Exist!');
            }
            else
            {
                $query = Office::insert([
                    'office_name'=>$request->input('OfficeName'),
                    'office_abbr'=>$request->input('OfficeAbbreviation'),
                    'office_head_id'=>$request->input('office_head_id'),
                    'oic_id'=>$request->input('oic_id'),
                ]);
                
                return redirect()->back()->with('success', 'Office Added Successfully'); 
            }
        }
    }

    public function officeEdit(Request $request, $id)
    {
        $guard = $this->getGuaard();
        $employee = Employee::where('emp_status', 1)->orderBy('lname')->orderBy('fname')->get();
        [$office, $stats] = $this->officeDirectoryData($request);
 
        $offEdit = Office::find($id);

        return view("offdept.officelist", compact('offEdit', 'office', 'employee', 'guard', 'stats'));
    }
    
    public function officeUpdate(Request $request){
        $validator = Validator::make($request->all(), [
            'OfficeName'=>'required',
            'OfficeAbbreviation'=>'required',
            'office_head_id'=> 'nullable',
            'oic_id' => 'nullable',
            'GroupBy' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        else{
            $select = Office::where('office_name', $request->OfficeName)->where('id', '!=', $request->oid)->exists();
            if ($select) {
                return redirect()->back()->with('success', 'Office Already Exist!');
            }
            else
            {
                $update = [
                    'office_name'=>$request->input('OfficeName'),
                    'office_abbr'=>$request->input('OfficeAbbreviation'),
                    'office_head_id'=>$request->input('office_head_id'),
                    'oic_id'=>$request->input('oic_id'),
                ];
                Office::where('id', $request->oid)->update($update);

                return redirect()->back()->with('success', 'Office Updated Successfully');
            }
        }
    }

    public function officeDelete($id){
        $office = Office::find($id);
        $office->delete();

        return response()->json([
            'status'=>200,
            'message'=>"Deleted Successfully",
        ]);
    }

    private function officeDirectoryData(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = Office::with(['head:id,emp_ID,fname,lname,position', 'oic:id,emp_ID,fname,lname,position'])
            ->withCount(['employees as employee_count' => function ($q) {
                $q->where('emp_status', 1);
            }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('office_name', 'like', "%{$search}%")
                  ->orWhere('office_abbr', 'like', "%{$search}%")
                  ->orWhere('office_code', 'like', "%{$search}%")
                  ->orWhereHas('head', function ($head) use ($search) {
                      $head->where('fname', 'like', "%{$search}%")
                           ->orWhere('lname', 'like', "%{$search}%")
                           ->orWhere('emp_ID', 'like', "%{$search}%");
                  })
                  ->orWhereHas('oic', function ($oic) use ($search) {
                      $oic->where('fname', 'like', "%{$search}%")
                          ->orWhere('lname', 'like', "%{$search}%")
                          ->orWhere('emp_ID', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->input('staffing') === 'with_head') {
            $query->whereNotNull('office_head_id');
        }

        if ($request->input('staffing') === 'without_head') {
            $query->whereNull('office_head_id');
        }

        $office = $query->orderBy('office_name')->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Office::count(),
            'with_head' => Office::whereNotNull('office_head_id')->count(),
            'without_head' => Office::whereNull('office_head_id')->count(),
            'with_oic' => Office::whereNotNull('oic_id')->count(),
            'assigned_employees' => Employee::where('emp_status', 1)->whereNotNull('emp_dept')->count(),
        ];

        return [$office, $stats];
    }
}
