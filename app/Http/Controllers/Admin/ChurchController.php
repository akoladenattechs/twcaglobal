<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CenterLocation;
use App\Models\ChurchMember;
use App\Models\ChurchStaff;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChurchController extends Controller
{
    public function members(Request $request)
    {
        $memberToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $memberToEdit = ChurchMember::findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            // Deletes only carry action + id, so handle them before the
            // add/edit validation below (which requires full member fields).
            if ($action === 'delete' && $request->input('id')) {
                $member = ChurchMember::findOrFail($request->input('id'));
                $member->delete();
            } else {
                $rules = [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'other_name' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255|unique:church_members,email',
                    'phone' => 'nullable|string|max:50',
                    'date_of_birth' => 'nullable|date',
                    'date_joined' => 'required|date',
                    'membership_status' => 'required|in:active,inactive,deceased',
                    'marital_status' => 'nullable|in:single,married,divorced,widowed',
                    'gender' => 'nullable|in:male,female',
                    'center_id' => 'nullable|exists:center_locations,id',
                    'address' => 'nullable|string|max:1000',
                    'city' => 'nullable|string|max:255',
                    'state' => 'nullable|string|max:255',
                    'country' => 'nullable|string|max:255',
                    'nationality' => 'nullable|string|max:255',
                    'occupation' => 'nullable|string|max:255',
                    'emergency_contact' => 'nullable|string|max:255',
                    'emergency_phone' => 'nullable|string|max:50',
                    'notes' => 'nullable|string|max:5000',
                ];

                // Allow keeping the existing email on edits
                if ($action === 'edit' && $request->input('id')) {
                    $rules['email'] = 'nullable|email|max:255|unique:church_members,email,'.$request->input('id');
                }

                $validated = $request->validate($rules);

                $data = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'other_name' => $validated['other_name'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'date_joined' => $validated['date_joined'],
                    'membership_status' => $validated['membership_status'],
                    'marital_status' => $validated['marital_status'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'center_id' => $validated['center_id'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'country' => $validated['country'] ?? null,
                    'nationality' => $validated['nationality'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'emergency_phone' => $validated['emergency_phone'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ];

                if ($action === 'add') {
                    ChurchMember::create($data);
                } elseif ($action === 'edit' && $request->input('id')) {
                    $member = ChurchMember::findOrFail($request->input('id'));
                    $member->update($data);
                }
            }

            return redirect()->route('admin.members');
        }

        $members = ChurchMember::orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->get();
        $centers = CenterLocation::orderBy('name', 'asc')->get();

        return view('admin.members', compact('members', 'memberToEdit', 'centers'));
    }

    public function memberView(int $id)
    {
        $member = ChurchMember::with('center')->findOrFail($id);

        return view('admin.member_view', compact('member'));
    }

    public function attendance(Request $request)
    {
        $attendanceToEdit = null;
        $serviceTypeToEdit = null;

        // GET: edit modes
        if ($request->isMethod('GET')) {
            if ($request->input('action') === 'edit' && $request->input('id')) {
                $attendanceToEdit = Attendance::findOrFail($request->input('id'));
            }
            if ($request->input('service_action') === 'edit' && $request->input('service_id')) {
                $serviceTypeToEdit = ServiceType::findOrFail($request->input('service_id'));
            }
        }

        if ($request->isMethod('POST')) {
            // --- Attendance CRUD ---
            if ($request->input('action')) {
                $action = $request->input('action');

                if ($action === 'add') {
                    Attendance::create([
                        'center_id' => $request->input('center_id'),
                        'service_date' => $request->input('service_date'),
                        'service_type' => $request->input('service_type'),
                        'males' => $request->input('males', 0),
                        'females' => $request->input('females', 0),
                        'first_timers' => $request->input('first_timers', 0),
                        'recorded_by' => Auth::user()->name ?? Auth::user()->username,
                        'notes' => $request->input('notes'),
                    ]);
                } elseif ($action === 'edit' && $request->input('id')) {
                    $record = Attendance::findOrFail($request->input('id'));
                    $record->update([
                        'center_id' => $request->input('center_id'),
                        'service_date' => $request->input('service_date'),
                        'service_type' => $request->input('service_type'),
                        'males' => $request->input('males', 0),
                        'females' => $request->input('females', 0),
                        'first_timers' => $request->input('first_timers', 0),
                        'notes' => $request->input('notes'),
                    ]);
                } elseif ($action === 'delete' && $request->input('id')) {
                    Attendance::destroy($request->input('id'));
                }
            }

            // --- Service Type CRUD ---
            if ($request->input('service_action')) {
                $serviceAction = $request->input('service_action');

                if ($serviceAction === 'add') {
                    ServiceType::create([
                        'name' => $request->input('service_name'),
                        'description' => $request->input('service_description'),
                        'is_active' => $request->has('is_active'),
                    ]);
                } elseif ($serviceAction === 'edit' && $request->input('service_id')) {
                    $type = ServiceType::findOrFail($request->input('service_id'));
                    $type->update([
                        'name' => $request->input('service_name'),
                        'description' => $request->input('service_description'),
                        'is_active' => $request->has('is_active'),
                    ]);
                } elseif ($serviceAction === 'delete' && $request->input('service_id')) {
                    ServiceType::destroy($request->input('service_id'));
                }
            }

            return redirect()->route('admin.attendance');
        }

        $attendance = Attendance::with('center')->orderBy('service_date', 'desc')->get();
        $serviceTypes = ServiceType::orderBy('name', 'asc')->get();
        $centers = CenterLocation::orderBy('name', 'asc')->get();

        return view('admin.attendance', compact('attendance', 'serviceTypes', 'centers', 'attendanceToEdit', 'serviceTypeToEdit'));
    }

    public function staff(Request $request)
    {
        $staffToEdit = null;

        if ($request->isMethod('GET') && $request->input('action') === 'edit' && $request->input('id')) {
            $staffToEdit = ChurchStaff::with('member')->findOrFail($request->input('id'));
        }

        if ($request->isMethod('POST')) {
            $action = $request->input('action');

            if ($action === 'add') {
                ChurchStaff::create([
                    'member_id' => $request->input('member_id'),
                    'position' => $request->input('position'),
                    'department' => $request->input('department'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'status' => $request->input('status', 'active'),
                    'salary' => $request->input('salary'),
                    'responsibilities' => $request->input('responsibilities'),
                ]);
            } elseif ($action === 'edit' && $request->input('id')) {
                $staff = ChurchStaff::findOrFail($request->input('id'));
                $staff->update([
                    'member_id' => $request->input('member_id'),
                    'position' => $request->input('position'),
                    'department' => $request->input('department'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'status' => $request->input('status', 'active'),
                    'salary' => $request->input('salary'),
                    'responsibilities' => $request->input('responsibilities'),
                ]);
            } elseif ($action === 'delete' && $request->input('id')) {
                $staff = ChurchStaff::findOrFail($request->input('id'));
                $staff->delete();
            }

            return redirect()->route('admin.staff');
        }

        $staff = ChurchStaff::with('member')->orderBy('position', 'asc')->get();
        $members = ChurchMember::orderBy('last_name', 'asc')->get();

        return view('admin.staff', compact('staff', 'members', 'staffToEdit'));
    }
}
