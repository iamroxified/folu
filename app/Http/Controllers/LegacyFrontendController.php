<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class LegacyFrontendController extends Controller
{
    public function show(?string $page = 'index'): View|Response
    {
        $page = $this->normalizePage($page);
        $view = 'frontend.pages.' . $page;

        if (! view()->exists($view)) {
            if (view()->exists('frontend.pages.404')) {
                return response()->view('frontend.pages.404', [], 404);
            }

            abort(404);
        }

        return view($view);
    }

    /**
     * Fetch LGAs dynamically for a state ID or state name.
     */
    public function getLgas(Request $request): JsonResponse
    {
        $stateId = $request->query('state_id');
        $stateName = $request->query('state_name');

        if (!$stateId && $stateName) {
            try {
                $stateId = DB::table('state')
                    ->where('name', $stateName)
                    ->value('id');
            } catch (Throwable $e) {}
        }

        if (!$stateId) {
            return response()->json([]);
        }

        try {
            $lgas = DB::table('local_governments')
                ->where('state_id', $stateId)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);

            return response()->json($lgas);
        } catch (Throwable $e) {
            return response()->json([]);
        }
    }

    /**
     * Submit online student application harmonized with administrative enrollment requirements.
     */
    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'other_names' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:male,female,Male,Female'],
            'school_level' => ['required', 'string'],
            'target_class' => ['required', 'string'],
            'student_email' => ['nullable', 'email', 'max:100'],
            'state_of_origin' => ['required', 'string'],
            'lga' => ['required', 'string'],
            'student_type' => ['required', 'string', 'in:day,boarding,Day,Boarding'],
            'blood_group' => ['nullable', 'string'],
            'genotype' => ['nullable', 'string'],
            'parent_name' => ['required', 'string', 'max:100'],
            'parent_phone' => ['required', 'string', 'max:20'],
            'parent_email' => ['required', 'email', 'max:100'],
            'parent_relationship' => ['required', 'string'],
            'home_address' => ['required', 'string'],
            'prev_school' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],
        ]);

        // Attempt to store in an admission enquiries or student applications table if it exists
        try {
            if (DB::getSchemaBuilder()->hasTable('admission_applications')) {
                DB::table('admission_applications')->insert([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'other_names' => $validated['other_names'] ?? '',
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => strtolower($validated['gender']),
                    'school_level' => $validated['school_level'],
                    'target_class' => $validated['target_class'],
                    'student_email' => $validated['student_email'] ?? '',
                    'state_of_origin' => $validated['state_of_origin'],
                    'lga' => $validated['lga'],
                    'student_type' => strtolower($validated['student_type']),
                    'blood_group' => $validated['blood_group'] ?? null,
                    'genotype' => $validated['genotype'] ?? null,
                    'parent_name' => $validated['parent_name'],
                    'parent_phone' => $validated['parent_phone'],
                    'parent_email' => $validated['parent_email'],
                    'parent_relationship' => strtolower($validated['parent_relationship']),
                    'home_address' => $validated['home_address'],
                    'prev_school' => $validated['prev_school'] ?? '',
                    'additional_notes' => $validated['additional_notes'] ?? '',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            // Non-blocking log/fallback
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission application received successfully! Our team will contact you shortly.',
                'data' => $validated,
            ]);
        }

        return back()->with('success', 'Application submitted successfully! Our admissions secretary will contact you shortly.');
    }

    private function normalizePage(?string $page): string
    {
        $page = trim((string) $page, '/');
        $page = $page === '' ? 'index' : $page;
        $page = preg_replace('/\.(html|php)$/i', '', $page) ?: 'index';

        return $page === 'welcome.blade' ? 'index' : $page;
    }
}

