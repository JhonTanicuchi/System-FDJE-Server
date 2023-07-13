<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Carbon\Carbon;

class TestsController extends Controller
{
    public function getTests()
    {
        $tests = Test::where('archived', false)->get();
        $tests->load('patient');
        $tests->load('patient.person');

        $testsWithMissing = [];

        foreach ($tests as $test) {

            // Inicializa un contador para el número de exámenes faltantes
            $examsMissing = 0;

            // Verifica si cada examen está faltante para el paciente
            if ($test) {
                if ($test->ophthalmologist === 'no') {
                    $examsMissing++;
                }
                if ($test->nephrologist === 'no') {
                    $examsMissing++;
                }
                if ($test->podiatrist === 'no') {
                    $examsMissing++;
                }
                if ($test->lipidic === 'no') {
                    $examsMissing++;
                }
                if ($test->thyroid === 'no') {
                    $examsMissing++;
                }
            } else {
                $examsMissing = 5;
            }

            array_push($testsWithMissing, [
                'test' => $test,
                'exams_missing' => $examsMissing
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tests' => $testsWithMissing
            ]
        ]);
    }

    public function getTestById($id)
    {
        $test = Test::find($id);
        $test->load('patient');
        $test->load('state');

        return response()->json([
            'status' => 'success',
            'data' => [
                'test' => $test
            ]
        ]);
    }

    public function getTestLast($id)
    {
        $patient = Patient::find($id);
        $testLast = $patient->Test()->orderBy('created_at', 'desc')->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'test_last' => $testLast
            ]
        ]);
    }

    public function getTestsWithPatients()
    {
        $patients = Patient::where('archived', false)->whereHas('type', function ($query) {
            $query->where('value', 'apadrinado');
        })->get();

        $patients->load('person');
        $patients->load('type');

        $tests = Test::all();

        $patientsWithTests = [];

        foreach ($patients as $patient) {
            $testByPatient = $tests->where('patient', $patient->id)->last();

            // Inicializa un contador para el número de exámenes faltantes
            $examsMissing = 0;

            // Verifica si cada examen está faltante para el paciente
            if ($testByPatient) {
                if ($testByPatient->ophthalmologist === 'no') {
                    $examsMissing++;
                }
                if ($testByPatient->nephrologist === 'no') {
                    $examsMissing++;
                }
                if ($testByPatient->podiatrist === 'no') {
                    $examsMissing++;
                }
                if ($testByPatient->lipidic === 'no') {
                    $examsMissing++;
                }
                if ($testByPatient->thyroid === 'no') {
                    $examsMissing++;
                }
            } else {
                $examsMissing = 5;
            }

            array_push($patientsWithTests, [
                'patient' => $patient,
                'test' => $testByPatient,
                'exams_missing' => $examsMissing
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_tests' => $patientsWithTests
            ]
        ]);
    }


    public function searchTestsByTerm($term = '')
    {
        $tests = Test::where('archived', false)
            ->where(function ($query) use ($term) {
                $query->orWhereHas('patient.person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                })
                    ->orWhere('delivered', 'like', '%' . $term . '%');
            })
            ->get();

        $tests->load('patient');
        $tests->load('patient.person');

        $Tests = [];

        foreach ($tests as $test) {

            // Inicializa un contador para el número de exámenes faltantes
            $examsMissing = 0;

            // Verifica si cada examen está faltante para el paciente
            if ($test) {
                if ($test->ophthalmologist === 'no') {
                    $examsMissing++;
                }
                if ($test->nephrologist === 'no') {
                    $examsMissing++;
                }
                if ($test->podiatrist === 'no') {
                    $examsMissing++;
                }
                if ($test->lipidic === 'no') {
                    $examsMissing++;
                }
                if ($test->thyroid === 'no') {
                    $examsMissing++;
                }
            } else {
                $examsMissing = 5;
            }

            array_push($Tests, [
                'test' => $test,
                'exams_missing' => $examsMissing
            ]);
        }


        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'tests' => $Tests
            ]
        ], 200);
    }

    public function searchTestsWithPatientsByTerm($term = '')
    {
        $patients = Patient::where('archived', false)
            ->whereHas('type', function ($query) {
                $query->where('value', 'apadrinado');
            })
            ->where(function ($query) use ($term) {
                $query->orWhereHas('person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                });
            })
            ->get();

        $patients->load('person');
        $patients->load('type');

        $tests = Test::all();

        $patientsWithTests = [];

        foreach ($patients as $patient) {
            $testByPatient = $tests->where('patient', $patient->id)->last();
            array_push($patientsWithTests, [
                'patient' => $patient,
                'test' => $testByPatient
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'patients_with_tests' => $patientsWithTests
            ]
        ]);
    }

    public function createTest(Request $request)
    {
        $request->validate([
            'patient' => 'required|array',
            'ophthalmologist' => 'required|string',
            'nephrologist' => 'required|string',
            'podiatrist' => 'required|string',
            'lipidic' => 'required|string',
            'thyroid' => 'required|string',
            'state' => 'required|integer',
        ]);

        try {
            DB::transaction(
                function () use ($request) {

                    $data = $request->all();
                    foreach (['ophthalmologist_date', 'nephrologist_date', 'podiatrist_date', 'lipidic_date', 'thyroid_date'] as $field) {
                        if (empty($data[$field])) {
                            $data[$field] = null;
                        } else {
                            $data[$field] = Carbon::parse($data[$field]);
                        }
                    }
                    $data['patient'] = $request->patient['id'];
                    Test::create($data);
                }
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Registro de hemoglobina creado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTest(Request $request, $id)
    {
        $request->validate([
            'patient' => 'required|array',
            'ophthalmologist' => 'required|string',
            'ophthalmologist_date' => 'date',
            'nephrologist' => 'required|string',
            'podiatrist' => 'required|string',
            'lipidic' => 'required|string',
            'thyroid' => 'required|string',
            'state' => 'required|integer',
        ]);

        try {
            DB::transaction(
                function () use ($request, $id) {
                    $test = Test::find($id);
                    $data = $request->all();
                    foreach (['ophthalmologist_date', 'nephrologist_date', 'podiatrist_date', 'lipidic_date', 'thyroid_date'] as $field) {
                        if (empty($data[$field])) {
                            $data[$field] = null;
                        } else {
                            $data[$field] = Carbon::parse($data[$field]);
                        }
                    }
                    $data['patient'] = $request->patient['id'];
                    $test->update($data);
                }
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Registro de hemoglobina actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el registro de hemoglobina: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getArchivedTests()
    {
        $tests = Test::where('archived', true)->get();
        $tests->load('patient');
        $tests->load('patient.person', 'patient.person.identification_type');
        $tests->load('archived_by');

        return response()->json([
            'status' => 'success',
            'data' => [
                'tests' => $tests
            ]
        ]);
    }

    public function searchTestsArchivedByTerm($term = '')
    {
        $tests = Test::where('archived', true)
            ->where(function ($query) use ($term) {
                $query->orWhereHas('patient.person', function ($query) use ($term) {
                    $query->where('names', 'like', '%' . $term . '%')
                        ->orWhere('last_names', 'like', '%' . $term . '%')
                        ->orWhere('identification', 'like', '%' . $term . '%')
                        ->orWhereRaw("concat(names, ' ', last_names) like ?", ['%' . $term . '%']);
                })
                    ->orWhere('delivered', 'like', '%' . $term . '%');
            })
            ->get();

        $tests->load('patient');
        $tests->load('patient.person');

        return new JsonResponse([
            'status' => 'success',
            'data' => [
                'tests' => $tests
            ]
        ], 200);
    }

    public function archiveTest($id)
    {
        $test = Test::find($id);
        $test->archived = true;
        $test->archived_at = now();
        $test->archived_by = auth()->user()->id;
        $test->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina archivado correctamente'
        ]);
    }

    public function restoreTest($id)
    {
        $test = Test::find($id);
        $test->archived = false;
        $test->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina restaurado correctamente'
        ]);
    }

    public function deleteTest($id)
    {
        $test = Test::find($id);
        $test->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Examen de hemoglobina eliminado correctamente'
        ]);
    }
}
