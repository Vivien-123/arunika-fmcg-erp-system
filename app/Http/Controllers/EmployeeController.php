<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;

class EmployeeController extends Controller
{
    /** S-14: Employees Kanban/List (F-18, T-27) */
    public function index(Request $request)
    {
        $query = Employee::with('department');

        if ($request->filled('dept'))   $query->where('department_id', $request->dept);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        $employees   = $query->orderBy('department_id')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $deptCounts  = $employees->groupBy('department_id')->map->count();
        $activeCnt   = $employees->where('status', 'Aktif')->count();
        $totalCnt    = $employees->count();

        return view('employees.index', compact('employees', 'departments', 'deptCounts', 'activeCnt', 'totalCnt'));
    }

    /** S-15: Employee Form (F-18, F-19, T-29) — SR-15: private hanya HR */
    public function show(Request $request, string $id)
    {
        $employee = Employee::with(['department', 'mappedUser'])->findOrFail($id);
        $isHR     = auth()->check() && auth()->user()->isHR();

        $privateRecord = $isHR ? $employee->privateRecord : null;
        $auditLogs     = AuditLog::forDocument('employee', $id);
        $auditCount    = $auditLogs->count();

        if ($request->has('private') && !$isHR) {
            AuditLog::record('employee', $id, 'private_access_denied', auth()->user()->username ?? 'guest', 'Non-HR attempted private data access (SR-15)');
        }

        return view('employees.show', compact('employee', 'privateRecord', 'auditLogs', 'auditCount', 'isHR'));
    }

    /** Toggle Aktif/Nonaktif — HR only (SR-18, T-30) */
    public function toggleStatus(Request $request, string $id)
    {
        if (!auth()->user()->isHR()) abort(403, 'Hanya HR yang dapat mengubah status pegawai.');

        $employee  = Employee::findOrFail($id);
        $oldStatus = $employee->status;
        $newStatus = $oldStatus === 'Aktif' ? 'Nonaktif' : 'Aktif';

        $employee->update(['status' => $newStatus]);

        AuditLog::record('employee', $id, 'status_changed', auth()->user()->username ?? auth()->user()->email, "Status changed from {$oldStatus} to {$newStatus}", auth()->user()->employee?->id, ['status' => $oldStatus], ['status' => $newStatus]);

        return redirect()->route('employees.show', $id)->with('success', "Status {$employee->name} diubah menjadi {$newStatus}.");
    }

    /** Validate sign-off eligibility (SR-17, SR-18) */
    public function validateSignOff(Request $request)
    {
        $request->validate(['employee_id' => 'required|string|exists:employees,id', 'document_type' => 'required|string', 'document_id' => 'required|string']);

        $employee = Employee::with('mappedUser')->findOrFail($request->employee_id);

        if (!$employee->canSignOff()) {
            $reason = $employee->status === 'Nonaktif' ? 'Pegawai berstatus Nonaktif (SR-18)' : ($employee->department_id !== 'D-WH' ? 'Bukan departemen Warehouse (SR-17)' : 'Tidak ada akun user aktif (SR-17)');
            AuditLog::record($request->document_type, $request->document_id, 'sign_off_rejected', auth()->user()->username ?? 'system', "Sign-off ditolak: {$reason}", $employee->id);
            return response()->json(['allowed' => false, 'reason' => $reason], 422);
        }

        AuditLog::record($request->document_type, $request->document_id, 'sign_off_authorized', auth()->user()->username ?? 'system', "Sign-off authorized for {$employee->name}", $employee->id);
        return response()->json(['allowed' => true, 'employee' => ['id' => $employee->id, 'name' => $employee->name, 'department' => $employee->department_id, 'status' => $employee->status]]);
    }
}
