<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\Employee;
use App\Models\Status;
use App\Models\Office;
use App\Models\Qualification;
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
use App\Models\Device;
use App\Models\OfficialTime;
use App\Models\CampBranch;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    private function enabledStatuses(Setting $setting): \Illuminate\Support\Collection
    {
        $enabledTypes = $setting->emp_types ?? [];

        if (empty($enabledTypes)) {
            return Status::active()->ordered()->get();
        }

        $preferredSector = ($setting->sector ?? null) === 'government' ? 'government' : 'private';

        $statusNamesByType = [
            'permanent'    => $preferredSector === 'government' ? ['Regular', 'Plantilla', 'Permanent'] : ['Regular'],
            'temporary'    => ['Temporary'],
            'coterminous'  => ['Coterminous'],
            'casual'       => ['Casual'],
            'contractual'  => ['Contractual', 'Project-based', 'Seasonal'],
            'job_order'    => ['Job Order'],
            'part_time'    => ['Part-time', 'Full-time'],
            'probationary' => ['Probationary'],
        ];

        $statusNames = collect($enabledTypes)
            ->flatMap(fn($type) => $statusNamesByType[$type] ?? [])
            ->unique()
            ->values();

        if ($statusNames->isEmpty()) {
            return collect();
        }

        $statuses = Status::active()
            ->whereIn('status_name', $statusNames)
            ->ordered()
            ->get();

        return $statuses
            ->groupBy('status_name')
            ->map(fn($group) => $group->firstWhere('sector', $preferredSector) ?? $group->firstWhere('sector', 'both') ?? $group->first())
            ->values()
            ->sortBy('sort_order')
            ->values();
    }

    private function enabledStatusIds(): array
    {
        return $this->enabledStatuses(Setting::singleton())->pluck('id')->all();
    }

    private function generateEmployeeId(): string
    {
        $existingIds = Employee::where('emp_ID', 'not like', '%-%')
            ->lockForUpdate()
            ->pluck('emp_ID');

        $lastNumber = $existingIds
            ->map(function ($employeeId) {
                if (preg_match('/^(?:EMP)?(\d+)$/', $employeeId, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->max() ?? 0;

        do {
            $employeeId = str_pad((string) ++$lastNumber, 4, '0', STR_PAD_LEFT);
        } while (Employee::where('emp_ID', $employeeId)->exists());

        return $employeeId;
    }

    private function previewEmployeeId(): string
    {
        $lastNumber = Employee::where('emp_ID', 'not like', '%-%')
            ->pluck('emp_ID')
            ->map(function ($employeeId) {
                if (preg_match('/^(?:EMP)?(\d+)$/', $employeeId, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->max() ?? 0;

        return str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function employeePayload(Employee $employee): array
    {
        $location = $employee->camp_id ? CampBranch::find($employee->camp_id) : null;
        $office = $employee->emp_dept ? Office::find($employee->emp_dept) : null;
        $status = $employee->emp_status ? Status::find($employee->emp_status) : null;

        return [
            'id' => $employee->id,
            'emp_ID' => $employee->emp_ID,
            'fname' => $employee->fname,
            'mname' => $employee->mname,
            'lname' => $employee->lname,
            'position' => $employee->position,
            'emp_status' => $employee->emp_status,
            'status_name' => $status?->status_name,
            'emp_dept' => $employee->emp_dept,
            'office_name' => $office?->office_name,
            'camp_id' => $employee->camp_id,
            'location_abbr' => $location ? ($location->abbr ?: $location->name) : null,
            'pds_token' => shortEncrypt((string) $employee->id),
            'email' => $employee->email,
            'sex' => $employee->sex,
            'civil_status' => $employee->civil_status,
            'bdate' => $employee->bdate,
            'age' => $employee->age,
            'stat_1' => $employee->stat_1,
        ];
    }

    // Labels may say Campus/Site/Plant, but source is CampBranch.
    private function resolveLocations(object $locCtx): \Illuminate\Support\Collection
    {
        if (!($locCtx->enabled ?? true)) {
            return collect();
        }

        return CampBranch::orderBy('name')->get()->map(fn($b) => (object)[
            'id'   => $b->id,
            'name' => $b->name,
            'abbr' => $b->abbr ?? $b->name,
        ]);
    }

    private function attachLocationData(\Illuminate\Pagination\LengthAwarePaginator $employees, \Illuminate\Support\Collection $locations): void
    {
        if ($locations->isEmpty()) {
            foreach ($employees as $emp) {
                $emp->location_abbr = null;
            }
            return;
        }

        $map = $locations->keyBy('id');
        foreach ($employees as $emp) {
            $loc = $map->get($emp->camp_id);
            $emp->location_abbr = $loc ? ($loc->abbr ?: $loc->name) : '-';
        }
    }

    public function employees(Request $request)
    {
        $setting   = Setting::singleton();
        $locCtx    = $setting->locationContext();
        $locations = $this->resolveLocations($locCtx);
        $statuses  = $this->enabledStatuses($setting);
        $statusIds = $statuses->pluck('id');
        $nextEmpID = $this->previewEmployeeId();

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $query = Employee::leftJoin('offices', 'employees.emp_dept', '=', 'offices.id')
            ->leftJoin('statuses', 'employees.emp_status', '=', 'statuses.id')
            ->select(
                'employees.id',
                'employees.emp_ID',
                'employees.fname',
                'employees.lname',
                'employees.mname',
                'employees.suffix',
                'employees.position',
                'employees.emp_status',
                'employees.emp_dept',
                'employees.camp_id',
                'employees.date_hired',
                'employees.profile',
                'employees.stat_1',
                'employees.org_email',
                'offices.office_name',
                'offices.office_abbr',
                'statuses.status_name',
                'statuses.id as status_id'
            );

        if ($statusIds->isNotEmpty()) {
            $query->whereIn('employees.emp_status', $statusIds);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.lname', 'like', "%{$search}%")
                  ->orWhere('employees.fname', 'like', "%{$search}%")
                  ->orWhere('employees.emp_ID', 'like', "%{$search}%")
                  ->orWhere('employees.position', 'like', "%{$search}%")
                  ->orWhere('offices.office_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('employees.emp_status', $status);
        }

        if ($dept = $request->input('dept')) {
            $query->where('employees.emp_dept', $dept);
        }

        if (($locCtx->enabled ?? true) && $location = $request->input('location')) {
            $query->where('employees.camp_id', $location);
        }

        $employees = $query->orderBy('employees.lname')->paginate($perPage)->withQueryString();

        $this->attachLocationData($employees, $locations);

        $totalQuery = Employee::query()
            ->when($statusIds->isNotEmpty(), fn($q) => $q->whereIn('emp_status', $statusIds));

        $byStatus = Employee::leftJoin('statuses', 'employees.emp_status', '=', 'statuses.id')
            ->select('statuses.id', 'statuses.status_name', DB::raw('COUNT(*) as count'))
            ->when($statusIds->isNotEmpty(), fn($q) => $q->whereIn('statuses.id', $statusIds))
            ->groupBy('statuses.id', 'statuses.status_name')
            ->get();

        $stats = [
            'total'         => (clone $totalQuery)->count(),
            'by_status'     => $byStatus,
            'new_this_year' => (clone $totalQuery)->whereYear('date_hired', now()->year)->count(),
        ];

        $offices  = Office::where('office_name', 'not like', '%UNKNOWN%')
                          ->where('office_name', 'not like', '%CAMPUS%')
                          ->get();

        return view('emp.emplist', compact('employees', 'stats', 'statuses', 'offices', 'locations', 'nextEmpID'));
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Quick-edit employee from list modal Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    public function empUpdate(Request $request)
    {
        $enabledStatusIds = $this->enabledStatusIds();

        $request->validate([
            'id'         => 'required|integer|exists:employees,id',
            'lname'      => 'required|string|max:100',
            'fname'      => 'required|string|max:100',
            'mname'      => 'nullable|string|max:100',
            'camp_id'    => 'nullable|integer|exists:camp_branches,id',
            'emp_dept'   => 'required|integer|exists:offices,id',
            'emp_status' => ['required', 'integer', Rule::in($enabledStatusIds)],
            'position'   => 'required|string|max:150',
        ]);

        $employee = Employee::findOrFail($request->id);
        $employee->update([
            'lname'      => strtoupper($request->lname),
            'fname'      => strtoupper($request->fname),
            'mname'      => $request->mname ? strtoupper($request->mname) : null,
            'camp_id'    => $request->camp_id ?: null,
            'emp_dept'   => $request->emp_dept,
            'emp_status' => $request->emp_status,
            'position'   => $request->position,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'employee' => $this->employeePayload($employee->fresh()),
            ]);
        }

        return redirect()->back()->with('success', 'Employee updated successfully.');
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

    public function emp_list()
    {
        $setting = Setting::singleton();

        $offices = Office::where('office_name', 'not like', '%UNKNOWN%')
                         ->where('office_name', 'not like', '%CAMPUS%')
                         ->get();

        $stat = $this->enabledStatuses($setting);
        $statusIds = $stat->pluck('id');

        $employee = Employee::leftJoin('offices', 'employees.emp_dept', '=', 'offices.id')
            ->leftJoin('statuses', 'employees.emp_status', '=', 'statuses.id')
            ->select(
                'employees.id',
                'employees.emp_ID',
                'employees.position',
                'employees.email',
                'employees.date_hired',
                'employees.lname',
                'employees.fname',
                'employees.mname',
                'employees.emp_dept',
                'employees.emp_status',
                'offices.office_name',
                'statuses.status_name',
                'employees.stat_1'
            )
            ->when($statusIds->isNotEmpty(), fn($q) => $q->whereIn('employees.emp_status', $statusIds))
            ->get()
            ->map(function ($item, $key) {
                $item->ids = $key + 1;
                return $item;
            });

        $quali = Qualification::all();

        return view("emp.emplist", compact('employee', 'offices', 'stat', 'quali'));
    }
    

    public function empAdd(){
        $regions = Region::all();
        $setting = Setting::singleton();
        $locCtx = $setting->locationContext();
        $locations = $this->resolveLocations($locCtx);
        $offices = Office::where('office_name', 'not like', '%UNKNOWN%')->get();
        $stat = $this->enabledStatuses($setting);
        $statusIds = $stat->pluck('id');
        $supervisor = Employee::when($statusIds->isNotEmpty(), fn($q) => $q->whereIn('emp_status', $statusIds))->get();
        $quali = Qualification::all();
        $nextEmpID = $this->previewEmployeeId();

        return view("emp.empadd", compact('offices', 'stat', 'quali', 'regions', 'supervisor', 'locations', 'nextEmpID'));
    }

    public function empCreate(Request $request)
    {
        $enabledStatusIds = $this->enabledStatusIds();

        $validated = $request->validate([
            'lname'      => 'required|string|max:100',
            'fname'      => 'required|string|max:100',
            'mname'      => 'required|string|max:100',
            'camp_id'    => 'nullable|integer|exists:camp_branches,id',
            'emp_dept'   => 'required|integer|exists:offices,id',
            'emp_status' => ['required', 'integer', Rule::in($enabledStatusIds)],
            'position'   => 'required|string|max:150',
            'item_no'    => 'nullable|string|max:100',
            'bdate'      => 'nullable|date',
            'age'        => 'nullable|integer|min:0|max:150',
            'sex'        => 'required|string|in:Male,Female',
            'civil_status' => 'nullable|string|max:50',
            'org_email'  => 'nullable|email|max:150',
        ]);

        $existingEmployee = Employee::where('lname', $request->lname)
                ->where('fname', $request->fname)
                ->first();

        if ($existingEmployee) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already exists.',
                    'errors' => ['employee' => ['Employee already exists.']],
                ], 422);
            }

            return redirect()->back()->withErrors(['Employee already exists.']);
        }


        if ($request->filled('ProfileImage')) {
            $base64Image = $request->input('ProfileImage');
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            $fileName = date('Ymdhis');
            $fileExtension = '.jpg';
            $fullFileName = $fileName . $fileExtension;
            $file = public_path('Profile/Employee/' . $fullFileName);
            file_put_contents($file, $imageData);
        } elseif ($request->hasFile('ProfileImage1')) {
            $profileImage1 = $request->file('ProfileImage1');
            $fileName = date('Ymdhis') . '.' . $profileImage1->getClientOriginalExtension();
            $profileImage1->move(public_path('Profile/Employee/'), $fileName);
            $fullFileName = $fileName;
        } 
        else {
            $fullFileName = null;
        }

        $age = $request->filled('bdate') ? Carbon::parse($request->bdate)->age : $request->age;

        $employee = DB::transaction(function () use ($request, $fullFileName, $age) {
            $newEmpID = $this->generateEmployeeId();

            $employee = new Employee([
                'profile' => $fullFileName,
                'date_hired' => $request->date_hired,
                'lname' => strtoupper($request->lname),
                'fname' => strtoupper($request->fname),
                'mname' => strtoupper($request->mname),
                'suffix' => $request->suffix,
                'position' => $request->position,
                'emp_ID' => $newEmpID,
                'emp_status' => $request->emp_status,
                'emp_dept' => $request->emp_dept,
                'camp_id' => $request->filled('camp_id') ? $request->camp_id : null,
                'item_no' => $request->item_no,
                'prefix' => $request->prefix,
                'bdate' => $request->bdate,
                'age' => $age,
                'b_place' => $request->b_place,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'height_cm' => $request->height_cm,
                'weight_kg' => $request->weight_kg,
                'weight_lb' => $request->weight_lb,
                'b_type' => $request->b_type,
                'gsis' => $request->gsis,
                'pagibig' => $request->pagibig,
                'philhealth' => $request->philhealth,
                'sss' => $request->sss,
                'tin' => $request->tin,
                'citizenship' => $request->citizenship,
                'c_category' => $request->c_category,
                'country' => $request->country,
                'telephone' => $request->telephone,
                'org_email' => $request->org_email,
                'email' => $request->org_email ?: $request->email,
                'mobile' => $request->mobile,
                'add_block' => $request->add_block,
                'add_street' => $request->add_street,
                'add_village' => $request->add_village,
                'add_brgy' => $request->add_brgy,
                'add_city' => $request->add_city,
                'add_prov' => $request->add_prov,
                'add_region' => $request->add_region,
                'add_zcode' => $request->add_zcode,
                'padd_block' => $request->padd_block,
                'padd_street' => $request->padd_street,
                'padd_village' => $request->padd_village,
                'padd_brgy' => $request->padd_brgy,
                'padd_city' => $request->padd_city,
                'padd_prov' => $request->padd_prov,
                'padd_region' => $request->padd_region,
                'padd_zcode' => $request->padd_zcode,
                'special_pl' => 0,
                'solo_pl' => 0,
                'password' => $newEmpID,
            ]);

            $employee->save();

            $models = ['FamilyBg', 'EducBg', 'OtherInfo', 'InfoQuestion', 'PdsReference', 'GovId', 'OfficialTime'];

            foreach ($models as $model) {
                $modelClass = "App\\Models\\{$model}";

                if (class_exists($modelClass)) {
                    $modelClass::create([
                        'empid' => $newEmpID,
                    ]);
                } else {
                    throw new \Exception("Model {$modelClass} not found.");
                }
            }

            return $employee->fresh();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee added successfully.',
                'employee' => $this->employeePayload($employee),
                'next_emp_id' => $this->previewEmployeeId(),
            ]);
        }

        return redirect()->back()->with('success', 'Employee added successfully.');
    }

    public function updateProfilePicture(Request $request, $id)
    {
        if (auth()->guard('employee')->check() && (int) $id !== (int) auth()->guard('employee')->id()) {
            return response()->json(['error' => 'You can only update your own profile picture.'], 403);
        }

        $request->validate([
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $uploadedProfile = $request->file('profileImage') ?? $request->file('profile');

        if (!$uploadedProfile) {
            return response()->json(['error' => 'Please choose a valid profile image.'], 422);
        }
    
        $employee = Employee::find($id);
    
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }
    
        $profileImagePath = public_path('Profile/Employee/');

        if (!is_dir($profileImagePath)) {
            mkdir($profileImagePath, 0755, true);
        }

        $placeholderFiles = ['default.png', 'default-male.png', 'default-female.png'];
        if ($employee->profile && !in_array(strtolower($employee->profile), $placeholderFiles, true) && file_exists($profileImagePath . $employee->profile)) {
            unlink($profileImagePath . $employee->profile);
        }

        $fileName = 'emp-' . $employee->id . '-' . Str::uuid() . '.' . $uploadedProfile->getClientOriginalExtension();
        $uploadedProfile->move($profileImagePath, $fileName);

        $employee->profile = $fileName;
        $employee->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'profile' => asset('Profile/Employee/' . $fileName) . '?v=' . time(),
        ]);
    }    

    public function employeeUpdate(Request $request)
    {
        $employee = Employee::findOrFail($request->id);
        $column = $request->column;

        if ($column === 'emp_status') {
            $request->validate([
                'value' => ['required', 'integer', Rule::in($this->enabledStatusIds())],
            ]);

            $employee->update(['emp_status' => $request->value]);
            return response()->json(['success' => true]);
        }

        if ($column === 'camp_id') {
            $request->validate([
                'value' => 'nullable|integer|exists:camp_branches,id',
            ]);
        }
    
        if ($column == 'bdate') {
            $bdate = Carbon::parse($request->value);
            $age = (int) $bdate->age;
            $employee->update([
                $column => $request->value,
                'age' => $age
            ]);
        } 
        elseif ($column == 'citizenship' && $request->value == 1) {
            $employee->update([
                $column => $request->value,
                'c_category' => '',
                'country' => '',
            ]);
        } 
        elseif ($column == 'email') {
            $employee->update(['email' => $request->value]);
        }
        elseif ($column == 'height_cm' || $column == 'height_m') {
            if ($column == 'height_cm') {
                // Convert cm to meters
                $height_m = round($request->value / 100, 2); // 1 m = 100 cm
        
                $employee->update([
                    $column => $request->value,
                    'height_m' => $height_m
                ]);
            } elseif ($column == 'height_m') {
                // Convert meters to cm
                $height_cm = round($request->value * 100); // 1 m = 100 cm
        
                $employee->update([
                    $column => $request->value,
                    'height_cm' => $height_cm
                ]);
            }
        }
        elseif ($column == 'weight_kg' || $column == 'weight_lb') {
            if ($column == 'weight_kg') {
                // Convert kg to pounds
                $weight_lb = round($request->value * 2.20462, 2); // 1 kg = 2.20462 lbs
        
                $employee->update([
                    $column => $request->value,
                    'weight_lb' => $weight_lb
                ]);
            } elseif ($column == 'weight_lb') {
                // Convert lb to kg
                $weight_kg = round($request->value / 2.20462, 2); // 1 lb = 0.453592 kg
        
                $employee->update([
                    $column => $request->value,
                    'weight_kg' => $weight_kg
                ]);
            }
        }
        elseif ($column == 'emp_salary') {
            return response()->json(['success' => true]);
        }
        else {
            $columnsToCapitalize = ['lname', 'fname', 'mname'];
    
            $employee->update([
                $column => in_array($column, $columnsToCapitalize) ? strtoupper($request->value) : $request->value
            ]);
        }
    
        return response()->json(['success' => true]);
    }

    public function PDS($id){
        $empid = $this->resolveEmployeeRouteId($id);

        if (!$empid) {
            abort(404);
        }

        if ((string) $id === (string) $empid) {
            return redirect()->route('PDS', shortEncrypt((string) $empid));
        }

        if (auth()->guard('employee')->check() && (int) $empid !== (int) auth()->guard('employee')->id()) {
            return redirect()->route('PDS', shortEncrypt((string) auth()->guard('employee')->id()))
                ->with('error1', 'You can only access your own Personal Data Sheet.');
        }

        $setting = Setting::singleton();
        $locations = $this->resolveLocations($setting->locationContext());
        $employee = Employee::findOrFail($empid);
        $payrollemp = null;
        $columnstatus = $this->columnStat($employee->emp_ID);
        $devices = Device::all();

        $hprovinces = Province::where('region_id', $employee->add_region)->get();
        $hcities = City::where('city_id', $employee->add_city)->get();
        $hbarangays = Barangay::find($employee->add_brgy);

        $gprovinces = Province::where('region_id', $employee->padd_region)->get();
        $gcities = City::where('city_id', $employee->padd_city)->get();
        $gbarangays = Barangay::find($employee->padd_brgy);

        $stat = $this->enabledStatuses($setting);
        $statusIds = $stat->pluck('id');
        $supervisor = Employee::where('id', '!=', $empid)
            ->when($statusIds->isNotEmpty(), fn($q) => $q->whereIn('emp_status', $statusIds))
            ->get();
        
        $regions = Region::all();
        $offices = Office::where('office_name', 'not like', '%UNKNOWN%')
                 ->where('office_name', 'not like', '%CAMPUS%')
                 ->get();
        
        $quali = Qualification::all();

        return view("emp.pds", compact('employee', 'payrollemp', 'supervisor', 'devices', 'offices', 'locations', 'stat', 'quali', 'regions', 'hprovinces', 'hcities', 'hbarangays', 'gprovinces', 'gcities', 'gbarangays', 'empid', 'columnstatus'));
    }

    private function resolveEmployeeRouteId($id): ?int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        $decrypted = shortDecrypt((string) $id);

        return is_numeric($decrypted) ? (int) $decrypted : null;
    }

    public function genEmp(){
        $customPaper = array(0, 0, 970, 612);
        $employees = Employee::leftJoin('offices', 'employees.emp_dept', '=', 'offices.id')
            ->leftJoin('statuses', 'employees.emp_status', '=', 'statuses.id')
            ->get();
        
        $pdf = \PDF::loadView('emp.gen-emp', compact('employees'))->setPaper($customPaper, 'portrait');

        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('margin-bottom', 0);
        $pdf->setOption('margin-left', 0);

        $pdf->setCallbacks([
            'before_render' => function ($domPdf) {
                $domPdf->getCanvas()->page_text(10, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
            },
        ]);

        $pdf->render();

        return $pdf->stream();
    }
    
    public function empEdit($id)
    {
        $empid = $this->resolveEmployeeRouteId($id);
        Employee::findOrFail($empid);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => 200,
                'redirect' => route('PDS', shortEncrypt((string) $empid)),
            ]);
        }

        return redirect()->route('PDS', shortEncrypt((string) $empid));
    }

    public function empDelete($id){
        $emp = Employee::findOrFail($id);
        $empId = $emp->emp_ID;

        DB::transaction(function () use ($emp, $empId) {
            $emp->delete();
        });

        return response()->json([
            'success' => true,
            'status'=>200,
            'message'=>"Deleted Successfully",
            'id' => (int) $id,
        ]);
    }

    public function empPartimeRate(Request $request){
        $validator = Validator::make($request->all(), [
            'PartimeRate'=>'',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>400,
                'error'=>$validator->messages(),
            ]);
        }

        else{
            $update = [
                'partime_rate'=>round($request->input('PartimeRate'), 2)
            ];
            DB::table('employees')->where('id', $request->empid)->update($update);

            return response()->json([
                'status'=>200,
                'message'=>"Successfully Update",
            ]);
        }
    }
    
    public function empEditRate($id){
        $emp = Employee::find($id);
        return response()->json([
            'status'=>200,
            'emp'=>$emp,
        ]);
    }

    public function toggleAcctStat(Request $request)
    {
        $employee = Employee::findOrFail($request->id);
        $employee->stat_1 = $request->stat_1;
        $employee->save();
        
        return response()->json(['success' => true, 'message' => 'User role updated successfully.']);
    }    

    public function OfficialTimeRead(Request $request, $empid)
    {
        $offtimes = OfficialTime::where('empid', '=', $empid)->first();
        $monmorn = explode('-', $offtimes->morn_mon);
        $monnoon = explode('-', $offtimes->aft_mon);

        $tuemorn = explode('-', $offtimes->morn_tue);
        $tuenoon = explode('-', $offtimes->aft_tue);

        $wedmorn = explode('-', $offtimes->morn_wed);
        $wednoon = explode('-', $offtimes->aft_wed);

        $thumorn = explode('-', $offtimes->morn_thu);
        $thunoon = explode('-', $offtimes->aft_thu);

        $frimorn = explode('-', $offtimes->morn_fri);
        $frinoon = explode('-', $offtimes->aft_fri);

        $data = [
            'mon_mornin' => $monmorn[0],
            'mon_mornout' => $monmorn[1],
            'mon_noonin' => $monnoon[0],
            'mon_noonout' => $monnoon[1],

            'tue_mornin' => $tuemorn[0],
            'tue_mornout' => $tuemorn[1],
            'tue_noonin' => $tuenoon[0],
            'tue_noonout' => $tuenoon[1],

            'wed_mornin' => $wedmorn[0],
            'wed_mornout' => $wedmorn[1],
            'wed_noonin' => $wednoon[0],
            'wed_noonout' => $wednoon[1],

            'thu_mornin' => $thumorn[0],
            'thu_mornout' => $thumorn[1],
            'thu_noonin' => $thunoon[0],
            'thu_noonout' => $thunoon[1],

            'fri_mornin' => $frimorn[0],
            'fri_mornout' => $frimorn[1],
            'fri_noonin' => $frinoon[0],
            'fri_noonout' => $frinoon[1],
        ];
    
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);

    }    

    public function OfficialTimeCreate(Request $request)
    {
        $validatedData = $request->validate([
            'empid' => 'required',
            'mon_mornin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'mon_mornout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'mon_noonin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'mon_noonout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
    
            'tue_mornin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'tue_mornout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'tue_noonin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'tue_noonout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
    
            'wed_mornin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'wed_mornout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'wed_noonin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'wed_noonout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
    
            'thu_mornin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'thu_mornout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'thu_noonin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'thu_noonout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
    
            'fri_mornin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'fri_mornout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'fri_noonin' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
            'fri_noonout' => 'required                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ',
        ]);
        
        $officialTime = OfficialTime::firstOrNew(['empid' => $request->empid]);

        $convertTo24HourFormat = function ($time) {
            return (new \DateTime($time))->format('H:i:s');
        };
    
        $officialTime->morn_mon = $convertTo24HourFormat($request->mon_mornin) . '-' . $convertTo24HourFormat($request->mon_mornout);
        $officialTime->aft_mon = $convertTo24HourFormat($request->mon_noonin) . '-' . $convertTo24HourFormat($request->mon_noonout);
    
        $officialTime->morn_tue = $convertTo24HourFormat($request->tue_mornin) . '-' . $convertTo24HourFormat($request->tue_mornout);
        $officialTime->aft_tue = $convertTo24HourFormat($request->tue_noonin) . '-' . $convertTo24HourFormat($request->tue_noonout);
    
        $officialTime->morn_wed = $convertTo24HourFormat($request->wed_mornin) . '-' . $convertTo24HourFormat($request->wed_mornout);
        $officialTime->aft_wed = $convertTo24HourFormat($request->wed_noonin) . '-' . $convertTo24HourFormat($request->wed_noonout);
    
        $officialTime->morn_thu = $convertTo24HourFormat($request->thu_mornin) . '-' . $convertTo24HourFormat($request->thu_mornout);
        $officialTime->aft_thu = $convertTo24HourFormat($request->thu_noonin) . '-' . $convertTo24HourFormat($request->thu_noonout);
    
        $officialTime->morn_fri = $convertTo24HourFormat($request->fri_mornin) . '-' . $convertTo24HourFormat($request->fri_mornout);
        $officialTime->aft_fri = $convertTo24HourFormat($request->fri_noonin) . '-' . $convertTo24HourFormat($request->fri_noonout);
    
        $officialTime->save();
    
        return redirect()->back()->with('success', 'Official time saved successfully.');
    }    
    
    public function empQr(){
        $employees = Employee::select('emp_ID', 'fname', 'lname', 'emp_dept')
            ->orderBy('emp_dept')
            ->orderBy('lname')
            ->get();

        return view('emp.qr-code', compact('employees'));
    }
    

}


