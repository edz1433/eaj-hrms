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
        $prefix = Setting::singleton()->employeeIdPrefix();
        $lastNumber = $this->nextEmployeeNumber($prefix, true);

        do {
            $employeeId = $prefix . str_pad((string) ++$lastNumber, 4, '0', STR_PAD_LEFT);
        } while (Employee::where('emp_ID', $employeeId)->exists());

        return $employeeId;
    }

    private function previewEmployeeId(): string
    {
        $prefix = Setting::singleton()->employeeIdPrefix();
        $lastNumber = $this->nextEmployeeNumber($prefix);

        return $prefix . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function nextEmployeeNumber(string $prefix, bool $lock = false): int
    {
        $query = Employee::whereNotNull('emp_ID');

        if ($lock) {
            $query->lockForUpdate();
        }

        $prefixPattern = preg_quote($prefix, '/');
        $employeeIds = $query->pluck('emp_ID');

        return $employeeIds
            ->map(function ($employeeId) {
                if (preg_match('/^(\d+)$/', $employeeId, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->merge(
                $employeeIds->map(function ($employeeId) use ($prefixPattern) {
                    if (preg_match('/^' . $prefixPattern . '(\d+)$/i', $employeeId, $matches)) {
                        return (int) $matches[1];
                    }

                    return null;
                })
            )
            ->filter(fn($number) => $number !== null)
            ->max() ?? 0;
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
                    if ($modelClass === OfficialTime::class) {
                        $modelClass::create(OfficialTime::defaultAttributes($newEmpID));
                    } else {
                        $modelClass::create([
                            'empid' => $newEmpID,
                        ]);
                    }
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

        if (auth()->guard('employee')->check() && (int) $employee->id !== (int) auth()->guard('employee')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own Personal Data Sheet.',
            ], 403);
        }

        $column = $request->column;
        $specialLeaveColumns = [
            'special_pl',
            'solo_pl',
            'study_leave',
            'vawc_leave',
            'rehab_leave',
            'benefits_leave',
            'calamity_leave',
            'adopt_leave',
            'servcred_leave',
            'well_leave',
        ];

        if (in_array($column, $specialLeaveColumns, true)) {
            $request->validate([
                'value' => ['required', 'numeric', 'min:0', 'max:999.999'],
            ]);

            $value = round((float) $request->value, 3);
            $employee->update([$column => $value]);

            return response()->json([
                'success' => true,
                'value' => number_format($value, 3, '.', ''),
            ]);
        }

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

    public function PDS($id = null){
        $employeeGuard = auth()->guard('employee');

        if ($employeeGuard->check()) {
            if ($id === null) {
                $empid = $employeeGuard->id();
            } else {
                $empid = $this->resolveEmployeeRouteId($id);

                if ((int) $empid !== (int) $employeeGuard->id()) {
                    return redirect()->route('PDS')
                        ->with('error1', 'You can only access your own Personal Data Sheet.');
                }

                return redirect()->route('PDS');
            }
        } else {
            $empid = $this->resolveEmployeeRouteId($id);

            if ((string) $id === (string) $empid) {
                return redirect()->route('PDS', shortEncrypt((string) $empid));
            }
        }

        if (!$empid) {
            abort(404);
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
            ->select(
                'employees.lname',
                'employees.fname',
                'employees.position',
                'employees.org_email',
                'offices.office_name',
                'statuses.status_name'
            )
            ->orderBy('employees.lname')
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
        abort_unless(Employee::where('emp_ID', $empid)->exists(), 404, 'Employee not found.');

        $officialTime = OfficialTime::forEmployee((string) $empid);
    
        return response()->json([
            'success' => true,
            'empid' => $empid,
            'data' => $this->formatOfficialTime($officialTime),
        ]);

    }    

    public function OfficialTimeCreate(Request $request)
    {
        $rules = [
            'empid' => ['required', 'string', 'exists:employees,emp_ID'],
        ];

        foreach (OfficialTime::DAYS as $day) {
            foreach (['mornin', 'mornout', 'noonin', 'noonout'] as $slot) {
                $rules["{$day}_{$slot}"] = ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'];
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            '*.regex' => 'Please enter a valid time.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach (OfficialTime::DAYS as $day) {
                try {
                    $morningIn = OfficialTime::normalizeTime((string) $request->input("{$day}_mornin"));
                    $morningOut = OfficialTime::normalizeTime((string) $request->input("{$day}_mornout"));
                    $afternoonIn = OfficialTime::normalizeTime((string) $request->input("{$day}_noonin"));
                    $afternoonOut = OfficialTime::normalizeTime((string) $request->input("{$day}_noonout"));
                } catch (\Throwable) {
                    continue;
                }

                if ($morningIn >= $morningOut) {
                    $validator->errors()->add("{$day}_mornout", strtoupper($day) . ' AM Out must be after AM In.');
                }

                if ($afternoonIn >= $afternoonOut) {
                    $validator->errors()->add("{$day}_noonout", strtoupper($day) . ' PM Out must be after PM In.');
                }

                if ($morningOut > $afternoonIn) {
                    $validator->errors()->add("{$day}_noonin", strtoupper($day) . ' PM In must not be earlier than AM Out.');
                }
            }
        });

        $validatedData = $validator->validate();
        $payload = [];

        foreach (OfficialTime::DAYS as $day) {
            $payload["morn_{$day}"] = OfficialTime::buildRange(
                $validatedData["{$day}_mornin"],
                $validatedData["{$day}_mornout"]
            );
            $payload["aft_{$day}"] = OfficialTime::buildRange(
                $validatedData["{$day}_noonin"],
                $validatedData["{$day}_noonout"]
            );
        }

        $officialTime = OfficialTime::updateOrCreate(
            ['empid' => $validatedData['empid']],
            $payload
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Official time saved successfully.',
                'data' => $this->formatOfficialTime($officialTime),
            ]);
        }

        return redirect()->back()->with('success', 'Official time saved successfully.');
    }    

    private function formatOfficialTime(OfficialTime $officialTime): array
    {
        $data = [];

        foreach (OfficialTime::DAYS as $day) {
            [$morningIn, $morningOut] = OfficialTime::splitRange(
                $officialTime->{"morn_{$day}"},
                OfficialTime::DEFAULT_MORNING
            );
            [$afternoonIn, $afternoonOut] = OfficialTime::splitRange(
                $officialTime->{"aft_{$day}"},
                OfficialTime::DEFAULT_AFTERNOON
            );

            $data["{$day}_mornin"] = substr($morningIn, 0, 5);
            $data["{$day}_mornout"] = substr($morningOut, 0, 5);
            $data["{$day}_noonin"] = substr($afternoonIn, 0, 5);
            $data["{$day}_noonout"] = substr($afternoonOut, 0, 5);
        }

        return $data;
    }
    
    public function empQr(){
        $placeholderFiles = ['default.png', 'default-male.png', 'default-female.png'];
        $palette = ['#C9407A', '#7C3AED', '#2563EB', '#059669', '#D97706', '#DC2626', '#0891B2', '#0D9488'];

        $employees = Employee::leftJoin('offices', 'employees.emp_dept', '=', 'offices.id')
            ->leftJoin('statuses', 'employees.emp_status', '=', 'statuses.id')
            ->select(
                'employees.emp_ID',
                'employees.fname',
                'employees.mname',
                'employees.lname',
                'employees.suffix',
                'employees.position',
                'employees.profile',
                'employees.stat_1',
                'offices.office_name',
                'statuses.status_name'
            )
            ->orderBy('employees.emp_dept')
            ->orderBy('employees.lname')
            ->get()
            ->map(function ($employee) use ($placeholderFiles, $palette) {
                $i1 = strtoupper(substr((string) $employee->fname, 0, 1));
                $i2 = strtoupper(substr((string) $employee->lname, 0, 1));
                $initials = ($i1 . $i2) ?: '?';
                $profileFile = trim((string) $employee->profile);
                $hasProfile = $profileFile
                    && !in_array(strtolower($profileFile), $placeholderFiles, true)
                    && file_exists(public_path('Profile/Employee/' . $profileFile));

                $employee->display_name = trim(collect([
                    $employee->fname ? ucwords(strtolower($employee->fname)) : null,
                    $employee->mname ? ucwords(strtolower(substr($employee->mname, 0, 1))) . '.' : null,
                    $employee->lname ? ucwords(strtolower($employee->lname)) : null,
                    $employee->suffix ?: null,
                ])->filter()->implode(' ')) ?: 'Employee Profile';
                $employee->initials = $initials;
                $employee->initial_color = $i1 ? $palette[ord($i1) % count($palette)] : '#C9407A';
                $employee->profile_url = $hasProfile ? asset('Profile/Employee/' . $profileFile) : null;
                $employee->qr_token = shortEncrypt($employee->emp_ID);

                return $employee;
            });

        return view('emp.qr-code', compact('employees'));
    }
    

}


