<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Services\TwilioSms;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermitController extends Controller
{
    public function index(Request $request)
{
    $q = \App\Models\Permit::query();

    if ($status = $request->query('status')) {
        $q->where('status', $status); // e.g., ?status=approved
    }

    if ($search = $request->query('q')) {
        $q->where(function ($w) use ($search) {
            $w->where('number', 'ilike', "%{$search}%")
              ->orWhere('applicant', 'ilike', "%{$search}%");
        });
    }

    return $q->latest()->paginate(10);
}	

    public function store(Request $request)
    {
        // POST /api/permits
        $data = $request->validate([
            'number'       => 'required|string|max:50|unique:permits,number',
            'applicant'    => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'status'       => 'required|in:pending,approved,rejected',
        ]);

        $permit = Permit::create($data);
        return response()->json($permit, Response::HTTP_CREATED); // 201
    }

    public function show(Permit $permit)
    {
        // GET /api/permits/{id}
        return $permit;
    }

    public function update(Request $request, Permit $permit)
    {
        // PUT/PATCH /api/permits/{id}
        $data = $request->validate([
            'number'       => 'sometimes|required|string|max:50|unique:permits,number,' . $permit->id,
            'applicant'    => 'sometimes|required|string|max:100',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'status'       => 'sometimes|required|in:pending,approved,rejected',
        ]);

        $permit->update($data);
        return response()->json($permit); // 200
    }

    public function destroy(Permit $permit)
    {
        // DELETE /api/permits/{id}
        $permit->delete();
        return response()->noContent(); // 204
    }

    public function approve(Request $request, Permit $permit, TwilioSms $sms)
    {
        // POST /api/permits/{permit}/approve
        $permit->update(['status' => 'approved']);

        $notified = false;
        if ($request->boolean('notify') && $permit->phone_number) {
            try {
                $msg = "Permit {$permit->number} has been approved.";
                $notified = $sms->send($permit->phone_number, $msg);
            } catch (\Throwable $e) {
                \Log::warning('SMS failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'permit'  => $permit,
            'sms_sent'=> $notified,
        ]);
    }
}
